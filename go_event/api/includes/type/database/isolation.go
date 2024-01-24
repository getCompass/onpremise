package Database

import Isolation "go_event/api/includes/type/isolation"

const isolationSystemConnectionKey = "database_connection.system"

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackage("Database", nil, isolationInit, isolationInvalidate)
	Isolation.RegPackageGlobal("Database", nil, isolationInitGlobal, isolationInvalidateGlobal)
}

// инициализирует изоляцию
func isolationInit(isolation *Isolation.Isolation) error {

	connection, err := makeCompanyConnection(isolation, "company_system")
	if err != nil {
		return err
	}

	// добавляем все нужные данные в изоляцию
	isolation.Set(isolationSystemConnectionKey, connection)
	return nil
}

// инвалидирует хранилище изоляции
func isolationInvalidate(isolation *Isolation.Isolation) error {

	// закрываем подключение к базе с задачами
	if connection := LeaseSystemConnection(isolation); connection != nil {
		connection.close()
	}

	// очищаем хранилище изоляции
	isolation.Set(isolationSystemConnectionKey, nil)

	return nil
}

// инициализирует изоляцию
func isolationInitGlobal(isolation *Isolation.Isolation) error {

	connection, err := makeGlobalConnection(isolation)
	if err != nil {
		return err
	}

	// добавляем все нужные данные в изоляцию
	isolation.Set(isolationSystemConnectionKey, connection)

	return nil
}

// инициализирует изоляцию
func isolationInvalidateGlobal(isolation *Isolation.Isolation) error {

	// закрываем подключение к базе с задачами
	if connection := LeaseSystemConnection(isolation); connection != nil {
		connection.close()
	}

	return nil
}

// --------------------------------------
// функции доступа к изолированным данных
// --------------------------------------

// LeaseSystemConnection возвращает хранилище хранилищ для задач
func LeaseSystemConnection(isolation *Isolation.Isolation) *Connection {

	isolatedValue := isolation.Get(isolationSystemConnectionKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*Connection)
}
