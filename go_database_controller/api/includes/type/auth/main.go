package auth

import (
	"crypto/aes"
	"crypto/cipher"
	"encoding/base64"
	"fmt"
)

// длина вектора инициализации
const ivLength = 16

// аутентификация клиента
func AuthenticateClient(secretKey string, signature string) error {

	isValid := ValidateSignature(secretKey, signature)

	if !isValid {
		return fmt.Errorf("cant authenticate client")
	}

	return nil
}

// валидируем подпись
func ValidateSignature(secretKey string, signature string) bool {

	_, err := decrypt(signature, secretKey)
	if err != nil {
		return false
	}

	return true
}

// расшифровать строку
func decrypt(encryptedString string, secretKey string) (string, error) {

	// декодируем base64 строку с секретным ключом
	secretKeyByte, err := base64.StdEncoding.DecodeString(secretKey)

	if err != nil {
		return "", err
	}

	// декодируем base64 строку
	encrypted, err := base64.StdEncoding.DecodeString(encryptedString)

	if err != nil {
		return "", err
	}

	block, err := aes.NewCipher(secretKeyByte)
	if err != nil {
		return "", err
	}

	if len(encrypted) < ivLength {
		return "", fmt.Errorf("plain content empty")
	}

	iv := encrypted[:ivLength]
	encrypted = encrypted[ivLength:]

	// расшифровываем контент
	ecb := cipher.NewCBCDecrypter(block, iv)
	decrypted := make([]byte, len(encrypted))

	ecb.CryptBlocks(decrypted, encrypted)

	decrypted, err = pkcs5Trimming(decrypted)

	if err != nil {
		return "", err
	}

	return string(decrypted), nil
}

// конвертим слайс расшифрованных байтов в удобоваримый вид
func pkcs5Trimming(encrypt []byte) ([]byte, error) {

	padding := encrypt[len(encrypt)-1]

	toIndex := len(encrypt) - int(padding)
	if toIndex < 1 {
		return []byte{}, fmt.Errorf("invalud decrypted value")
	}

	return encrypt[:toIndex], nil
}
