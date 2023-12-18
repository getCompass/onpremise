package structGeneral

import (
	dbRatingMemberDayList "go_company/api/includes/type/db/company_data/rating_member_day_list"
)

// структуру описания количества ивентов
type EventCount struct {
	Year  int `json:"year"`
	Day   int `json:"day"`
	Count int `json:"count"`
}

// сткруткура описания рейтинга
type Rating struct {
	Count     int       `json:"count"`
	UpdatedAt int64     `json:"updated_at"`
	TopList   []TopItem `json:"top_list"`
	HasNext   int       `json:"has_next"`
}

// сткрутура описания топа
type TopItem struct {
	UserId     int64 `json:"user_id"`
	Position   int   `json:"position"`
	Count      int   `json:"count"`
	IsDisabled int64 `json:"is_disabled"`
}

// структура описания статистики по пользователю за день
type UserDayStats struct {
	UserId int64                      `json:"user_id"`
	Data   dbRatingMemberDayList.Data `json:"data"`
}
