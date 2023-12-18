package localizer

/* Пакет, содержащий в себе текстовый контент сообщений и функции их формирования
   которые будут отправлены от лица ботов */
/* В этом файле объявлены функции для работы со строками */

import (
	"errors"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_event/api/conf"
	"regexp"
	"strings"
)

// тип — callback правила замены в строке
type stringRuleCallback = func([]string) string

// тип — хранилище локализованных строк
type localizedStringStore = map[string]map[string]string

// дефолтная локаль
var defaultLocale = "ru"

// дефолтное сообщение об ошибке
var errorString = "<<undefined>>"

// метки правил
var ruleSet = map[string]stringRuleCallback{
	"plural::ru::numeric": func(data []string) string {

		if len(data) < 2 {
			return ""
		}

		// получаем счетчик
		count := functions.StringToInt(data[0])

		// вариации склонения
		variety := strings.Split(data[1], "//")

		// если вариаций недостаточно, то возвращаем как есть
		if len(variety) < 3 {
			return variety[0]
		}

		// выбираем нужный вариант слова
		remainder := count % 100

		if remainder >= 5 && remainder <= 20 {
			return variety[2]
		}

		remainder = remainder % 10

		if remainder == 1 {
			return variety[0]
		}

		if remainder >= 2 && remainder <= 4 {
			return variety[1]
		}

		return variety[2]
	},
}

// выполнить подготовку сообщения с дефолтной локалью
func GetString(header string, locale string) (string, error) {

	// выполняем замену с пустым списком подстановок
	return GetStringWithSubstitutions(header, locale, map[string]string{})
}

// выполнить подготовку сообщения с заданной локалью и подстановками
func GetStringWithSubstitutions(header string, locale string, substitutionList map[string]string) (string, error) {

	// получаем строку с нужной локалью
	localizedString, err := GetLocalizedString(header, locale, conf.GetLocaleConf().StringStore)
	if err != nil {
		return errorString, err
	}

	return ProcessRules(ProcessSubstitutions(localizedString, substitutionList), ruleSet), nil
}

// возвращает строковое значение сообщения на требуемом языке
// если на требуемом языке сообщение не объявлено, возвращает на дефолтном
func GetLocalizedString(header string, locale string, store localizedStringStore) (string, error) {

	chunkList := strings.Split(locale, "-")
	locale = chunkList[0]

	// проверяем наличие такого заголовка в списке
	if messageList, isMessageListExists := store[header]; isMessageListExists {

		// проверяем наличие требуемой локали
		if messageString, isMessageStringExists := messageList[locale]; isMessageStringExists {

			// сообщение с требуемой локалью
			return messageString, nil
		} else if messageString, isMessageStringExists = messageList[defaultLocale]; isMessageStringExists {

			// сообщение с дефолтной локалью
			return messageString, nil
		}
	}

	// сообщение с таком заголовком не найдено или для него не нашлось требуемой и дефолтной локали
	return errorString, errors.New("string not found")
}

// выполняет замену алиасов в строке
func ProcessSubstitutions(str string, substitutionList map[string]string) string {

	for k, v := range substitutionList {

		template := strings.Join([]string{"%", k, "%"}, "")
		str = strings.ReplaceAll(str, template, v)
	}

	return str
}

// выполняет обработку правил в строке
func ProcessRules(str string, ruleList map[string]stringRuleCallback) string {

	// регулярка для обработки правил
	ruleRegexp := regexp.MustCompile(`<<.*>>`)

	return string(ruleRegexp.ReplaceAllFunc([]byte(str), func(bytes []byte) []byte {

		str := string(bytes)

		// правило
		rule := strings.Split(strings.Trim(str, "<>"), "||")
		ruleName := rule[0]

		ruleType, exists := ruleList[ruleName]
		if !exists {
			return []byte{}
		}

		// выкидываем имя правила и передаем в обработчик
		return []byte(ruleType(rule[1:]))
	}))
}
