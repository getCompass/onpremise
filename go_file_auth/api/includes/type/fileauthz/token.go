package fileauthz

import (
	"encoding/json"
	"fmt"
	"go_file_auth/api/includes/type/crypt"
)

// Пакет проверки авторизации доступа к файлам.
// Файл содержит основную логику работы с токеном загрузки.

// токен загрузки файла
type downloadToken struct {
	CompanyId  int64  `json:"c,omitempty"`
	Entrypoint string `json:"e,omitempty"`
}

// декодер токенов скачивания
type downloadTokenLoader struct {
	decrypter *comcrypt.AES256CBCDecrypter
}

// Load загружает токен из строки
func (dtl *downloadTokenLoader) Load(encryptedToken string) (*downloadToken, error) {

	decryptedToken, err := dtl.decrypter.Decrypt(encryptedToken)
	if err != nil {
		return nil, fmt.Errorf("download token decryption error: %s", err.Error())
	}

	token := &downloadToken{}
	if err = json.Unmarshal(decryptedToken, &token); err != nil {
		return nil, fmt.Errorf("can not restore token from decrypted value")
	}

	return token, nil
}

// создает экземпляр загрузчика токенов
func makeDownloadTokenLoader(decryptKey []byte) *downloadTokenLoader {

	tokenDecrypter, err := comcrypt.MakeAES256CBCDecrypter(decryptKey)
	if err != nil {
		panic(fmt.Sprintf("can not initialize download token decryptor: %s", err.Error()))
	}

	return &downloadTokenLoader{decrypter: tokenDecrypter}
}
