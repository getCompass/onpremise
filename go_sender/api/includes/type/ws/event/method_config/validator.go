package method_config

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

/**
 * Файл пакета для валидации присылаемого содержимого конфига
 */

// максимальный размер содержимого конфига в символах
const maxConfigContentLength = 10000

// валидируем содержимое конфига
func ValidateContent(hash string, configContent string) error {

	// если не совпала присланная hash-сумма с полученной
	if hash != functions.GetSha1String(configContent) {

		log.Errorf("получен hash: %s    действительный hash: %s", hash, functions.GetSha1String(configContent))
		return fmt.Errorf("hash sum is not equal")
	}

	// если длина конфига превысила максимальный размер
	if len(configContent) > maxConfigContentLength {

		log.Errorf("полученный конфиг превзошел лимит по размеру")
		return fmt.Errorf("config content length max limit exceeded")
	}

	return nil
}

// парсим содержимое конфига
func ParseContent(configContent string) (map[string]int, error) {

	var methodConfig map[string]int
	err := json.Unmarshal([]byte(configContent), &methodConfig)
	if err != nil {
		return nil, err
	}

	return methodConfig, nil
}
