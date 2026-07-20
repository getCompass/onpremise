package base64

import (
	"encoding/base64"
	"encoding/json"
)

// base64Encode кодирует данные в URL-safe base64 без padding
func Base64Encode(data []byte) string {
	return base64.RawURLEncoding.EncodeToString(data)
}

// base64Decode декодирует URL-safe base64 строку
func Base64Decode(s string) ([]byte, error) {
	return base64.RawURLEncoding.DecodeString(s)
}

// EncodeData кодирует произвольные данные в base64
func EncodeData(data interface{}) (string, error) {
	var dataBytes []byte
	var err error

	switch v := data.(type) {
	case []byte:
		dataBytes = v
	case string:
		dataBytes = []byte(v)
	default:
		// Для структур сериализуем в JSON
		dataBytes, err = json.Marshal(v)
		if err != nil {
			return "", err
		}
	}

	return Base64Encode(dataBytes), nil
}

// DecodeData декодирует произвольные данные из base64
func DecodeData(data string, v interface{}) error {
	dataBytes, err := Base64Decode(data)
	if err != nil {
		return err
	}

	switch target := v.(type) {
	case *[]byte:
		*target = dataBytes
	case *string:
		*target = string(dataBytes)
	default:
		// Для структур парсим JSON
		return json.Unmarshal(dataBytes, v)
	}

	return nil
}
