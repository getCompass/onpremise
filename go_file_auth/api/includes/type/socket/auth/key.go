package socketauth

import (
	"crypto/md5"
	"encoding/hex"
	"encoding/json"
)

// GetSignature формирует подпись данных запроса
func GetSignature(privateKey string, jsonParams json.RawMessage) string {

	data := []byte(privateKey + string(jsonParams))
	hash := md5.Sum(data) // nosemgrep

	return hex.EncodeToString(hash[:])
}
