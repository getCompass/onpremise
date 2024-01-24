package company_data

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"strings"
)

const companyConfigTableKey = "company_config"

type KeyRow struct {
	Key       string `json:"key"`
	CreatedAt int32  `json:"created_at"`
	UpdatedAt int32  `json:"updated_at"`
	Value     string `json:"value"`
}

// GetKeyRow получаем строчку конфига по ключу
func (dbConn *DbConn) GetKeyRow(ctx context.Context, key string) (*KeyRow, error) {

	var row KeyRow

	// проверяем, что у нас имеется подключение к необходимой базе данных
	query := fmt.Sprintf("SELECT `key`, `created_at`, `updated_at`, `value` FROM `%s` WHERE `key` = ? LIMIT ?", companyConfigTableKey)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := dbConn.Conn.QueryRowContext(queryCtx, query, key, 1).Scan(
		&row.Key,
		&row.CreatedAt,
		&row.UpdatedAt,
		&row.Value,
	)

	if err != nil {

		if err == sql.ErrNoRows {

			log.Infof("Row with key %s not found", key)
			return nil, nil
		}
		return nil, err
	}

	return &row, nil
}

// GetKeyList получаем по массиву ключей
func (dbConn *DbConn) GetKeyList(ctx context.Context, keyList []string) ([]*KeyRow, error) {

	var plugForIn []string
	var queryArgs []interface{}
	for _, v := range keyList {

		plugForIn = append(plugForIn, "?")
		queryArgs = append(queryArgs, v)
	}

	queryArgs = append(queryArgs, len(plugForIn))
	query := fmt.Sprintf("SELECT `key`, `created_at`, `updated_at`, `value` FROM `%s` WHERE `key` IN (%s) LIMIT ?", companyConfigTableKey, strings.Join(plugForIn, ","))

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)

	results, err := dbConn.Conn.QueryContext(queryCtx, query, queryArgs...)
	defer func() { _ = results.Close(); cancel() }()

	if err != nil {

		log.Errorf(err.Error())
		return nil, err
	}

	keyRowList := make([]*KeyRow, 0, len(plugForIn))

	for results.Next() {

		var row KeyRow

		err = results.Scan(&row.Key,
			&row.CreatedAt,
			&row.UpdatedAt,
			&row.Value,
		)
		if err != nil {
			return nil, err
		}

		keyRowList = append(keyRowList, &row)
	}
	return keyRowList, nil
}
