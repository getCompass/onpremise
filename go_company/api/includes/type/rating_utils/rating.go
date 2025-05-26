package rating_utils

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"time"
)

// инициализируем параметры для запрашиваемого периода
func InitYearParamsList(fromDateAt int, toDateAt int) map[int]map[string]int {

	// получаем год и день с какой даты получаем рейтинг
	timeDateFrom := time.Unix(int64(fromDateAt), 0)
	fromYear := timeDateFrom.Year()
	startDay := functions.GetDaysCountByTimestamp(int64(fromDateAt), fromYear)

	// получаем год и день до какой даты достаем рейтинг
	timeDateTo := time.Unix(int64(toDateAt), 0)
	toYear := timeDateTo.Year()
	endDay := functions.GetDaysCountByTimestamp(int64(toDateAt), toYear)

	// формируем данные для получения рейтинга
	yearListData := map[int]map[string]int{}
	for year := fromYear; year <= toYear; year++ {

		switch year {
		case fromYear:
			yearListData[year] = map[string]int{
				"startDay": startDay,
				"endDay":   endDay,
			}
		case toYear:
			yearListData[year] = map[string]int{
				"startDay": 1,
				"endDay":   endDay,
			}
		default:
			yearListData[year] = map[string]int{
				"startDay": 1,
				"endDay":   366,
			}
		}
	}

	if fromYear != toYear {
		yearListData[fromYear]["endDay"] = 366
	}

	return yearListData
}

// получаем начало дня из года
func GetDayStartByYearAndDay(year int, day int) int64 {

	timeStart := time.Date(year, 1, 0, 10, 0, 0, 0, time.UTC)
	dayStart := timeStart.AddDate(0, 0, day)
	return dayStart.Unix()
}

// функция для получения начала часа для определенного времени
func HourStartByTimeAt(timeAt int64) int64 {

	timeObj := time.Unix(timeAt, 0)

	year := timeObj.Year()
	month := timeObj.Month()
	day := timeObj.Day()
	hour := timeObj.Hour()
	location := timeObj.Location()

	hourStart := time.Date(year, month, day, hour, 0, 0, 0, location).Unix()

	return hourStart
}
