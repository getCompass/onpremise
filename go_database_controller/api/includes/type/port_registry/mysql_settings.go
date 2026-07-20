package port_registry

import (
	"context"
	"encoding/json"
	"fmt"
	"go_database_controller/api/includes/type/db/domino_service"
)

// MysqlSettingsStruct хранит опциональные настройки MySQL для конкретного порта компании.
type MysqlSettingsStruct struct {
	InnodbBufferPoolSizeMb  *int `json:"innodb_buffer_pool_size_mb,omitempty"`
	InnodbThreadConcurrency *int `json:"innodb_thread_concurrency,omitempty"`
	TableOpenCache          *int `json:"table_open_cache,omitempty"`
}

// ParseMysqlSettingsJSON валидирует настройки, пришедшие из PHP.
func ParseMysqlSettingsJSON(raw string) (*MysqlSettingsStruct, error) {

	if raw == "" {
		return nil, nil
	}

	settings := &MysqlSettingsStruct{}
	if err := json.Unmarshal([]byte(raw), settings); err != nil {
		return nil, fmt.Errorf("invalid mysql settings json: %w", err)
	}

	if err := settings.Validate(); err != nil {
		return nil, err
	}

	return settings, nil
}

// Validate проверяет диапазоны значений.
func (settings *MysqlSettingsStruct) Validate() error {

	if settings == nil {
		return nil
	}

	if settings.InnodbBufferPoolSizeMb != nil && *settings.InnodbBufferPoolSizeMb < 1 {
		return fmt.Errorf("innodb_buffer_pool_size_mb must be greater than 0")
	}

	if settings.InnodbThreadConcurrency != nil && *settings.InnodbThreadConcurrency < 0 {
		return fmt.Errorf("innodb_thread_concurrency must be greater than or equal to 0")
	}

	if settings.TableOpenCache != nil && *settings.TableOpenCache < 1 {
		return fmt.Errorf("table_open_cache must be greater than 0")
	}

	return nil
}

// SetMysqlSettings сохраняет настройки MySQL в extra порта.
func SetMysqlSettings(ctx context.Context, portValue int32, host string, companyId int64, mysqlSettingsJSON string) error {

	settings, err := ParseMysqlSettingsJSON(mysqlSettingsJSON)
	if err != nil {
		return err
	}

	if settings == nil {
		return nil
	}

	port, err := GetByPort(ctx, portValue, host)
	if err != nil {
		return err
	}

	if companyId > 0 && port.CompanyId != companyId {
		return fmt.Errorf("port %d host %s is bound to company %d, expected %d", portValue, host, port.CompanyId, companyId)
	}

	if port.ExtraField.Version != _handler1 {
		return fmt.Errorf("unsupported port extra version %d", port.ExtraField.Version)
	}

	extraBody := port.ExtraField.ExtraBody.(extraHandlerVersion1)
	extraBody.MysqlSettings = settings

	rawExtraBody, err := json.Marshal(extraBody)
	if err != nil {
		return err
	}

	rawExtra, err := json.Marshal(struct {
		Version int             `json:"version"`
		Extra   json.RawMessage `json:"extra"`
	}{
		Version: _handler1,
		Extra:   rawExtraBody,
	})
	if err != nil {
		return err
	}

	_, err = domino_service.UpdateExtra(ctx, portValue, host, string(rawExtra))
	return err
}
