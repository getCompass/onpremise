package privatize

/*
 * модель для сокрытия конфиденциальной информации
 */

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"math"
	"reflect"
)

// убираем приватную иформацию из интерфейса
func Do(privatizeInterface interface{}, notPrivateFieldList []string) interface{} {

	return privatize(privatizeInterface, notPrivateFieldList)
}

// убираем приватную иформацию из интерфейса
// @long - switch...case
func privatize(privatizeInterface interface{}, notPrivateFieldList []string) interface{} {

	// определяем тип содержимого
	switch reflect.TypeOf(privatizeInterface) {

	// проходимся по map
	case reflect.TypeOf(map[string]interface{}{}):
		return privatizeMap(privatizeInterface.(map[string]interface{}), notPrivateFieldList)

	// проходимся по slice
	case reflect.TypeOf([]interface{}{}):
		return privatizeSlice(privatizeInterface.([]interface{}))

	// проходимся по string
	case reflect.TypeOf(""):
		return privatizeString(privatizeInterface.(string))

	// если число то просто пропускаем
	case reflect.TypeOf(float64(0)):
		return privatizeNumber()

	// если такого типа нет
	default:
		log.Errorf("Неизвестный тип интерфейса. Type: %v", reflect.TypeOf(privatizeInterface))
	}

	return privatizeInterface
}

// убираем приватную информацию из map
func privatizeMap(oldMap map[string]interface{}, notPrivateFieldList []string) map[string]interface{} {

	// объявляем новый map
	var newMap = make(map[string]interface{})

	// проходимся по каждому полю oldMap
	for key, value := range oldMap {

		// если поле конфиденциально
		if !functions.IsStringInSlice(key, notPrivateFieldList) {

			// убираем приватную инфу
			value = privatize(value, notPrivateFieldList)
		}

		// записываем значение в newMap
		newMap[key] = value
	}

	return newMap
}

// убираем приватную информацию из слайса и пишем его длину
func privatizeSlice(slice []interface{}) []interface{} {

	// получаем длину слайса
	sliceLength := len(slice)

	// если слайс пустой - просто возвращаем его
	if sliceLength == 0 {
		return slice
	}

	return []interface{}{fmt.Sprintf("*** (slice length: %d)", sliceLength)}
}

// убираем приватную информацию из строки и пишем длину
func privatizeString(string string) string {

	// получаем длину строки
	stringLength := len(string)

	// если строка пустая - просто возвращаем ее
	if stringLength == 0 {
		return string
	}

	return fmt.Sprintf("*** (%d)", stringLength)
}

// убираем приватную информацию из числа
func privatizeNumber() string {

	return "***"
}

// маскируем половину строки
// подходит для максировки UUID
func MaskHalfPartOfString(in string) string {

	// если не длинная строка, то маскируем полностью
	l := len(in)
	if l < 2 {
		return "****"
	}

	out := []rune(in)[:int(math.Round(float64(l)/2))]
	out = append(out, []rune("–****")...)

	return string(out)
}
