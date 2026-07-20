package aes

import (
	"crypto/aes"
	"crypto/cipher"
	"fmt"
)

// EncryptAESGCM шифрует данные с использованием AES-GCM
func EncryptAESGCM(plaintext []byte, keyByte []byte, ivByte []byte) ([]byte, error) {

	// создаем AES cipher
	block, err := aes.NewCipher(keyByte)
	if err != nil {
		return nil, fmt.Errorf("failed to create cipher: %w", err)
	}

	// создаем GCM режим
	gcm, err := cipher.NewGCM(block)
	if err != nil {
		return nil, fmt.Errorf("failed to create GCM: %w", err)
	}

	//
	if len(ivByte) != gcm.NonceSize() {
		return nil, fmt.Errorf("failed to use iv: need to be length of %d", gcm.NonceSize())
	}

	// шифруем данные
	ciphertext := gcm.Seal(ivByte, ivByte, plaintext, nil)
	return ciphertext, nil
}

// DecryptAESGCM расшифровывает данные, зашифрованные EncryptAESGCM
func DecryptAESGCM(ciphertext []byte, keyByte []byte) ([]byte, error) {

	// cоздаем AES шифр
	block, err := aes.NewCipher(keyByte)
	if err != nil {
		return nil, fmt.Errorf("failed to create cipher: %w", err)
	}

	// cоздаем GCM режим
	gcm, err := cipher.NewGCM(block)
	if err != nil {
		return nil, fmt.Errorf("failed to create GCM: %w", err)
	}

	// проверяем минимальную длину ciphertext
	ivSize := gcm.NonceSize()
	if len(ciphertext) < ivSize {
		return nil, fmt.Errorf("ciphertext too short")
	}

	// извлекаем nonce и остальную часть ciphertext
	ivByte, encrypted := ciphertext[:ivSize], ciphertext[ivSize:]

	// расшифровываем и проверяем аутентификацию
	plaintext, err := gcm.Open(nil, ivByte, encrypted, nil)
	if err != nil {
		return nil, fmt.Errorf("decryption failed: %w", err)
	}

	return plaintext, nil
}
