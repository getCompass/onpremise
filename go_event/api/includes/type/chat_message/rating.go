package ChatMessage

/** Пакет работы с сообщениями */
/** Вспомогательные функции/типы для отправки сообщений статистики */

// структура-описание названий поддерживаемых сообщений категории статистика
type RatingMessageTypeListStruct = struct {
	RatingCompanyEmployeeMetric string // рейтинга по метрикам карточки за период времени
	RatingCompanyRatingMessage  string // общая активность компании
}

// данные для отправки сообщения с рейтингом
type RatingMessageData struct {
	Type  string `json:"type"`
	Year  int64  `json:"year"`
	Week  int64  `json:"week"`
	Count int64  `json:"count"`
	Name  string `json:"name"`
}

// структура элемента метрика-количество в сообщении
type RatingCompanyMetricCountItemStruct struct {
	MetricType string `json:"metric_type"`
	Count      int    `json:"count"`
}

//  данные для отправки сообщений с метриками карточки карточки
type RatingCompanyEmployeeMetricMessage struct {
	Text                string                               `json:"text"`
	Type                string                               `json:"type"`
	CompanyName         string                               `json:"company_name"`
	PeriodStartDate     int64                                `json:"period_start_date"`
	PeriodEndDate       int64                                `json:"period_end_date"`
	MetricCountItemList []RatingCompanyMetricCountItemStruct `json:"metric_count_item_list"`
}

// названия поддерживаемых сообщений категории статистика
var RatingMessageTypeList = RatingMessageTypeListStruct{
	RatingCompanyEmployeeMetric: "company_employee_metric_statistic",
	RatingCompanyRatingMessage:  "company_rating_message",
}

// сообщение о периодических метриках в компании
func MakeRatingCompanyEmployeeMetric(text string, companyName string, dateFrom int64, dateTo int64, metricCountList []RatingCompanyMetricCountItemStruct) RatingCompanyEmployeeMetricMessage {

	return RatingCompanyEmployeeMetricMessage{
		Type:                MessageTypeList.Rating.RatingCompanyEmployeeMetric,
		Text:                text,
		CompanyName:         companyName,
		PeriodStartDate:     dateFrom,
		PeriodEndDate:       dateTo,
		MetricCountItemList: metricCountList,
	}
}

// формирует сообщение рейтинга в компании
func MakeRatingMessageData(year int64, week int64, count int64, name string) RatingMessageData {

	return RatingMessageData{
		Type:  MessageTypeList.Rating.RatingCompanyRatingMessage,
		Year:  year,
		Week:  week,
		Count: count,
		Name:  name,
	}
}
