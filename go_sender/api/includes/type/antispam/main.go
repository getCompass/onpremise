package antispam

/**
 * основной файл пакета, содержит core структуры/методы, а также все лимиты
 */

// стуктура, которая описывает каждый существующий лимит
type limitStruct struct {
	maxCount int64 // максимальное значение лимита
	expire   int64 // промежуток времени
}

/**
 * ниже перечислены все существующие ключи для лимитов:
 */

// отправка конфига с версиями поддерживаемых ws-методов
const LimitSaveWsMethodConfig = "save_ws_method_config"

// переменная описывает каждый лимит
var limitStructMapping = map[string]limitStruct{
	LimitSaveWsMethodConfig: {
		maxCount: 30,
		expire:   60,
	},
}
