package conf

import (
	"encoding/json"
	"fmt"
	"os"
)

type DbConfig struct {
	Host string
	Port int
	User string
	Pass string
}

type CompanyConfigStruct struct {
	Mysql  DbConfig `json:"mysql"`
	Status int      `json:"status"`
}

// ResolveConfigByCompanyId получает имя файла конфига для компании
func ResolveConfigByCompanyId(companyId int64) string {

	return fmt.Sprintf("%s/%d_company.json", GetConfig().WorldConfigPath, companyId)
}

// GetWorldConfig проверяем изменился ли timestamp
func GetWorldConfig(filePath string) (*CompanyConfigStruct, error) {

	file, err := os.Open(filePath)
	if err != nil {
		return nil, err
	}

	// считываем информацию из файла в переменную
	decoder := json.NewDecoder(file)
	var companyInfo CompanyConfigStruct
	err = decoder.Decode(&companyInfo)
	if err != nil {
		return nil, err
	}

	// закрываем файл
	_ = file.Close()
	return &companyInfo, nil
}

// GetCompanyConfig пытается получить готовый конфиг для компании из файла
func GetCompanyConfig(companyId int64) (*CompanyConfigStruct, error) {

	return GetWorldConfig(ResolveConfigByCompanyId(companyId))
}
