package wsEventHandler

/*
 * Пакет позволяет переводить приходящие из PHP события в стрктуру необходимого handlerVersion
 * Для каждого handlerVersion создается отдельный файл внутри пакета event_handler
 * Файл именуется handler_{handlerVersion}
 */

// объект обработчика handler version 1
var handlerVersion1 handler1

// функция переводит событие пришедшее из PHP в структуру необоходимого handlerVersion
func Translate(event string, eventVersion int64, eventData interface{}, wsUsers interface{}, handlerVersion int, wsUniqueID string) (interface{}, bool) {

	// версионность структур с событиями
	switch handlerVersion {

	// обработчик version 1
	case 1:
		return handlerVersion1.TranslateHandler(event, eventVersion, eventData, wsUsers, wsUniqueID), true
	}

	return nil, false
}
