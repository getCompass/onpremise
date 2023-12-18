package port_registry

import (
	"encoding/json"
)

// константы с версиями
const (
	_handler1 = 1
)

// структура поля extra
type ExtraField struct {
	Version       int             `json:"version"`
	Extra         json.RawMessage `json:"extra"`
	isInitialized bool
	ExtraBody     interface{}
}

// инициализируем, распаковываем входящую extra
func (port *PortRegistryStruct) initExtra() {

	// если уже инициализирована
	if port.ExtraField.isInitialized {
		return
	}

	// в зависимости от версии extra вызываем нужный обработчик
	switch port.ExtraField.Version {

	case _handler1:

		temp := extraHandlerVersion1{}

		err := json.Unmarshal(port.ExtraField.Extra, &temp)

		if err != nil {
			panic(err)
		}

		port.ExtraField.ExtraBody = temp
	}

	// помечаем, что инициализирована
	port.ExtraField.isInitialized = true
}

// GetEncryptedUser вернуть юзера
func (port *PortRegistryStruct) GetEncryptedUser() string {

	// инициализируем extra
	port.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch port.ExtraField.Version {

	case _handler1:
		return port.ExtraField.ExtraBody.(extraHandlerVersion1).EncryptedMysqlUser
	}

	return ""
}

// GetEncryptedPassword вернуть пароль
func (port *PortRegistryStruct) GetEncryptedPassword() string {

	// инициализируем extra
	port.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch port.ExtraField.Version {

	case _handler1:
		return port.ExtraField.ExtraBody.(extraHandlerVersion1).EncryptedMysqlPass
	}

	return ""
}
