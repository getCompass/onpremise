package companyContext

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
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
func checkTimeStamp(worldConfigPath string) bool {

	filename := ".timestamp.json"

	// получаем файл время изменения которого мне нужно
	timestamp, err := os.Stat(fmt.Sprintf("%s/%s", worldConfigPath, filename))

	if err != nil {

		log.Errorf(err.Error())
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
func checkFileTimeStamp(filePath string, file os.DirEntry) bool {

	if !strings.Contains(file.Name(), ".json") {
		return false
	}

	// получаем актуальные данные для файла
	fileInfo, err := os.Stat(filePath + "/" + file.Name())
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
