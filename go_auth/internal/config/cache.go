package config

import (
	"encoding/json"
	"fmt"
	"os"
)

// структура конфига кеша
type CacheConfigStruct struct {
	ItemTTLSec         int64 `json:"item_ttl_sec"`
	NegativeItemTTLSec int64 `json:"negative_item_ttl_sec"`
}

const cacheConfigName = "cache.json"

// LoadCacheConfig загрузить конфиг кеша
func LoadCacheConfig() (*CacheConfigStruct, error) {

	filePath := getFullConfigDir() + cacheConfigName
	file, err := os.Open(filePath)

	if err != nil {
		return nil, fmt.Errorf("cant load target json from %s", filePath)
	}

	defer file.Close()

	cacheConfig := &CacheConfigStruct{}
	decoder := json.NewDecoder(file)
	err = decoder.Decode(&cacheConfig)

	if err != nil {
		return nil, fmt.Errorf("cant decode cache json from %s. Error: %v", filePath, err)
	}

	return cacheConfig, nil
}
