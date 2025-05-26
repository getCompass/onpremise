package comcrypt

import (
	"crypto/cipher"
	"encoding/base64"
	"fmt"
	"github.com/service/go_base_frame/api/system/log"
)

// Пакет шифрования Compass. Назначение — расшифровывать шифртексты, зашифрованные другими частями приложения.
// Файл содержит логику шифрования AES256CBC — аналог BaseFrame\Crypt\Crypter\OpenSSL.

import (
	"crypto/aes"
)

// AES256CBCDecrypter расшифровщик AES256CBC
type AES256CBCDecrypter struct {
	key   []byte
	block cipher.Block
}

// MakeAES256CBCDecrypter создает экземпляр MakeAES256CBCDecrypter
func MakeAES256CBCDecrypter(key []byte) (*AES256CBCDecrypter, error) {

	block, err := aes.NewCipher(key)
	if err != nil {
		return nil, err
	}

	return &AES256CBCDecrypter{key: key, block: block}, nil
}

// Decrypt выполняет расшифровывание указанной строки
func (d *AES256CBCDecrypter) Decrypt(encoded string) (result []byte, err error) {

	defer func() {

		// объявляем восстановление из паники, потому что иногда отстреливает
		// нужно разобраться во всех причинах и после убрать этот блок
		if r := recover(); r != nil {

			log.Errorf("error occurred during decryption %v", r)
			result, err = nil, fmt.Errorf("error occurred during decryption")
		}
	}()

	// из-за recover дальше все выглядит кривовато, но так надо
	var encodedBytes []byte

	// первым делом расшифровываем base64
	if encodedBytes, err = base64.StdEncoding.DecodeString(encoded); err != nil {

		result, err = nil, fmt.Errorf("can not decode value from base64: %s", err.Error())
		return
	}

	// минимальная длина шифра будет 32 байта (т.е. просто вектор)
	if len(encodedBytes) < 32 {

		result, err = nil, fmt.Errorf("incorrect encrypted value is too short: %d", len(encodedBytes))
		return
	}

	iv := encodedBytes[0:d.block.BlockSize()]
	data := encodedBytes[d.block.BlockSize():]

	// go запаникует, если шифртекст будет иметь некорректную длину
	if len(data)%aes.BlockSize != 0 {

		result, err = nil, fmt.Errorf("incorrect encrypted cyphertext length: %d", len(data))
		return
	}

	decoder := cipher.NewCBCDecrypter(d.block, iv)
	decoder.CryptBlocks(data, data)

	// чистим отступы, это будет результат расшифровывания
	result, err = d.cutPadding(data), nil
	return
}

// удаляет отступы из расшифрованного шифртекста
func (d *AES256CBCDecrypter) cutPadding(src []byte) []byte {

	length := len(src)
	cutLength := int(src[length-1])

	return src[:(length - cutLength)]
}
