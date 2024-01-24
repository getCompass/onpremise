package companyContext

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
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
func checkTimeStamp(worldConfigPath string) bool {

	filename := ".timestamp.json"

	// получаем файл время изменения которого мне нужно
	timestamp, err := os.Stat(fmt.Sprintf("%s/%s", worldConfigPath, filename))

	if err != nil {

		log.Errorf("%v", err)
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

// стартуем окружение по env
func checkFileTimeStamp(file fs.FileInfo) bool {

	isJson := strings.Contains(file.Name(), ".json")
	if !isJson {
		return false
	}

	config, ok := worldConfigStore[file.Name()]
	if ok && config.lastUpdatedConfig.Equal(file.ModTime()) {
		return false
	}
	worldConfigStore[file.Name()] = worldConfigItem{
		lastUpdatedConfig: file.ModTime(),
	}
	return true
}
