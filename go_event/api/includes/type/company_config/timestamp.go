package CompanyConfig

// Пакет описывает использование изоляции как изолированной среды компании.
// Когда компания начинает обслуживаться сервисом, она первым делом проходит через этот пакет
//
// ЭТОТ ПАКЕТ НЕ МОЖЕТ ПОДКЛЮЧАТЬ ДРУГИЕ ПАКЕТЫ С ЛОГИКОЙ КОМПАНИИ КАК ЗАВИСИМОСТИ,
// ИНАЧЕ ЭТО ВЫЗЫВАЕТ ПОРОЧНЫЙ КРУГ ЦИКЛИЧЕСКИХ ИМПОРТОВ

import (
	"fmt"
	"go_event/api/conf"
	"io/fs"
	"os"
	"strings"
	"time"
)

type worldConfigItem struct {
	lastUpdatedConfig time.Time
}

var worldConfigModifiedTime time.Time
var worldConfigStore = make(map[string]worldConfigItem)

// проверяем изменился ли timestamp
func checkTimeStamp() bool {

	filename := ".timestamp.json"

	// получаем файл время изменения которого мне нужно
	timestamp, err := os.Stat(fmt.Sprintf("%s/%s", conf.GetConfig().WorldConfigPath, filename))

	if err != nil {
		fmt.Println(err)
		return false
	}

	// получаем время изменения и сверяем
	modifiedTime := timestamp.ModTime()
	if worldConfigModifiedTime.Equal(modifiedTime) {
		return false
	}

	worldConfigModifiedTime = modifiedTime
	return true
}

// проверяем файл с конфигом компании
func checkFileTimeStamp(fileInfo fs.FileInfo) bool {

	if !strings.Contains(fileInfo.Name(), ".json") {
		return false
	}

	// получаем актуальные данные для файла
	fileInfo, err := os.Stat(conf.GetConfig().WorldConfigPath + "/" + fileInfo.Name())
	if err != nil {
		return false
	}

	config, ok := worldConfigStore[fileInfo.Name()]
	if ok && config.lastUpdatedConfig.Equal(fileInfo.ModTime()) {
		return false
	}

	worldConfigStore[fileInfo.Name()] = worldConfigItem{
		lastUpdatedConfig: fileInfo.ModTime(),
	}

	return true
}
