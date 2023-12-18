package companyContext

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/conf"
	GlobalIsolation "go_company_cache/api/includes/type/global_isolation"
	"os"
	"strings"
	"time"
)

// UpdateWorldConfig получаем конфигурацию custom
// @long
func UpdateWorldConfig(globalIsolation *GlobalIsolation.GlobalIsolation) (map[int64]*conf.CompanyConfigStruct, map[int64]struct{}, error) {

	isNeedUpdate := checkTimeStamp(globalIsolation.GetConfig().WorldConfigPath)
	if !isNeedUpdate {
		return nil, nil, nil
	}

	files, err := os.ReadDir(globalIsolation.GetConfig().WorldConfigPath)
	if err != nil {
		return nil, nil, err
	}

	startedAt := time.Now()
	configList := make(map[int64]*conf.CompanyConfigStruct)
	allCompanyList := make(map[int64]struct{})

	for _, file := range files {

		// пропускаем все «скрытые» файлы в директории, такие как:
		// .timestamp.json; .deleted_companies.json, etc ...
		if strings.HasPrefix(file.Name(), ".") {
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

		isNeedUpdate := checkFileTimeStamp(globalIsolation.GetConfig().WorldConfigPath, file)
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

	log.Infof("конфиги компаний были перезагружены за %dms", time.Now().Sub(startedAt).Milliseconds())
	return configList, allCompanyList, nil
}
