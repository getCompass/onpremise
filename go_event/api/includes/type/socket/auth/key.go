package socketAuthKey

import (
	"crypto/md5"
	"encoding/hex"
	"encoding/json"
)

func GetSignature(privateKey string, jsonParams json.RawMessage) string {

	data := []byte(privateKey + string(jsonParams))
	hash := md5.Sum(data)

	return hex.EncodeToString(hash[:])
}
