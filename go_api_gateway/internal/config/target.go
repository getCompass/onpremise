package config

import (
	"encoding/base64"
	"encoding/json"
	"fmt"
	"net/url"
	"os"
	"strings"
)

// структура конфига таргета
type TargetStruct struct {
	Entrypoint    *url.URL
	RawEntrypoint string `json:"entrypoint"`
	Subdomain     string `json:"subdomain"`
	UrlPath       string `json:"url_path"`
}

// структура конфига для таргетов
type TargetConfigStruct struct {
	AppDomain    string                   `json:"app_domain"`
	SecretKeyB64 string                   `json:"secret_key_b64"`
	Targets      map[string]*TargetStruct `json:"targets"`
}

const targetConfigName = "target.json"

// LoadTargetConfig создать конфига для таргетов
func LoadTargetConfig() (*TargetConfigStruct, error) {

	filePath := getFullConfigDir() + targetConfigName
	file, err := os.Open(filePath)

	if err != nil {
		return nil, fmt.Errorf("cant load target json from %s", filePath)
	}

	defer file.Close()

	targetConfig := &TargetConfigStruct{}
	decoder := json.NewDecoder(file)
	err = decoder.Decode(&targetConfig)

	if err != nil {
		return nil, fmt.Errorf("cant decode target json from %s. Error: %v", filePath, err)
	}

	for _, target := range targetConfig.Targets {
		entrypoint, err := url.Parse(target.RawEntrypoint)

		if err != nil {
			return nil, fmt.Errorf("entrypoint is not valid: %s. Error: %v", target.RawEntrypoint, err)
		}

		target.Entrypoint = entrypoint
		target.UrlPath, _ = strings.CutPrefix(target.UrlPath, "/")
	}

	// переводим b64 ключ в URL кодировку
	tempKey, err := base64.StdEncoding.DecodeString(targetConfig.SecretKeyB64)

	if err != nil {
		return nil, fmt.Errorf("cant decode b64 key. Error: %v", err)
	}

	targetConfig.SecretKeyB64 = base64.RawURLEncoding.EncodeToString(tempKey)

	fmt.Println("Loaded target config")
	return targetConfig, nil
}
