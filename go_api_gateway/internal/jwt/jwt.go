package jwt

import (
	"fmt"
	"strings"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/base64"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

// Ошибки
var (
	ErrInvalidToken         = fmt.Errorf("invalid token")
	ErrExpiredToken         = fmt.Errorf("token has expired")
	ErrTokenNotValidYet     = fmt.Errorf("token is not valid yet")
	ErrUnsupportedAlgorithm = fmt.Errorf("unsupported signing algorithm")
	ErrInvalidSignature     = fmt.Errorf("invalid signature")
	ErrKeyTooShort          = fmt.Errorf("key is too short")
	ErrMalformedToken       = fmt.Errorf("malformed token")
)

// Header представляет заголовок JWT
type Header struct {
	Algorithm string `json:"alg"`
	Type      string `json:"typ"`
}

// JWTManager управляет созданием и валидацией JWT токенов
type JWTManager struct {
	secretKey     []byte
	tokenDuration time.Duration
	signingMethod SigningMethod
}

// InitJWTManager создает новый JWTManager
func InitJWTManager(secretKeyB64 string, tokenDuration time.Duration) *JWTManager {

	secretKey, err := base64.Base64Decode(secretKeyB64)

	if err != nil {

		log.Errorf("JWT Manager not initialized! Error %v", err)
		return nil
	}

	defer log.Success("Initialized JWT Manager")

	return &JWTManager{
		secretKey:     secretKey,
		tokenDuration: tokenDuration,
		signingMethod: SigningMethodHS256, // По умолчанию HS256
	}
}

// WithSigningMethod устанавливает алгоритм подписи
func (m *JWTManager) WithSigningMethod(method SigningMethod) *JWTManager {
	m.signingMethod = method
	return m
}

// Generate создает новый JWT токен
func (m *JWTManager) Generate(userId int64, scopePermissions map[int64]int64) (string, error) {

	if scopePermissions == nil {
		scopePermissions = make(map[int64]int64)
	}

	// Создаем claims
	claims := NewClaims(userId, scopePermissions)

	// Создаем заголовок
	header := Header{
		Algorithm: m.signingMethod.Alg(),
		Type:      "JWT",
	}

	// Кодируем заголовок
	headerJSON, err := base64.EncodeData(header)
	if err != nil {
		return "", err
	}

	// Кодируем claims
	claimsJSON, err := base64.EncodeData(claims)
	if err != nil {
		return "", err
	}

	// Собираем signing string
	signingString := headerJSON + "." + claimsJSON

	// Создаем подпись
	signature, err := m.signingMethod.Sign(signingString, m.secretKey)
	if err != nil {
		return "", err
	}

	// Собираем итоговый токен
	return signingString + "." + signature, nil
}

// Verify проверяет JWT токен и возвращает claims
func (m *JWTManager) Verify(tokenString string) (*CustomClaims, error) {
	// Разбираем токен на части
	parts := strings.Split(tokenString, ".")
	if len(parts) != 3 {
		return nil, ErrMalformedToken
	}

	headerSegment, claimsSegment, signatureSegment := parts[0], parts[1], parts[2]

	// Декодируем заголовок
	var header Header
	if err := base64.DecodeData(headerSegment, &header); err != nil {
		return nil, ErrInvalidToken
	}

	// Получаем алгоритм подписи
	signingMethod, err := getSigningMethod(header.Algorithm)
	if err != nil {
		return nil, err
	}

	// Проверяем подпись
	signingString := headerSegment + "." + claimsSegment
	if err := signingMethod.Verify(signingString, signatureSegment, m.secretKey); err != nil {
		return nil, ErrInvalidSignature
	}

	// Декодируем claims
	var claims CustomClaims
	if err := base64.DecodeData(claimsSegment, &claims); err != nil {
		return nil, ErrInvalidToken
	}

	// Проверяем валидность claims
	if err := claims.Valid(); err != nil {
		return nil, err
	}

	return &claims, nil
}

// Parse без проверки подписи (только для отладки)
func (m *JWTManager) Parse(tokenString string) (*CustomClaims, error) {
	parts := strings.Split(tokenString, ".")
	if len(parts) != 3 {
		return nil, ErrMalformedToken
	}

	var claims CustomClaims
	if err := base64.DecodeData(parts[1], &claims); err != nil {
		return nil, ErrInvalidToken
	}

	return &claims, nil
}

// Refresh создает новый токен на основе старого
func (m *JWTManager) Refresh(tokenString string) (string, error) {
	claims, err := m.Verify(tokenString)
	if err != nil {
		return "", err
	}

	// Создаем новый токен с обновленным временем
	return m.Generate(claims.UserId, claims.ScopePermissions)
}

// GetExpiration возвращает время истечения токена
func (m *JWTManager) GetExpiration(tokenString string) (time.Time, error) {
	claims, err := m.Parse(tokenString)
	if err != nil {
		return time.Time{}, err
	}

	return time.Unix(claims.ExpiresAt, 0), nil
}
