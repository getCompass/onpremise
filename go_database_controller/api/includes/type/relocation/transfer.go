package relocation

// пакет, отвечающий за переезд копаний между домино
// в этом файле описаны функции, работающие с данными компании

import (
	"context"
	"fmt"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/keeper"
	"go_database_controller/api/includes/type/logger"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/routine"
	"io/ioutil"
	"os"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"golang.org/x/crypto/ssh"
)

// таймаут отправки дампа базы на другой сервер
const copyDumpFileTimeout = 5 * time.Second

// название хранилища рутин
const routineStoreName = "relocation"

// хранилище рутин релокации компаний
var relocationRoutineStore = routine.MakeStore(routineStoreName)

// BeginDataCopying запускает рутину копирования данных да другой сервер, используется при релокации компаний
// скопировать данные можно только для компании, к которой привязан сервисный порт
// возвращает ключ рутины и имя файла-дампа (без пути)
func BeginDataCopying(companyId int64, targetHost string) (string, string) {

	routineChan := make(chan *routine.Status)
	routineUniq := functions.GenerateUuid()

	// формируем имя для файла-дампа
	dumpFileName := resolveDatabaseDumpFileName(companyId)

	// добавляем рутину в хранилище рутин
	routineKey := relocationRoutineStore.Push(routineUniq, routineChan, &logger.Log{})

	// запускаем рутину переезда
	go copyData(routineChan, companyId, targetHost, dumpFileName)

	return routineKey, dumpFileName
}

// выполняет копирование и перенос данных с одного сервера на другой
// @long много проверок на ошибки + пуш результата в канал
func copyData(routineChan chan *routine.Status, companyId int64, targetHost, dumpFileName string) {

	routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("can't sync database dump file with host %s: %s", fmt.Errorf("no available on onpremise"), targetHost))
}

// BeginDataApplying запускает рутину применения скопированных данных, используется при релокации компаний
// скопировать данные можно только для компании, к которой привязан сервисный или рабочий порт
func BeginDataApplying(companyId int64) string {

	routineChan := make(chan *routine.Status)
	routineUniq := functions.GenerateUuid()

	// добавляем рутину в хранилище рутин
	routineKey := relocationRoutineStore.Push(routineUniq, routineChan, &logger.Log{})

	// запускаем рутину переезда
	go applyData(routineChan, companyId)

	return routineKey
}

// применяет копированные данные
func applyData(routineChan chan *routine.Status, companyId int64) {

	ctx := context.Background()

	// получаем порт, на котором развернута компания
	maintenancePort, err := port_registry.GetByCompany(ctx, companyId)

	if err != nil {

		// проверяем, что можно получить запись для компании
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	} else if maintenancePort.Port == 0 {

		// проверяем, что к компании привязан порт
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, fmt.Sprintf("company %d has no maintenance port", companyId))
		return
	}

	if err = keeper.RestoreDatabase(maintenancePort); err != nil {

		// проверяем, что к компании привязан порт
		routineChan <- routine.MakeRoutineStatus(routine.StatusError, err.Error())
		return
	}

	routineChan <- routine.MakeRoutineStatus(routine.StatusDone, "routine done")
}

// формирует имя для файла-дампа
func resolveDatabaseDumpFileName(companyId int64) string {

	return fmt.Sprintf("%s", "mysql_company_"+functions.Int64ToString(companyId)+".zip")
}

// возвращает credentials для доступа к домино по ssh
func resolveRemoteHostCredentials() (*ssh.ClientConfig, error) {

	privateKeyFile, err := os.Open(conf.GetConfig().BackupSshFileKeyFilePath)
	if err != nil {
		return nil, err
	}

	privateKeyBytes, err := ioutil.ReadAll(privateKeyFile)
	if err != nil {
		return nil, err
	}

	signer, err := ssh.ParsePrivateKey(privateKeyBytes)
	if err != nil {
		return nil, err
	}

	return &ssh.ClientConfig{
		User: conf.GetConfig().DominoUser,
		Auth: []ssh.AuthMethod{
			ssh.PublicKeys(signer),
		},
		HostKeyCallback: ssh.InsecureIgnoreHostKey(), // nosemgrep
	}, nil
}
