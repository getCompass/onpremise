package method_config

/**
 * Файл пакета содержит методы для работы непосредственно с самим экземпляром конфига
 */

// структура конфига
type ConfigStruct struct {

	// словарь [event] => поддерживаемая версия, например:
	// action.conversation_message_received => 1
	eventVersionMap map[string]int

	// платформа (больше для отладки)
	platform string

	// версия приложения (больше для отладки)
	appVersion string
}

// создаем конфиг
func MakeConfig(configContent map[string]int, platform string, appVersion string) ConfigStruct {

	return ConfigStruct{
		eventVersionMap: configContent,
		platform:        platform,
		appVersion:      appVersion,
	}
}

// поддерживается ли конфигом версия события
func (c ConfigStruct) isEventVersionSupported(eventName string, version int) (isSupported bool, exist bool) {

	supportedVersion, exist := c.GetEventVersion(eventName)

	// если не нашли
	if !exist {
		return false, false
	}

	// если версии не совпали
	if supportedVersion != version {
		return false, true
	}

	// остался только позитивный кейс
	return true, true
}

// получаем поддерживаемую версию события из конфига
func (c ConfigStruct) GetEventVersion(eventName string) (int, bool) {

	version, exist := c.eventVersionMap[eventName]

	return version, exist
}
