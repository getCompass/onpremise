package AsyncTrap

// Пакет для отслеживания обработанных событий или задач.
// Работает в связки с брокерами в компании и позволяет отслеживать завершение обработки того или иного действия.

import Isolation "go_event/api/includes/type/isolation"

const isolationEventHunterKey = "event_trap.event_hunter" // ключ изоляции для хранилища дискретных контроллеров

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackage("CompanyEnvironment", nil, isolationInit, isolationInvalidate)
	Isolation.RegPackageGlobal("GlobalEnvironment", nil, isolationInit, isolationInvalidate)
}

// выполняет настройку изоляции,
// чтобы в дальнейшем ловушки событий могли нормально отрабатывать
func isolationInit(context *Isolation.Isolation) error {

	// добавляем в контекст ловушки
	// сюда будут попадать все события, которые были разосланы в изоляции
	context.Set(isolationEventHunterKey, &eventHunter{
		trapList: map[string]*asyncTrap{},
	})

	return nil
}

// завершает работу изоляции,
// чтобы ловушки событий перестали обслуживать все, что с ней связано
func isolationInvalidate(context *Isolation.Isolation) error {

	// сбрасываем данные изоляции
	context.Set(isolationEventHunterKey, nil)
	return nil
}

// --------------------------------------
// функции доступа к изолированным данных
// --------------------------------------

// возвращает хранилище контроллеров из изоляции
func leaseEventHunter(isolation *Isolation.Isolation) *eventHunter {

	isolatedValue := isolation.Get(isolationEventHunterKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*eventHunter)
}
