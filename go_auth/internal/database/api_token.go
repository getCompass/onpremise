package database

import (
	"encoding/json"
	"fmt"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

// ApiTokenRecord структура для чтения из таблицы
type ApiTokenRecord struct {
	UserId       int64           `json:"user_id,omitempty"`    // идентификатор пользователя
	ApiToken     string          `json:"api_token,omitempty"`  // api токен
	CreatedAt    int64           `json:"created_at,omitempty"` // дата создания токена
	UpdatedAt    int64           `json:"updated_at,omitempty"` // дата обновления токена
	ExpiresAt    int64           `json:"expires_at,omitempty"` // дата протухания токена
	Name         string          `json:"name,omitempty"`       // название токена
	ScopeListInt json.RawMessage `json:"scope_list,omitempty"` // список зон ответственности токена
	Extra        json.RawMessage `json:"extra,omitempty"`      // данные токена
}

// поля для таблицы api_token_list
var apiTokenFieldList = []string{"user_id", "api_token", "created_at", "updated_at", "expires_at", "name", "scope_list", "extra"}

const apiTokenTable = "api_token_list"

// GetApiToken получает токен из базы
func (d *Database) GetApiToken(userId int64, apiToken string) (*ApiTokenRecord, error) {

	fieldStr := ""

	for _, field := range apiTokenFieldList {
		fieldStr += fmt.Sprintf("`%s` ,", field)
	}

	fieldStr = fieldStr[:len(fieldStr)-2]

	// EXPLAIN PRIMARY
	query := fmt.Sprintf("SELECT %s FROM `%s` WHERE `user_id` = ? AND `api_token` = ? LIMIT ?", fieldStr, apiTokenTable)

	fl := make([]any, 0)
	fl = append(fl, userId, apiToken, 1)

	r := &ApiTokenRecord{}
	err := d.dbConnection.QueryRow(query, fl...).Scan(
		&r.UserId,
		&r.ApiToken,
		&r.CreatedAt,
		&r.UpdatedAt,
		&r.ExpiresAt,
		&r.Name,
		&r.ScopeListInt,
		&r.Extra,
	)

	return r, err
}

// GetApiTokenList загружает токены из базы данных
// @long - структура строки
func (d *Database) GetApiTokenList(userId int64) ([]*ApiTokenRecord, error) {

	fieldStr := ""

	for _, field := range apiTokenFieldList {
		fieldStr += fmt.Sprintf("`%s`, ", field)
	}

	fieldStr = fieldStr[:len(fieldStr)-2]

	// EXPLAIN PRIMARY
	query := fmt.Sprintf("SELECT %s FROM `%s` WHERE `user_id` = ? LIMIT ?", fieldStr, apiTokenTable)

	fl := make([]any, 0)
	fl = append(fl, userId, 1000)

	rows, err := d.dbConnection.Query(query, fl...)

	if err != nil {
		return nil, err
	}

	result := make([]*ApiTokenRecord, 0)
	for rows.Next() {

		record := &ApiTokenRecord{}

		err = rows.Scan(
			&record.UserId,
			&record.ApiToken,
			&record.CreatedAt,
			&record.UpdatedAt,
			&record.ExpiresAt,
			&record.Name,
			&record.ScopeListInt,
			&record.Extra,
		)

		if err != nil {
			continue
		}

		result = append(result, record)
	}
	if err != nil {
		return nil, err
	}

	return result, err
}

// CreateApiToken создаем api токен
func (d *Database) CreateApiToken(apiToken *ApiTokenRecord) (*ApiTokenRecord, error) {

	fieldStr := ""
	placeholderStr := ""

	for _, field := range apiTokenFieldList {
		fieldStr += fmt.Sprintf("`%s` ,", field)
		placeholderStr += "?, "
	}

	fieldStr = fieldStr[:len(fieldStr)-2]
	placeholderStr = placeholderStr[:len(placeholderStr)-2]

	query := fmt.Sprintf("INSERT IGNORE INTO %s (%s) VALUES (%s)", apiTokenTable, fieldStr, placeholderStr)

	_, err := d.dbConnection.Exec(
		query,
		apiToken.UserId,
		apiToken.ApiToken,
		apiToken.CreatedAt,
		apiToken.UpdatedAt,
		apiToken.ExpiresAt,
		apiToken.Name,
		apiToken.ScopeListInt,
		apiToken.Extra,
	)

	if err != nil {
		return nil, err
	}

	return apiToken, err
}

func (d *Database) UpdateApiToken(apiToken *ApiTokenRecord) (*ApiTokenRecord, error) {

	apiToken.UpdatedAt = time.Now().Unix()

	query := fmt.Sprintf("UPDATE %s SET `updated_at` = ?, `expires_at` = ?, `name` = ?, `scope_list` = ?, `extra` = ? WHERE `user_id` = ? AND `api_token` = ? LIMIT ?", apiTokenTable)

	_, err := d.dbConnection.Exec(
		query,
		apiToken.UpdatedAt,
		apiToken.ExpiresAt,
		apiToken.Name,
		apiToken.ScopeListInt,
		apiToken.Extra,
		apiToken.UserId,
		apiToken.ApiToken,
		1,
	)

	if err != nil {
		return nil, err
	}

	return apiToken, err
}

// DeleteApiToken удаляет api токен
func (d *Database) DeleteApiToken(userId int64, apiToken string) error {

	// EXPLAIN INDEX PRIMARY
	query := fmt.Sprintf("DELETE FROM `%s` WHERE `user_id` = ? AND `api_token` = ? LIMIT ?", apiTokenTable)
	_, err := d.dbConnection.Exec(query, userId, apiToken, 1)

	return err
}

// DeleteApiToken удаляет api токен
func (d *Database) OptimizeApiTokenTable() error {

	// EXPLAIN INDEX PRIMARY
	query := fmt.Sprintf("OPTIMIZE TABLE %s", apiTokenTable)
	_, err := d.dbConnection.Exec(query)

	return err
}

// CountApiTokensByExpiresAt считает просроченные api токены
func (d *Database) CountApiTokensByExpiresAt(expiresAt int64) int {

	var count int

	// EXPLAIN INDEX expires_at
	query := fmt.Sprintf("SELECT COUNT(*) FROM `%s` WHERE `expires_at` < ? LIMIT ?", apiTokenTable)
	err := d.dbConnection.QueryRow(query, expiresAt, 1).Scan(&count)

	if err != nil {

		log.Errorf("couldnt count expired tokens because of database error")
		return 0
	}

	return count
}

// DeleteExpiredApiTokens удаляет просроченные api токены
func (d *Database) DeleteApiTokensByExpiresAt(expiresAt int64, limit int) error {

	// EXPLAIN INDEX expires_at
	query := fmt.Sprintf("DELETE FROM `%s` WHERE `expires_at` < ? LIMIT ?", apiTokenTable)
	_, err := d.dbConnection.Exec(query, expiresAt, limit)

	return err
}
