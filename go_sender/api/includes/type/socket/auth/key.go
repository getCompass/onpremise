package socketAuthKey

import (
	"crypto"
	"crypto/md5"
	"crypto/rand"
	"crypto/rsa"
	"crypto/sha256"
	"crypto/x509"
	"encoding/hex"
	"encoding/pem"
	"fmt"
)

// GetCompanySignature получаем подпись в компании
func GetCompanySignature(privateKey string, jsonParams []byte) string {

	data := []byte(privateKey + string(jsonParams))
	hash := md5.Sum(data)

	return hex.EncodeToString(hash[:])
}

// GetPivotSignature получаем подпись на пивоте
func GetPivotSignature(privateKey string, jsonParams []byte) (signature string, error error) {

	defer func() {

		if err := recover(); err != nil {
			error = fmt.Errorf("panic: %v", err)
		}
	}()

	block, _ := pem.Decode([]byte(privateKey))
	if block == nil {
		panic("failed to parse root certificate PEM")
	}
	parsedPrivateKey, err := x509.ParsePKCS8PrivateKey(block.Bytes)
	if err != nil {
		panic("failed to parse certificate: " + err.Error())
	}

	h := sha256.New()
	h.Write(jsonParams)
	digest := h.Sum(nil)

	s, err := rsa.SignPKCS1v15(rand.Reader, parsedPrivateKey.(*rsa.PrivateKey), crypto.SHA256, digest)
	if err != nil {
		panic("failed to sign:" + err.Error())
	}

	signature = string(s)

	return signature, error
}
