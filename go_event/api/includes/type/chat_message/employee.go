package ChatMessage

/** Пакет работы с сообщениями */
/** В файле описаны:
  — структуры для сообщений метрик карточки
  — методы объявления сообщений соответствующих типов
  — вспомогательные методы для работы с экстрой **/

// типы поддерживаемых сообщений
type EmployeeMessageTypeListStruct struct {
	EditorAnniversaryMessage           string // годовщина работы в компании, отсылается руководителю
	EditorWorksheetRating              string // отчет по рабочим часам за период для руководителя
	SystemBotMessagesMovedNotification string // смена типа чата оповещения
}

// структура сообщения с изменением общего времени в компании ПО сотруднику
// получает руководитель
type EmployeeEditorAnniversaryMessage struct {
	Type           string `json:"type"`
	EmployeeUserId int64  `json:"employee_user_id"`
	Text           string `json:"text"`
	HiredAt        int64  `json:"hired_at"`
}

// тип — пользвоатель время в сообщении
type EmployeeEditorWorksheetRatingUserItem struct {
	UserId   int64 `json:"user_id"`
	WorkTime int64 `json:"work_time"`
}

// сообщение с рейтингом отработанных часов
// получает руководитель
type EmployeeEditorWorksheetRatingMessage struct {
	Type                   string                                  `json:"type"`
	Text                   string                                  `json:"text"`
	PeriodId               int                                     `json:"period_id"`                  // за какой период это дело (в текущей реализации ид спринта)
	PeriodStartDate        int64                                   `json:"period_start_date"`          // начало отчетного периода
	PeriodEndDate          int64                                   `json:"period_end_date"`            // конец отчетного периода
	LeaderUserWorkItemList []EmployeeEditorWorksheetRatingUserItem `json:"leader_user_work_item_list"` // список больше всех
	DrivenUserWorkItemList []EmployeeEditorWorksheetRatingUserItem `json:"driven_user_work_item_list"` // список меньше всех
}

// сообщение, связанное с изменением типа чата оповещения
// получает пользователь
type SystemBotMessagesMovedNotificationMessage struct {
	Type string `json:"type"`
}

// список сообщений, связанный с карточкой сотрудника
var EmployeeMessageTypeList = EmployeeMessageTypeListStruct{
	EditorAnniversaryMessage:           "editor_employee_anniversary",
	EditorWorksheetRating:              "editor_worksheet_rating",
	SystemBotMessagesMovedNotification: "system_bot_messages_moved_notification",
}

// region функции генерации сообщений

// сообщение-упоминание о времени работы сотрудника в компании
func MakeEmployeeEditorAnniversaryMessage(employeeUserId int64, text string, hiredAt int64) EmployeeEditorAnniversaryMessage {

	return EmployeeEditorAnniversaryMessage{
		Type:           MessageTypeList.Employee.EditorAnniversaryMessage,
		Text:           text,
		HiredAt:        hiredAt,
		EmployeeUserId: employeeUserId,
	}
}

// сообщение-рейтинг рабочих часов за период времени
func MakeEmployeeEditorWorksheetRatingMessage(text string, periodId int, periodFrom int64, periodTo int64, leaderList []EmployeeEditorWorksheetRatingUserItem, drivenList []EmployeeEditorWorksheetRatingUserItem) EmployeeEditorWorksheetRatingMessage {

	return EmployeeEditorWorksheetRatingMessage{
		Type:                   MessageTypeList.Employee.EditorWorksheetRating,
		Text:                   text,
		PeriodId:               periodId,
		PeriodStartDate:        periodFrom,
		PeriodEndDate:          periodTo,
		LeaderUserWorkItemList: leaderList,
		DrivenUserWorkItemList: drivenList,
	}
}

// сообщение о смене типа чата оповещения
func MakeSystemBotMessagesMovedNotificationMessage() SystemBotMessagesMovedNotificationMessage {

	return SystemBotMessagesMovedNotificationMessage{
		Type: MessageTypeList.Employee.SystemBotMessagesMovedNotification,
	}
}

// endregion функции генерации сообщений
