package AsyncTask

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Isolation "go_event/api/includes/type/isolation"
)

const isolationTaskRepositoryListKey = "async_task.store_list"                         // ключ изоляции для хранилища дискретных контроллеров
const isolationTaskDiscreteControllerStoreKey = "async_task.discrete_controller_store" // ключ изоляции для контроллеров доставки задач

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackage("AsyncTask", nil, isolationInit, isolationInvalidate)
	Isolation.RegPackageGlobal("AsyncTask", nil, isolationInit, isolationInvalidate)
}

// выполняет настройку изоляции,
func isolationInit(isolation *Isolation.Isolation) error {

	// добавляем все нужные данные в изоляцию
	isolation.Set(isolationTaskDiscreteControllerStoreKey, makeDiscreteControllerStore(isolation))
	isolation.Set(isolationTaskRepositoryListKey, makeTaskRepositoryStore(isolation))

	if err := loadTasks(isolation); err != nil {
		log.Errorf("can't load existing task for isolation %s: %s", isolation.GetUniq(), err.Error())
	}

	// цепляем все известные генераторы к изоляции
	attachIsolationToGenerators(isolation)

	return nil
}

// завершает работу изоляции
func isolationInvalidate(isolation *Isolation.Isolation) error {

	// сбрасываем хранилище задач и останавливаем все хранилища
	// жизненный цикл прервется каналом при необходимости в самом хранилище
	isolation.Set(isolationTaskDiscreteControllerStoreKey, nil)

	// сбрасываем хранилище задач
	// и останавливаем все provider рутины
	isolation.Set(isolationTaskRepositoryListKey, nil)

	return nil
}

// --------------------------------------
// функции доступа к изолированным данных
// --------------------------------------

// возвращает хранилище контроллеров из изоляции
func leaseTaskDiscreteControllerStore(isolation *Isolation.Isolation) *discreteControllerStore {

	isolatedValue := isolation.Get(isolationTaskDiscreteControllerStoreKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*discreteControllerStore)
}

// возвращает хранилище хранилищ для задач
func leaseTaskStoreList(isolation *Isolation.Isolation) *taskRepositoryStore {

	isolatedValue := isolation.Get(isolationTaskRepositoryListKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*taskRepositoryStore)
}
