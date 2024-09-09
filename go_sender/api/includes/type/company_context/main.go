package companyContext

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/conf"
	GlobalIsolation "go_sender/api/includes/type/global_isolation"
	"os"
	"strings"
)

// UpdateWorldConfig получаем конфигурацию custom
func UpdateWorldConfig(globalIsolation *GlobalIsolation.GlobalIsolation) (map[int64]*conf.CompanyConfigStruct, map[int64]struct{}, error) {

	globalConfig := globalIsolation.GetConfig()

	timestampFilename := ".timestamp.json"

	if !checkFileTimeStamp(globalConfig, timestampFilename) {
		return nil, nil, nil
	}

	files, err := os.ReadDir(globalConfig.WorldConfigPath)
	if err != nil {
		return nil, nil, err
	}

	configList := make(map[int64]*conf.CompanyConfigStruct)
	allCompanyList := make(map[int64]struct{})

	for _, file := range files {

		if file.Name() == ".timestamp.json" {
			continue
		}

		splitString := strings.Split(file.Name(), "_")
		if len(splitString) < 2 {

			log.Errorf("invalid config file %v", file.Name())
			continue
		}

		companyId := functions.StringToInt64(splitString[0])
		if companyId == 0 {

			log.Errorf("invalid config file %v", file.Name())
			continue
		}

		allCompanyList[companyId] = struct{}{}

		isNeedUpdate := checkFileTimeStamp(globalConfig, file.Name())
		if !isNeedUpdate {
			continue
		}

		companyConfig, err := conf.GetWorldConfig(fmt.Sprintf("%s/%s", globalIsolation.GetConfig().WorldConfigPath, file.Name()))
		if err != nil {

			log.Errorf("invalid file %v", err)
			continue
		}

		configList[companyId] = companyConfig
	}

	return configList, allCompanyList, nil
}
