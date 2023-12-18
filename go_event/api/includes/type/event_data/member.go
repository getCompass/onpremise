package EventData

/* Пакет для работы с данными события.
   Важно — имя каждого типа должно начинаться с категории, к которой принадлежит событие
   В этом файле описаны структуры данных событий категории user_company */

import (
	"encoding/json"
	"errors"
	"github.com/getCompassUtils/go_base_frame"
)

// список событий, чтобы не использовать строки при создании подписок
var UserCompanyEventList = struct {
	AnniversaryReached string
	SystemBotMoved     string
}{
	AnniversaryReached: "member.anniversary_reached", // сотрудник празднует юбилей в компании
	SystemBotMoved:     "member.system_bot_moved",    // сообщение от бота о смене типа чата "оповещения"
}

// событие о смене типа чата
type SystemBotMoved struct {
	UserId int64 `json:"user_id"` // кому переслать
}

// декодит событие о смене типа чата
func (eventData SystemBotMoved) Decode(raw json.RawMessage) (SystemBotMoved, error) {

	// декодим данные события
	err := go_base_frame.Json.Unmarshal(raw, &eventData)

	// проверяем, что это нужное событие
	if err != nil {
		return eventData, errors.New("error occurred during decoding member.single_with_bot_type_changed event data")
	}

	return eventData, nil
}

// данные события — отработано N времени в компании
type MemberAnniversaryReached struct {
	ConversationMap  string  `json:"conversation_map"`    // чат в который шлем сообщение
	EmployeeUserId   int64   `json:"employee_user_id"`    // у какого сотрудника сегодня юбилей
	EditorUserIdList []int64 `json:"editor_user_id_list"` // кому нужно выслать напоминание
	HiredAt          int64   `json:"hired_at"`            // дата приема на работу
}

// декодит данные события — отработано N времени в компании
func (eventData MemberAnniversaryReached) Decode(raw json.RawMessage) (MemberAnniversaryReached, error) {

	// декодим данные события
	err := go_base_frame.Json.Unmarshal(raw, &eventData)

	// проверяем, что это нужное событие
	if err != nil {
		return MemberAnniversaryReached{}, errors.New("error occurred during decoding employee.anniversary_reached event data")
	}

	// проверяем, что пользователь получатель верный
	for _, editorUserId := range eventData.EditorUserIdList {

		if !CheckUserId(editorUserId) {
			return MemberAnniversaryReached{}, errors.New("passed incorrect editor user id for employee.anniversary_reached event")
		}
	}

	return eventData, nil
}

// данные события «запрошена обратная связь от руководителя»
type MemberEditorFeedbackRequested struct {
	FeedbackRequestId int64 `json:"feedback_request_id"` // идентификатор запроса ОС
	EmployeeUserId    int64 `json:"employee_user_id"`    // кто запросил обратную связь
	EditorUserId      int64 `json:"editor_user_id"`      // кому нужно выслать напоминание
	PeriodStartDate   int64 `json:"period_start_date"`   // дата начала периода
	PeriodEndDate     int64 `json:"period_end_date"`     // дата окончания периода
}

// декодит данные события «запрошена обратная связь от руководителя»
func (eventData MemberEditorFeedbackRequested) Decode(raw json.RawMessage) (MemberEditorFeedbackRequested, error) {

	// декодим данные события
	err := go_base_frame.Json.Unmarshal(raw, &eventData)

	// проверяем, что это нужное событие
	if err != nil {
		return eventData, errors.New("error occurred during decoding MemberEditorFeedbackRequested event data")
	}

	// проверяем, что данные пользователей, упомянутых в событии, корректны
	if !CheckUserId(eventData.EditorUserId) {
		return eventData, errors.New("passed incorrect editor user id for employee_editor_feedback_requested event")
	}

	if !CheckUserId(eventData.EmployeeUserId) {
		return eventData, errors.New("passed incorrect employee user id for employee_editor_feedback_requested event")
	}

	if eventData.FeedbackRequestId <= 0 {
		return eventData, errors.New("passed incorrect feedback request id for employee_editor_feedback_requested event")
	}

	return eventData, nil
}

// данные события «автоматическая фиксация рабочего времени»
type MemberWorkTimeAutoLogged struct {
	EmployeeUserId int64 `json:"employee_user_id"` // для кого было списано время
	WorkTime       int64 `json:"work_time"`        // сколько было списано времени
}

// декодит данные события «автоматическая фиксация рабочего времени»
func (eventData MemberWorkTimeAutoLogged) Decode(raw json.RawMessage) (MemberWorkTimeAutoLogged, error) {

	// декодим данные события
	err := go_base_frame.Json.Unmarshal(raw, &eventData)

	// проверяем, что это нужное событие
	if err != nil {
		return eventData, errors.New("error occurred during decoding work_time_auto_logged event data")
	}

	// проверяем, что данные пользователей, упомянутых в событии, корректны
	if !CheckUserId(eventData.EmployeeUserId) {
		return eventData, errors.New("passed incorrect employee user id for work_time_auto_logged event")
	}

	return eventData, nil
}

// данные события «пользователь вступил в компанию»
type UserJoinedCompany struct {
	UserId               int64  `json:"user_id"`                 // кто вступил в компанию
	JoinedAt             int    `json:"joined_at"`               // дата вступления
	CompanyInviterUserId int64  `json:"company_inviter_user_id"` // кто пригласил в компанию
	EntryType            int    `json:"entry_type"`              // как пользователь попал в компанию (создал/инвайт)
	CompanyName          string `json:"company_name"`            // название компании
	IsFirstJoinedCompany bool   `json:"is_first_joined_company"` // является ли эта компаний первой, к которой присоединился пользователь
}

// декодит данные события «пользователь вступил в компанию»
func (eventData UserJoinedCompany) Decode(raw json.RawMessage) (UserJoinedCompany, error) {

	// декодим данные события
	err := go_base_frame.Json.Unmarshal(raw, &eventData)

	// проверяем, что это нужное событие
	if err != nil {
		return eventData, errors.New("error occurred during decoding user_joined_company event data")
	}

	// проверяем, что данные пользователей, упомянутых в событии, корректны
	if !CheckUserId(eventData.UserId) {
		return eventData, errors.New("error occurred during decoding user_joined_company event data")
	}

	// проверяем, что данные пользователей, упомянутых в событии, корректны
	if !CheckUserId(eventData.CompanyInviterUserId) {
		return eventData, errors.New("error occurred during decoding user_joined_company event data")
	}

	return eventData, nil
}

/** region protected **/

// проверяет, что указанный ид пользователя корректен
func CheckUserId(userId int64) bool {

	return userId > 0
}

/** endregion protected **/
