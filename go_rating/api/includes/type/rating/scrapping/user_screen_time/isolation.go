package user_screen_time

import (
	Isolation "go_rating/api/includes/type/isolation"
)

const isolationUserScreenTimeInstanceKey = "userscreentime.userscreentime_instance" // ключ экземпляром userscreentime

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackage("userscreentime", nil, isolationInit, isolationInvalidate)
	Isolation.RegPackageGlobal("userscreentime", nil, isolationInitGlobal, isolationInvalidate)
}

// инициализация изоляции
func isolationInit(isolation *Isolation.Isolation) error {

	isolation.Set(isolationUserScreenTimeInstanceKey, makeUserScreenTime(isolation))
	return nil
}

// инициализация глобальной изоляции
func isolationInitGlobal(isolation *Isolation.Isolation) error {

	isolation.Set(isolationUserScreenTimeInstanceKey, makeUserScreenTime(isolation))
	return nil
}

// инвалидация изоляции
// глобальная изоляция не содержит уникальных ключей, поэтому инвалидция общая
func isolationInvalidate(isolation *Isolation.Isolation) error {

	isolation.Set(isolationUserScreenTimeInstanceKey, nil)
	return nil
}

// --------------------------------------
// функции доступа к изолированным данных
// --------------------------------------

// возвращает экземпляр userscreentime
func leaseUserScreenTime(isolation *Isolation.Isolation) *userscreentime {

	isolatedValue := isolation.Get(isolationUserScreenTimeInstanceKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*userscreentime)
}
