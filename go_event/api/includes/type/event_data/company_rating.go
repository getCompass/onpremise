package EventData

/* Пакет для работы с данными события.
   Важно — имя каждого типа должно начинаться с категории, которой принадлежит событие */
/* В этом файле описаны структуры данных событий категории company rating */

import (
	"encoding/json"
	"errors"
	"github.com/getCompassUtils/go_base_frame"
)

// список событий, чтобы не использовать строки при создании подписок
var CompanyRatingEventList = struct {
	ActionTotalFixed         string
	EmployeeMetricTotalFixed string
	WorksheetRatingFixed     string
}{
	WorksheetRatingFixed:     "company_rating.worksheet_rating_fixed",      // рейтинг рабочих часов за период времени
	ActionTotalFixed:         "company_rating.action_total_fixed",          // всего действий за период времени
	EmployeeMetricTotalFixed: "company_rating.employee_metric_total_fixed", // всего действий с карточкой сотрудника за период времени
}

// структура для события отправки рейтинга
type CompanyRatingActionTotalFixed struct {
	ConversationMap string `json:"conversation_map"`
	Year            int64  `json:"year"`
	Week            int64  `json:"week"`
	Count           int64  `json:"count"`
	Name            string `json:"name"`
}

// структура элемента метрика-количество в событии
type CompanyRatingEmployeeMetricTotalItem struct {
	MetricType string `json:"metric_type"`
	Count      int    `json:"count"`
}

// структура, описывающая данные события «зафиксирована статистика по метрикам сотрудника за период»
type CompanyRatingEmployeeMetricTotalFixed struct {
	ConversationMap     string                                 `json:"conversation_map"`       // кому нужно отправить данные
	CompanyName         string                                 `json:"company_name"`           // название компании
	PeriodStartDate     int64                                  `json:"period_start_date"`      // дата начала периода
	PeriodEndDate       int64                                  `json:"period_end_date"`        // дата окончания периода
	MetricCountItemList []CompanyRatingEmployeeMetricTotalItem `json:"metric_count_item_list"` // список метрик и их значения
}

// структура пользователь-отработанное время
type CompanyRatingWorksheetRatingItem struct {
	UserId   int64 `json:"user_id"`
	WorkTime int64 `json:"work_time"`
}

// данные события «сработал триггер для отправки информации по отработанным часам за период времени»
type CompanyRatingWorksheetRatingFixed struct {
	ConversationMap        string                             `json:"conversation_map"`           // кому нужно выслать напоминание
	PeriodId               int                                `json:"period_id"`                  // за какой период
	PeriodStartDate        int64                              `json:"period_start_date"`          // дата начала периода
	PeriodEndDate          int64                              `json:"period_end_date"`            // дата окончания периода
	LeaderUserWorkItemList []CompanyRatingWorksheetRatingItem `json:"leader_user_work_item_list"` // список пользователей, для которых нужно проставить метрики
	DrivenUserWorkItemList []CompanyRatingWorksheetRatingItem `json:"driven_user_work_item_list"` // список пользователей, для которых нужно проставить метрики
}

// декодит данные события «сработал триггер для отправки информации по отработанным часам за период времени»
func (eventData CompanyRatingWorksheetRatingFixed) Decode(raw json.RawMessage) (CompanyRatingWorksheetRatingFixed, error) {

	// декодим данные события
	err := go_base_frame.Json.Unmarshal(raw, &eventData)

	// проверяем, что это нужное событие
	if err != nil {
		return CompanyRatingWorksheetRatingFixed{}, errors.New("error occurred during decoding employee_editor_worksheet_fixed event data")
	}

	for _, employeeWorkItem := range eventData.LeaderUserWorkItemList {

		if !CheckUserId(employeeWorkItem.UserId) {
			return CompanyRatingWorksheetRatingFixed{}, errors.New("passed incorrect employee user id for employee_editor_worksheet_fixed event")
		}
	}

	for _, employeeWorkItem := range eventData.DrivenUserWorkItemList {

		if !CheckUserId(employeeWorkItem.UserId) {
			return CompanyRatingWorksheetRatingFixed{}, errors.New("passed incorrect employee user id for employee_editor_worksheet_fixed event")
		}
	}

	return eventData, nil
}
