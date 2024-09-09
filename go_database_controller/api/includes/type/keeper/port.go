package keeper

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_database_controller/api/includes/type/db/company"
	"go_database_controller/api/includes/type/db/domino_service"
	"go_database_controller/api/includes/type/logger"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/routine"
	"time"
)

// название хранилища рутин
const routineStoreName = "bind"

// хранилище рутин привязки портов компаний
var bindPortRoutineStore = routine.MakeStore(routineStoreName)

// создает данные свободной компании на указанном порте
// @long миллион обработок ошибок
func CreateVacantCompany(portValue int32, companyId int64) error {

	port, err := port_registry.GetByPort(portValue)
	if err != nil {
		return err
	}

	// убеждаемся, что порт никем не занят
	if port.CompanyId > 0 || port.Status != port_registry.PortVoid {
		return fmt.Errorf("port is not allowed")
	}

	// создаем файлы пустой  базы данных для компании
	if err = CreateDatabase(companyId); err != nil {
		return err
	}

	// переводим порт в статус активных
	if _, err = domino_service.SetStatus(portValue, port_registry.PortActive, 0, companyId); err != nil {
		return err
	}

	// пытаемся запустить контейнер на выбранном порте
	if err = Start(portValue); err != nil {
		return err
	}

	return company.CreateUserOnEmptyDb(port)
}

// добавляем порт новый в систему
func AddPort(port int32, status int32, portType int32, lockedTill int32, createdAt int32, updatedAt int32, companyId int64, extra string) error {

	return domino_service.InsertIgnoreOne(port, status, portType, lockedTill, createdAt, updatedAt, companyId, extra)
}

// помечаем порт невалидным
func InvalidatePort(port int32) error {

	log.Infof("начинаем инвалидацию порта %d...", port)

	_, err := domino_service.UpdateStatus(port, port_registry.PortInvalid)

	// пытаемся остановить контейнер; результат неинтересен по большей части,
	// потому что явно уже что-то идет не так
	if err := Stop(port); err != nil {
		log.Errorf("error occurred on port invalidation: %s", err.Error())
	}

	return err
}

// BindPort привязываем порт к указанной компании
func BeginBindPort(portValue int32, companyId int64, nonExistingDataDirPolicy, duplicateDataDirPolicy int32) string {

	routineChan := make(chan *routine.Status)
	routineUniq := functions.GenerateUuid()

	// добавляем рутину в хранилище рутин
	routineKey := bindPortRoutineStore.Push(routineUniq, routineChan, &logger.Log{})

	go bindPort(routineChan, portValue, companyId, nonExistingDataDirPolicy, duplicateDataDirPolicy)

	return routineKey
}

// UnbindPort отвязываем порт от любой компании, которая висит на нем
func UnbindPort(port int32) error {

	log.Infof("начинаем анбинд порта %d...", port)

	row, err := port_registry.GetByPort(port)
	if err != nil {
		return err
	}

	lockedTill := functions.GetCurrentTimeStamp() + 60
	if _, err = domino_service.SetStatus(port, port_registry.PortLocked, lockedTill, 0); err != nil {
		return err
	}

	if err = Stop(row.Port); err != nil {
		return err
	}

	if _, err = domino_service.SetStatus(port, port_registry.PortVoid, 0, 0); err != nil {
		return err
	}
	return err
}

// BindOnServicePort биндим порт для компании
// Deprecated: используй обычный Bind
func BindOnServicePort(companyId int64) (int32, string, string, error) {

	port, err := port_registry.BindServicePort(companyId)
	if err != nil {
		return 0, "", "", err
	}

	if _, err = domino_service.SetStatus(port.Port, port_registry.PortActive, 0, companyId); err != nil {
		return 0, "", "", err
	}

	if err = Start(port.Port); err != nil {
		return 0, "", "", err
	}

	mysqlUser, mysqlPass, err := port.GetCredentials()
	if err != nil {
		return 0, "", "", err
	}

	return port.Port, mysqlUser, mysqlPass, nil
}

// GetCompanyPort возвращает порт по id компании
func GetCompanyPort(companyId int64) (int32, string, string, error) {

	port, err := port_registry.GetByCompany(companyId)
	if err != nil {

		log.Errorf("%v", err)
		return 0, "", "", err
	}

	if port == nil {
		return 0, "", "", nil
	}

	mysqlUser, mysqlPass, err := port.GetCredentials()
	if err != nil {
		return 0, "", "", err
	}

	return port.Port, mysqlUser, mysqlPass, nil
}

// ResetPort сбрасывает состояние порта до void
// убивает контейнер, если запущен, данные компании при этом не трогает
func ResetPort(portValue int32) error {

	port, err := port_registry.GetByPort(portValue)
	if err != nil {
		return err
	}

	// если порту нужен контейнер, то скорее всего он запущен
	if port.NeedDaemon() {

		// отвязываем порт
		if err = UnbindPort(portValue); err != nil {
			return err
		}
	}

	// обновляем статус для порта
	_, err = domino_service.SetStatus(portValue, port_registry.PortVoid, 0, 0)

	return err
}

func bindPort(routineChan chan *routine.Status, portValue int32, companyId int64, nonExistingDataDirPolicy, duplicateDataDirPolicy int32) {

	// проверяем, что такой порт есть
	port, err := port_registry.GetByPort(portValue)
	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	}

	// теперь нужно проверить наличие директории с данными компании и решить, как поступать дальше
	needInit, err := PrepareDataDir(companyId, nonExistingDataDirPolicy, duplicateDataDirPolicy)
	if err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	}

	// отмечаем порт активным
	if _, err = domino_service.SetStatus(portValue, port_registry.PortActive, 0, companyId); err != nil {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	}

	// запускаем контейнер
	if err = Start(portValue); err != nil {
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
	}

	isAlive := waitPortAlive(port, needInit, 120)

	if !isAlive {

		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("Не смогли поднять порт %s за 120 секунд", portValue))
		return
	}

	// если вдруг нужно проинициализировать данные
	if needInit {

		if err = company.CreateUserOnEmptyDb(port); err != nil {

			routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
			return
		}
	}

	routineChan <- routine.MakeRoutineStatus(routine.StatusDone, "routine done")
}

// ждем пока mysql оживет
func waitPortAlive(port *port_registry.PortRegistryStruct, needInit bool, timeout int64) bool {

	endAt := functions.GetCurrentTimeStamp() + timeout

	for {

		if err := isPortAlive(port, needInit); err == nil {
			return true
		}

		if endAt < functions.GetCurrentTimeStamp() {
			return false
		}

		time.Sleep(500 * time.Millisecond)
	}
}

// проверяем жив ли mysql
func isPortAlive(port *port_registry.PortRegistryStruct, needInit bool) error {

	user, password, err := port.GetCredentials()

	if err != nil {
		return err
	}

	if needInit {
		user = "root"
		password = "root"
	}

	db, err := sql.Open("mysql", fmt.Sprintf("%s:%s@tcp(%s:%d)/", user, password, company.GetCompanyHost(port.Port), port.Port))
	defer db.Close()
	if err != nil {
		return err
	}

	ctx, cancel := context.WithTimeout(context.Background(), time.Millisecond*100)
	defer cancel()
	err = db.PingContext(ctx)

	if err != nil {
		return err
	}

	log.Infof("Пропинговали порт %i", port.Port)

	return nil
}
