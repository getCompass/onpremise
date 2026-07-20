package jwt

import (
	"crypto/hmac"
	"crypto/sha256"
	"crypto/sha512"
	"github.com/getCompassUtils/go_base_frame/api/system/base64"
	"hash"
)

// SigningMethod интерфейс для алгоритмов подписи
type SigningMethod interface {
	Verify(signingString, signature string, key []byte) error
	Sign(signingString string, key []byte) (string, error)
	Alg() string
}

// HMACSigningMethod реализация HMAC подписи
type HMACSigningMethod struct {
	Name   string
	Hash   func() hash.Hash
	KeyLen int
}

// Алгоритмы подписи
var (
	SigningMethodHS256 = &HMACSigningMethod{
		Name:   "HS256",
		Hash:   sha256.New,
		KeyLen: 32,
	}
	SigningMethodHS384 = &HMACSigningMethod{
		Name:   "HS384",
		Hash:   sha512.New384,
		KeyLen: 48,
	}
	SigningMethodHS512 = &HMACSigningMethod{
		Name:   "HS512",
		Hash:   sha512.New,
		KeyLen: 64,
	}
)

// Alg возвращает название алгоритма
func (m *HMACSigningMethod) Alg() string {
	return m.Name
}

// Sign подписывает данные с использованием HMAC
func (m *HMACSigningMethod) Sign(signingString string, key []byte) (string, error) {
	if len(key) < m.KeyLen {
		return "", ErrKeyTooShort
	}

	hasher := hmac.New(m.Hash, key)
	hasher.Write([]byte(signingString))
	signature := hasher.Sum(nil)

	return base64.Base64Encode(signature), nil
}

// Verify проверяет подпись
func (m *HMACSigningMethod) Verify(signingString, signature string, key []byte) error {
	// Вычисляем ожидаемую подпись
	expectedSig, err := m.Sign(signingString, key)
	if err != nil {
		return err
	}

	// сравниваем подписи
	if !hmac.Equal([]byte(signature), []byte(expectedSig)) {
		return ErrInvalidSignature
	}

	return nil
}

// getSigningMethod возвращает алгоритм подписи по имени
func getSigningMethod(alg string) (SigningMethod, error) {
	switch alg {
	case "HS256":
		return SigningMethodHS256, nil
	case "HS384":
		return SigningMethodHS384, nil
	case "HS512":
		return SigningMethodHS512, nil
	default:
		return nil, ErrUnsupportedAlgorithm
	}
}
