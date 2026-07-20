package apikey

// стуктура apikey
// дублируется в go_api_gateway

import (
	"bytes"
	"crypto/sha1"
	"encoding/json"
	"fmt"

	"github.com/getCompassUtils/go_base_frame/api/system/aes"
	"github.com/getCompassUtils/go_base_frame/api/system/base58"
)

// данные API ключа для мапы
type ApiKeyData struct {
	Version int    `json:"_,omitempty"`
	UserId  int64  `json:"a,omitempty"`
	Token   string `json:"b,omitempty"`
}

// текущая версия мапы
const currentMapVersion = 1

// запаковать данные ключа
func Pack(userId int64, token string) *ApiKeyData {

	return &ApiKeyData{
		Version: currentMapVersion,
		UserId:  userId,
		Token:   token,
	}
}

// получить мапу API ключа
func (p *ApiKeyData) ToMap() ([]byte, error) {

	return json.Marshal(p)
}

// зашифровать мапу для отправки клиенту
func (p *ApiKeyData) Encrypt(keyByte []byte) ([]byte, error) {

	cMap, err := p.ToMap()

	if err != nil {
		return nil, err
	}

	// вытаскиваем из токена 12 байт
	tokenByte := []byte(p.Token)

	if len(tokenByte) < 12 {
		return nil, fmt.Errorf("too short token for iv")
	}

	ivByte := tokenByte[:12]

	encryptedBytes, err := aes.EncryptAESGCM(cMap, keyByte, ivByte)

	if err != nil {
		return nil, err
	}

	hash := sha1.Sum(encryptedBytes)
	shortHash := hash[:4]

	encryptedBytes = append(shortHash, encryptedBytes...)

	return base58.Base58Encode(encryptedBytes), nil
}

// расшифровать ключ в структуру
func Decrypt(apiKeyByte []byte, keyByte []byte) (*ApiKeyData, error) {

	k, err := base58.Base58Decode(apiKeyByte)

	if err != nil {
		return nil, err
	}

	// учитываем, что iv - 12 байт, и 4 байта - контрольная сумма
	if len(k) < 16 {
		return nil, fmt.Errorf("invalid apikey size")
	}

	hash := k[:4]
	payload := k[4:]

	h := sha1.Sum(payload)

	verify := h[:len(hash)]

	if !bytes.Equal(hash, verify) {
		return nil, fmt.Errorf("key checksum invalid")
	}

	decryptedByte, err := aes.DecryptAESGCM(payload, keyByte)

	if err != nil {
		return nil, err
	}

	apikeyData := &ApiKeyData{}

	err = json.Unmarshal(decryptedByte, apikeyData)

	if err != nil {
		return nil, err
	}
	return apikeyData, nil
}
