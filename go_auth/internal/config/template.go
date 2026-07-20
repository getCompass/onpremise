package config

import (
	"encoding/json"
	"fmt"
	"os"
)

const apiKeyTemplatesConfigName = "api_key_templates.json"

// структура темплейта
type ApiKeyTemplate struct {
	TemplateId  int64             `json:"template_id"`
	Order       int64             `json:"order"`
	Title       string            `json:"title"`
	UniqName    string            `json:"uniq_name"`
	Description string            `json:"description"`
	ScopeList   map[string]string `json:"scope_list"`
}

// структура конфига темплейтов
type ApiKeyTemplatesConfig struct {
	ApiKeyTemplateList []*ApiKeyTemplate `json:"apikey_template_list"`
}

// LoadApiKeyTemplatesConfig загрузить конфиг темплейтов
func LoadApiKeyTemplatesConfig() (*ApiKeyTemplatesConfig, error) {

	filePath := getFullConfigDir() + apiKeyTemplatesConfigName
	file, err := os.Open(filePath)

	if err != nil {
		return nil, fmt.Errorf("cant load api key templates json from %s. Error: %v", filePath, err)
	}

	defer file.Close()

	apiKeyTemplatesConfig := &ApiKeyTemplatesConfig{}

	decoder := json.NewDecoder(file)
	err = decoder.Decode(&apiKeyTemplatesConfig)

	if err != nil {
		return nil, fmt.Errorf("cant decode api key templates json from %s. Error: %v", filePath, err)
	}

	fmt.Println("Loaded api key templates config")
	return apiKeyTemplatesConfig, nil
}
