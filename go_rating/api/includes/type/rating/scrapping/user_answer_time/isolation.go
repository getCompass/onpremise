package user_answer_time

import (
	Isolation "go_rating/api/includes/type/isolation"
)

const isolationUserAnswerTimeInstanceKey = "useranswertime.useranswertime_instance" // ключ экземпляром useranswertime

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackage("useranswertime", nil, isolationInit, isolationInvalidate)
	Isolation.RegPackageGlobal("useranswertime", nil, isolationInitGlobal, isolationInvalidate)
}

// инициализация изоляции
func isolationInit(isolation *Isolation.Isolation) error {

	isolation.Set(isolationUserAnswerTimeInstanceKey, makeUserAnswerTime(isolation))
	return nil
}

// инициализация глобальной изоляции
func isolationInitGlobal(isolation *Isolation.Isolation) error {

	isolation.Set(isolationUserAnswerTimeInstanceKey, makeUserAnswerTime(isolation))
	return nil
}

// инвалидация изоляции
// глобальная изоляция не содержит уникальных ключей, поэтому инвалидция общая
func isolationInvalidate(isolation *Isolation.Isolation) error {

	isolation.Set(isolationUserAnswerTimeInstanceKey, nil)
	return nil
}

// --------------------------------------
// функции доступа к изолированным данных
// --------------------------------------

// возвращает экземпляр useranswertime
func leaseUserAnswerTime(isolation *Isolation.Isolation) *useranswertime {

	isolatedValue := isolation.Get(isolationUserAnswerTimeInstanceKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*useranswertime)
}
