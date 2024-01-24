package device

import (
	"encoding/json"
)

// константы с версиями
const (
	handler1 = 1
	handler2 = 2
)

// структура поля extra
type extraField struct {
	Version       int             `json:"handler_version"`
	Extra         json.RawMessage `json:"extra"`
	isInitialized bool
	extraBody     interface{}
}

// получить список токенов девайса
func (d *DeviceStruct) GetTokenList() []TokenItem {

	d.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case handler1:
		return d.ExtraField.extraBody.(extraHandlerVersion1).getTokenList()
	case handler2:
		return d.ExtraField.extraBody.(extraHandlerVersion2).getTokenList()
	}

	return []TokenItem{}
}

// установить список токенов девайса
func (d *DeviceStruct) SetTokenList(tokenList []TokenItem) {

	d.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case handler1:
		tokenList := d.ExtraField.extraBody.(extraHandlerVersion1).setTokenList(tokenList)
		d.saveExtra(tokenList)
	case handler2:
		tokenList := d.ExtraField.extraBody.(extraHandlerVersion2).setTokenList(tokenList)
		d.saveExtra(tokenList)
	}

}

// список токенов компании для пушей
func (d *DeviceStruct) GetUserCompanyTokenList() []string {

	d.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case handler1:
		return d.ExtraField.extraBody.(extraHandlerVersion1).getUserCompanyTokenPushList()
	case handler2:
		return d.ExtraField.extraBody.(extraHandlerVersion2).getUserCompanyTokenPushList()
	}

	return []string{}
}

// инициализируем, распаковываем входящую extra
func (d *DeviceStruct) initExtra() {

	// если уже инициализирована
	if d.ExtraField.isInitialized {
		return
	}

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case handler1:

		temp := extraHandlerVersion1{}

		err := json.Unmarshal(d.ExtraField.Extra, &temp)
		if err != nil {
			panic(err)
		}

		d.ExtraField.extraBody = temp

	case handler2:

		temp := extraHandlerVersion2{}

		err := json.Unmarshal(d.ExtraField.Extra, &temp)
		if err != nil {
			panic(err)
		}

		d.ExtraField.extraBody = temp
	}

	// помечаем, что инициализирована
	d.ExtraField.isInitialized = true
}

// запаковывем extra
func (d *DeviceStruct) saveExtra(extraHandler interface{}) {

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case handler1, handler2:

		extraJson, err := json.Marshal(extraHandler)
		if err != nil {
			panic(err)
		}

		d.ExtraField.Extra = extraJson
	}

	// помечаем, что extra закрыта
	d.ExtraField.isInitialized = false
}
