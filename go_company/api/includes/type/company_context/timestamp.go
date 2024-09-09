package companyContext

import (
	"github.com/getCompassUtils/go_base_frame/api/system/server"
	"go_company/api/conf"
	"os"
	"strings"
	"time"
)

type worldConfigItem struct {
	configModifiedAt time.Time
	configUpdatedAt  time.Time
}

var worldConfigStore = make(map[string]worldConfigItem)

// проверяем файл с конфигом компании
func checkFileTimeStamp(globalConfig *conf.ConfigStruct, fileName string) bool {

	if !strings.Contains(fileName, ".json") {
		return false
	}

	// получаем актуальные данные для файла
	fileInfo, err := os.Stat(globalConfig.WorldConfigPath + "/" + fileName)
	if err != nil {
		return false
	}

	isNeedUpdate := false
	worldConfig, ok := worldConfigStore[fileInfo.Name()]

	if !ok || !worldConfig.configModifiedAt.Equal(fileInfo.ModTime()) {
		isNeedUpdate = true
	}

	forceUpdateInterval := globalConfig.ForceCompanyConfigUpdateIntervalSec * time.Second

	if ok && server.IsOnPremise() && worldConfig.configUpdatedAt.Before(time.Now().Add(-forceUpdateInterval)) {
		isNeedUpdate = true
	}

	if !isNeedUpdate {
		return false
	}

	worldConfigStore[fileInfo.Name()] = worldConfigItem{
		configModifiedAt: fileInfo.ModTime(),
		configUpdatedAt:  time.Now(),
	}

	return true
}
