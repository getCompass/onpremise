package domino_service

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_database_controller/api/system/sharding"
)

const portRegistryTableKey = "port_registry"
const maxPortCount = 10000

// GetAllCompanyPortList получаем все записи из базы
func GetAllCompanyPortList(ctx context.Context) (map[int]map[string]string, error) {

	// проверяем, что у нас имеется подключение к необходимой базе данных
	conn := sharding.Mysql(ctx, GetDbName())
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", GetDbName())
	}

	var queryArgs []interface{}
	queryArgs = append(queryArgs, 0)
	queryArgs = append(queryArgs, maxPortCount)

	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `company_id` != ? LIMIT ?", portRegistryTableKey)
	rows, err := conn.GetAll(ctx, query, queryArgs...)
	if err != nil {
		return nil, fmt.Errorf("неудачный запрос: %s в базу %s Error: %v", query, GetDbName(), err)
	}

	return rows, nil
}

// GetOne получаем одну запись по порту
func GetOne(ctx context.Context, port int32, host string) (map[string]string, error) {

	// проверяем, что у нас имеется подключение к необходимой базе данных
	conn := sharding.Mysql(ctx, GetDbName())
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", GetDbName())
	}

	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `port` = ? AND `host` = ? LIMIT ?", portRegistryTableKey)
	row, err := conn.FetchQuery(ctx, query, port, host, 1)
	if err != nil {
		return nil, fmt.Errorf("неудачный запрос: %s в базу %s Error: %v", query, GetDbName(), err)
	}

	return row, nil
}

// GetOneWithStatusByCompanyId получаем одну запись по компании с указанным статусом
func GetOneWithStatusByCompanyId(ctx context.Context, companyId int64, status int) (map[string]string, error) {

	// проверяем, что у нас имеется подключение к необходимой базе данных
	conn := sharding.Mysql(ctx, GetDbName())
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", GetDbName())
	}

	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `status` = ? AND `company_id` = ? LIMIT ?", portRegistryTableKey)
	row, err := conn.FetchQuery(ctx, query, status, companyId, 1)
	if err != nil {
		return nil, fmt.Errorf("неудачный запрос: %s в базу %s Error: %v", query, GetDbName(), err)
	}

	return row, nil
}

// SetStatus метод, для обновления записи которая с локом
func SetStatus(ctx context.Context, port int32, host string, status int, lockedTill int64, companyId int64) (int64, error) {

	// проверяем, что у нас имеется подключение к необходимой базе данных
	conn := sharding.Mysql(ctx, GetDbName())
	if conn == nil {
		return 0, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", GetDbName())
	}

	// совершаем запрос
	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("UPDATE `%s` SET "+
		"`updated_at` = ?, "+
		"`status` = ?, "+
		"`company_id` = ?, "+
		"`locked_till` = ? "+
		"WHERE `port` = ? AND host = ? LIMIT %d", portRegistryTableKey, 1)
	return conn.Update(ctx, query, functions.GetCurrentTimeStamp(), status, companyId, lockedTill, port, host)
}

// UpdateStatus метод, для обновления записи
func UpdateStatus(ctx context.Context, port int32, host string, status int) (int64, error) {

	// проверяем, что у нас имеется подключение к необходимой базе данных
	conn := sharding.Mysql(ctx, GetDbName())
	if conn == nil {
		return 0, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", GetDbName())
	}

	// совершаем запрос
	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf(fmt.Sprintf("UPDATE `%s` SET `updated_at` = ?, `status` = ? WHERE `port` = ? AND `host` = ? LIMIT %d", portRegistryTableKey, 1))
	return conn.Update(ctx, query, functions.GetCurrentTimeStamp(), status, port, host)
}

// InsertIgnoreOne создать запись
func InsertIgnoreOne(ctx context.Context, port int32, host string, status int32, portType int32, lockedTill int32, createdAt int32, updatedAt int32, companyId int64, extra string) error {

	conn := sharding.Mysql(ctx, GetDbName())
	if conn == nil {
		return fmt.Errorf("пришел DbName: %s для которого не найдено подключение", GetDbName())
	}

	query := fmt.Sprintf("INSERT IGNORE INTO `%s` (`port`, `host`, `status`, `type`, `locked_till`, `created_at`, `updated_at`, `company_id`,`extra`) "+
		"VALUES (?,?,?,?,?,?,?,?,?)", portRegistryTableKey)

	return conn.Query(ctx, query, port, host, status, portType, lockedTill, createdAt, updatedAt, companyId, extra)
}

// GetServicePortForUpdate получаем одну запись с сервисным портом
func (tx *TransactionStruct) GetServicePortForUpdate(ctx context.Context, status int) (map[string]string, error) {

	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `company_id` = ? AND `status` = ? LIMIT ? FOR UPDATE", portRegistryTableKey)
	row, err := tx.transaction.FetchQuery(ctx, query, 0, status, 1)
	if err != nil {
		return nil, fmt.Errorf("неудачный запрос: %s в базу %s Error: %v", query, GetDbName(), err)
	}
	return row, nil
}

// Update обновляем запись, занимая порт
func (tx *TransactionStruct) Update(ctx context.Context, port int32, status int, lockedTill int64, companyId int64) error {

	query := fmt.Sprintf("UPDATE `%s` SET `company_id` = ?, `status` = ?, `locked_till` = ? WHERE `port` = ? LIMIT ?", portRegistryTableKey)
	count, err := tx.transaction.Update(ctx, query, companyId, status, lockedTill, port, 1)
	if err != nil {
		return fmt.Errorf("неудачный запрос: %s в базу %s Error: %v", query, GetDbName(), err)
	}

	if count < 1 {
		return fmt.Errorf("не обновили не одной записи запрос: %s в базу %s", query, GetDbName())
	}

	return nil
}
