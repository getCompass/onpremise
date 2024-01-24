package conf

import (
	"encoding/json"
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

// проверяем изменился ли timestamp
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
