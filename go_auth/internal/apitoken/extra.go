package apitoken

import (
	"encoding/json"
	"fmt"
)

// версия extra v1
type DataV1 struct {
	TemplateId int64 `json:"template_id"`
}

// интерфейс для всей версий экстры
type ExtraData interface {
	GetVersion() int
	GetTemplateId() int64
	SetTemplateId(int64)
}

// extra поле для токена
type ApiTokenExtra struct {
	Version int       `json:"version"`
	Data    ExtraData `json:"data"`
}

const (
	extraV1             = 1
	currentExtraVersion = 1
)

// инициализировать экстру токена
func InitExtra() (ApiTokenExtra, error) {

	var data ExtraData
	switch currentExtraVersion {
	case extraV1:
		data = &DataV1{}
	default:
		return ApiTokenExtra{}, fmt.Errorf("unknown current extra version")
	}

	data.SetTemplateId(0)

	return ApiTokenExtra{
		Version: currentExtraVersion,
		Data:    data,
	}, nil
}

// маршалинг json для экстры
func (e *ApiTokenExtra) MarshalJSON() ([]byte, error) {

	return json.Marshal(struct {
		Version int `json:"version"`
		Data    any `json:"data"`
	}{
		Version: e.Version,
		Data:    e.Data,
	})
}

// анмаршалинг json для экстры
func (e *ApiTokenExtra) UnmarshalJSON(data []byte) error {

	var temp struct {
		Version int             `json:"version"`
		Data    json.RawMessage `json:"data"`
	}

	if err := json.Unmarshal(data, &temp); err != nil {
		return err
	}

	e.Version = temp.Version
	switch temp.Version {
	case extraV1:

		var dataV1 DataV1
		if err := json.Unmarshal(temp.Data, &dataV1); err != nil {
			return err
		}

		e.Data = &dataV1
	default:
		return fmt.Errorf("unknown extra version")
	}

	return nil
}

// получит версию для extra v1
func (d *DataV1) GetVersion() int {
	return extraV1
}

// получить идентификатор темплейта в extra v1
func (d *DataV1) GetTemplateId() int64 {
	return d.TemplateId
}

// изменить идентификатор темплейта в extra v1
func (d *DataV1) SetTemplateId(templateId int64) {
	d.TemplateId = templateId
}
