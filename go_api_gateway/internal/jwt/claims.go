package jwt

import (
	"time"
)

const jwtIssuer = "api_gw"

// StandardClaims стандартные claims JWT токена
type StandardClaims struct {
	Issuer    string `json:"iss,omitempty"`
	Subject   string `json:"sub,omitempty"`
	Audience  string `json:"aud,omitempty"`
	ExpiresAt int64  `json:"exp,omitempty"`
	NotBefore int64  `json:"nbf,omitempty"`
	IssuedAt  int64  `json:"iat,omitempty"`
	ID        string `json:"jti,omitempty"`
}

// CustomClaims кастомные claims с пользовательскими данными
type CustomClaims struct {
	StandardClaims
	UserId           int64           `json:"uid"`
	ScopePermissions map[int64]int64 `json:"perms"`
}

// NewClaims создает новые claims с указанным временем жизни
func NewClaims(userId int64, scopePermissions map[int64]int64) *CustomClaims {
	now := time.Now()

	return &CustomClaims{
		StandardClaims: StandardClaims{
			Issuer:   jwtIssuer,
			IssuedAt: now.Unix(),
		},
		UserId:           userId,
		ScopePermissions: scopePermissions,
	}
}

// Valid проверяет валидность claims
func (c *CustomClaims) Valid() error {
	now := time.Now().Unix()

	if c.ExpiresAt > 0 && now > c.ExpiresAt {
		return ErrExpiredToken
	}

	if c.NotBefore > 0 && now < c.NotBefore {
		return ErrTokenNotValidYet
	}

	return nil
}
