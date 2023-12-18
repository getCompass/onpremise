package Database

import Isolation "go_rating/api/includes/type/isolation"

const isolationConversationConnectionKey = "database_connection.conversation"

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackage("Database", nil, isolationInit, isolationInvalidate)
	Isolation.RegPackageGlobal("Database", nil, isolationInitGlobal, isolationInvalidateGlobal)
}

// инициализирует изоляцию
func isolationInit(isolation *Isolation.Isolation) error {

	connection, err := makeCompanyConnection(isolation, "company_conversation")
	if err != nil {
		return err
	}

	// добавляем все нужные данные в изоляцию
	isolation.Set(isolationConversationConnectionKey, connection)
	return nil
}

// инвалидирует хранилище изоляции
func isolationInvalidate(isolation *Isolation.Isolation) error {

	// закрываем подключение к базе
	if connection := LeaseConversationConnection(isolation); connection != nil {
		connection.close()
	}

	// очищаем хранилище изоляции
	isolation.Set(isolationConversationConnectionKey, nil)

	return nil
}

// инициализирует изоляцию
func isolationInitGlobal(isolation *Isolation.Isolation) error {

	connection, err := makeGlobalConnection(isolation)
	if err != nil {
		return err
	}

	// добавляем все нужные данные в изоляцию
	isolation.Set(isolationConversationConnectionKey, connection)

	return nil
}

// инициализирует изоляцию
func isolationInvalidateGlobal(isolation *Isolation.Isolation) error {

	// закрываем подключение к базе
	if connection := LeaseConversationConnection(isolation); connection != nil {
		connection.close()
	}

	return nil
}

// --------------------------------------
// функции доступа к изолированным данных
// --------------------------------------

// LeaseConversationConnection возвращает хранилище хранилищ
func LeaseConversationConnection(isolation *Isolation.Isolation) *Connection {

	isolatedValue := isolation.Get(isolationConversationConnectionKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*Connection)
}
