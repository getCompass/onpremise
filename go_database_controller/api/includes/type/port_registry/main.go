package port_registry

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/crypt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/db/domino_service"
)

const PortVoid = 10    // порт свободен и готов принимать компанию
const PortActive = 20  // порт занят активной компанией
const PortLocked = 30  // порт заблокирован какой-то компанией для дальнейших действий
const PortInvalid = 90 // с портом при последней операции произошла ошибка и он недоступен

// PortRegistryStruct тип — порт компании на домино
type PortRegistryStruct struct {
	Port       int32      `json:"port"`
	Status     int        `json:"status"`
	Type       int        `json:"type"`
	LockedTill int        `json:"locked_till"`
	CreatedAt  int        `json:"created_at"`
	UpdatedAt  int        `json:"updated_at"`
	CompanyId  int64      `json:"company_id"`
	ExtraField ExtraField `json:"extra"`
}

// GetCredentials возвращает credentials для доступа к базе данных
func (port *PortRegistryStruct) GetCredentials() (string, string, error) {

	usr, err := port.GetDecryptedMysqlUser()
	if err != nil {
		return "", "", err
	}

	password, err := port.GetDecryptedMysqlPass()
	if err != nil {
		return "", "", err
	}

	return usr, password, nil
}

// GetDecryptedMysqlUser расшифровываем пользователя
func (port *PortRegistryStruct) GetDecryptedMysqlUser() (string, error) {

	// расшифровываем находящиеся в экстре пользователя и пароль
	user, err := crypt.Decrypt(port.GetEncryptedUser(), "aes-256", conf.GetConfig().EncryptKey, conf.GetConfig().EncryptIv)
	if err != nil {
		return "", fmt.Errorf("cant decrypt user for port: %d", port.Port)
	}
	return user, nil
}

// GetDecryptedMysqlPass расшифровываем пароль
func (port *PortRegistryStruct) GetDecryptedMysqlPass() (string, error) {

	password, err := crypt.Decrypt(port.GetEncryptedPassword(), "aes-256", conf.GetConfig().EncryptKey, conf.GetConfig().EncryptIv)
	if err != nil {
		return "", fmt.Errorf("cant decrypt password for port: %d", port.Port)
	}

	return password, nil
}

// NeedDaemon возвращает нужно ли для порта поднять демон или нет
func (port *PortRegistryStruct) NeedDaemon() bool {

	return port.Status == PortActive && port.CompanyId != 0
}

// GetAllCompanyPortList получаем все записи из базы
func GetAllCompanyPortList() ([]*PortRegistryStruct, error) {

	rows, err := domino_service.GetAllCompanyPortList()

	if err != nil {
		return nil, err
	}

	portRegistryList, err := makePortRegistryStructList(rows)
	if err != nil {
		return nil, err
	}
	return portRegistryList, nil
}

// BindServicePort получаем запись по порту
func BindServicePort(companyId int64) (*PortRegistryStruct, error) {

	transaction, err := domino_service.BeginTransaction()
	if err != nil {
		return nil, err
	}

	row, err := transaction.GetServicePortForUpdate(PortVoid)
	if err != nil {

		_ = transaction.RollbackTransaction()
		return nil, err
	}

	portRegistry, err := makePortRegistryStruct(row)
	if err != nil {

		_ = transaction.RollbackTransaction()
		return nil, err
	}

	lockedTill := functions.GetCurrentTimeStamp() + 60
	if err = transaction.Update(portRegistry.Port, PortLocked, lockedTill, companyId); err != nil {

		_ = transaction.RollbackTransaction()
		return nil, err
	}

	if err = transaction.CommitTransaction(); err != nil {
		return nil, err
	}

	return portRegistry, nil
}

// GetByPort получаем запись по порту
func GetByPort(port int32) (*PortRegistryStruct, error) {

	row, err := domino_service.GetOne(port)

	if err != nil {
		return nil, err
	}

	portRegistry, err := makePortRegistryStruct(row)
	if err != nil {
		return nil, err
	}
	return portRegistry, nil
}

// GetByCompany получаем запись по порту
func GetByCompany(companyId int64) (*PortRegistryStruct, error) {

	row, err := domino_service.GetOneWithStatusByCompanyId(companyId, PortActive)
	if err != nil {

		log.Errorf("%v", err)
		return nil, err
	}

	_, exist := row["port"]
	if !exist {
		return nil, nil
	}

	portRegistry, err := makePortRegistryStruct(row)
	if err != nil {

		log.Errorf("%v", err)
		return nil, err
	}
	return portRegistry, nil
}

// создаем структуру с портами
func makePortRegistryStructList(rows map[int]map[string]string) ([]*PortRegistryStruct, error) {

	portRegistryList := make([]*PortRegistryStruct, 0)

	for _, row := range rows {

		portRegistry, err := makePortRegistryStruct(row)
		if err != nil {
			return nil, err
		}
		portRegistryList = append(portRegistryList, portRegistry)
	}

	return portRegistryList, nil
}

// создаем структуру с одним портом
func makePortRegistryStruct(row map[string]string) (*PortRegistryStruct, error) {

	_, exist := row["port"]
	if !exist {
		return nil, fmt.Errorf("port not exist")
	}

	extra, err := getExtra(row)

	if err != nil {
		return nil, err
	}

	portRegistry := &PortRegistryStruct{
		Port:       functions.StringToInt32(row["port"]),
		Status:     functions.StringToInt(row["status"]),
		Type:       functions.StringToInt(row["type"]),
		LockedTill: functions.StringToInt(row["locked_till"]),
		CreatedAt:  functions.StringToInt(row["created_at"]),
		UpdatedAt:  functions.StringToInt(row["updated_at"]),
		CompanyId:  functions.StringToInt64(row["company_id"]),
		ExtraField: extra,
	}
	portRegistry.initExtra()

	return portRegistry, nil
}

// получаем extra пользователя
func getExtra(row map[string]string) (ExtraField, error) {

	var extra ExtraField

	// получаем extra пользователя
	err := json.Unmarshal([]byte(row["extra"]), &extra)

	extra.isInitialized = false
	return extra, err
}

// SyncPort выполняет синхронизацию порта
// данную функцию нельзя вызывать в локальных методах, только через апи с pivot-сервера
func SyncPort(port, status, lockedTill int32, companyId int64) error {

	if _, err := domino_service.SetStatus(port, int(status), int64(lockedTill), companyId); err != nil {
		return err
	}

	return nil
}
