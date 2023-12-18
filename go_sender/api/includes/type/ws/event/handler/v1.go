package wsEventHandler

import "time"

/*
 * Файл содержит логику перевода структуры событий в структуру версии 1
 * Изменения по сравнению с прошлой версией (версией под номер 0):
 * - WS Сообщения разделены на две группы: action, event
 *
 * Изменена структура события:
 * - Тип WS сообщения записывается в параметр ws_method
 * - Тело WS сообщения записывается в параметр ws_data
 */

// структура объекта
type handler1 struct{}

// любое событие данного хендлера имеет этот скелет
type handler1AnyEventStruct struct {
	WSMethod        string      `json:"ws_method"`
	WSMethodVersion int64       `json:"ws_method_version"`
	WSData          interface{} `json:"ws_data"`
	WSUniqueID      string      `json:"ws_unique_id,omitempty"`
	WSUsers         interface{} `json:"ws_users,omitempty"`
	ServerTime      int64       `json:"server_time"`
}

func (h *handler1) TranslateHandler(event string, methodVersion int64, eventData interface{}, wsUsers interface{}, wsUniqueID string) interface{} {

	// формируем структуру
	translatedStructure := handler1AnyEventStruct{
		WSMethod:        event,
		WSMethodVersion: methodVersion,
		WSData:          eventData,
		WSUniqueID:      wsUniqueID,
		WSUsers:         wsUsers,
		ServerTime:      time.Now().Unix(),
	}

	// возвращаем
	return translatedStructure
}
