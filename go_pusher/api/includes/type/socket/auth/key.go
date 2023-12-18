package socketAuthKey

import (
	"crypto"
	"crypto/rsa"
	"crypto/sha256"
	"crypto/x509"
	"encoding/pem"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_pusher/api/includes/type/socket"
	"sync"
)

var publicKeyStore sync.Map

const validPublicKeyTime = 60 * 60 * 24 * 7

type PublicKeyStruct struct {
	publicKey []byte
	expireAt  int64
}

// сверить подпись для запроса
func VerifyCompanySignature(jsonParams string, signature string, companyId int) error {

	publicKey := PublicKeyStruct{}
	publicKeyInterface, exist := publicKeyStore.Load(companyId)
	if !exist {

		responseSocketKey, err := socket.GetGetCompanySocketKey(companyId)
		if err != nil {
			return err
		}

		publicKey = PublicKeyStruct{
			publicKey: []byte(responseSocketKey.Response.SocketKey),
		}
	} else {
		publicKey = publicKeyInterface.(PublicKeyStruct)
	}
	publicKey.expireAt = functions.GetCurrentTimeStamp() + validPublicKeyTime
	publicKeyStore.Store(companyId, publicKey)

	block, _ := pem.Decode(publicKey.publicKey)
	if block == nil {
		return fmt.Errorf("failed to parse public key")
	}
	parsedPublicKeyKey, err := x509.ParsePKIXPublicKey(block.Bytes)
	if err != nil {
		return fmt.Errorf("failed to parse certificate: " + err.Error())
	}

	h := sha256.New()
	h.Write([]byte(jsonParams))
	digest := h.Sum(nil)

	return rsa.VerifyPKCS1v15(parsedPublicKeyKey.(*rsa.PublicKey), crypto.SHA256, digest, []byte(signature))
}

// удаляем неиспользуемые ключи
func DeleteUnusedKey() {

	publicKeyStore.Range(func(key, value interface{}) bool {

		publicKey := value.(PublicKeyStruct)
		if publicKey.expireAt < functions.GetCurrentTimeStamp() {
			publicKeyStore.Delete(key)
		}
		return true
	})
}
