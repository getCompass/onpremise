package system

import (
	"time"
)

// функция для получения текущих 15-ти минут
func Min15Start() int64 {

	timeObj := time.Now()

	year := timeObj.Year()
	month := timeObj.Month()
	day := timeObj.Day()
	hour := timeObj.Hour()
	minute := timeObj.Minute()
	location := timeObj.Location()

	// округляем минуты до ближайшей 15-ти минутки
	roundedMinute := (minute / 15) * 15
	min15Start := time.Date(year, month, day, hour, roundedMinute, 0, 0, location).Unix()

	return min15Start
}
