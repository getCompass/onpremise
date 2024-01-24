package EventBroker

import (
	Isolation "go_event/api/includes/type/isolation"
)

const isolationDiscreteStoreKey = "event_broker.discrete_store"          // ключ изоляции для хранилища дискретных контроллеров
const isolationListenerListStoreKey = "event_broker.listener_list_store" // ключ изоляции для хранилища списков слушателей

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackage("EventBroker", nil, isolationInit, isolationInvalidate)
	Isolation.RegPackageGlobal("EventBroker", nil, isolationInitGlobal, isolationInvalidateGlobal)
}

// выполняет настройку изоляции,
func isolationInit(isolation *Isolation.Isolation) error {

	// дискретное хранилище используется для доставки событий пачками
	// все получателям с соответствующей подпиской
	isolation.Set(isolationDiscreteStoreKey, makeDiscreteControllerStore(isolation))

	// для изоляции фиксируем список подписчиков
	// нет смысла делать разные списки, поэтому просто используем глобальный
	// в будущем можно будет распилить как-нибудь при необходимости
	isolation.Set(isolationListenerListStoreKey, &globalListenerListStore)

	// цепляем все известные генераторы к изоляции
	attachIsolationToGenerators(isolation)

	return nil
}

// завершает работу изоляции
func isolationInvalidate(isolation *Isolation.Isolation) error {

	// сбрасываем хранилище задач и останавливаем все хранилища
	// жизненный цикл прервется каналом при необходимости в самом хранилище
	isolation.Set(isolationDiscreteStoreKey, nil)

	// сбрасываем хранилище задач
	// и останавливаем все provider рутины
	isolation.Set(isolationListenerListStoreKey, nil)

	return nil
}

// выполняет настройку изоляции,
// чтобы брокер событий мог в дальнейшем с ней работать
func isolationInitGlobal(isolation *Isolation.Isolation) error {

	// дискретное хранилище используется для доставки событий пачками
	// все получателям с соответствующей подпиской
	isolation.Set(isolationDiscreteStoreKey, makeDiscreteControllerStore(isolation))

	// для изоляции фиксируем список подписчиков
	// нет смысла делать разные списки, поэтому просто используем глобальный
	// в будущем можно будет распилить как-нибудь при необходимости
	isolation.Set(isolationListenerListStoreKey, &globalListenerListStore)

	return nil
}

// завершает работу изоляции,
// чтобы брокер перестал обслуживать все, что с ней связано
func isolationInvalidateGlobal(isolation *Isolation.Isolation) error {

	// подчищаем изоляцию от данных
	isolation.Set(isolationDiscreteStoreKey, nil)
	isolation.Set(isolationListenerListStoreKey, nil)

	return isolationInvalidate(isolation)
}
