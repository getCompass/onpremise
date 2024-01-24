package user_action

import (
	Isolation "go_rating/api/includes/type/isolation"
)

const isolationUserActionInstanceKey = "useraction.useraction_instance" // ключ экземпляром useraction

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackage("useraction", nil, isolationInit, isolationInvalidate)
	Isolation.RegPackageGlobal("useraction", nil, isolationInitGlobal, isolationInvalidate)
}

// инициализация изоляции
func isolationInit(isolation *Isolation.Isolation) error {

	isolation.Set(isolationUserActionInstanceKey, makeUserAction(isolation))

	return nil
}

// инициализация глобальной изоляции
func isolationInitGlobal(isolation *Isolation.Isolation) error {

	isolation.Set(isolationUserActionInstanceKey, makeUserAction(isolation))

	return nil
}

// инвалидация изоляции
// глобальная изоляция не содержит уникальных ключей, поэтому инвалидация общая
func isolationInvalidate(isolation *Isolation.Isolation) error {

	isolation.Set(isolationUserActionInstanceKey, nil)
	return nil
}

// --------------------------------------
// функции доступа к изолированным данных
// --------------------------------------

// возвращает экземпляр useraction
func leaseUserAction(isolation *Isolation.Isolation) *useraction {

	isolatedValue := isolation.Get(isolationUserActionInstanceKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*useraction)
}
