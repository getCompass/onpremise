package apitoken

import (
	"crypto/rand"
	"encoding/hex"
	"errors"
	"go_auth/internal/database"
	"sync"
	"time"
)

// ApiTokenKey - ключ для кеша
type ApiTokenKey struct {
	UserId   int64  `json:"user_id"`
	ApiToken string `json:"api_token"`
}

// структура токена
type ApiToken struct {
	UserId       int64         `json:"user_id"`
	ApiToken     string        `json:"api_token"`
	CreatedAt    time.Time     `json:"created_at"`
	UpdatedAt    time.Time     `json:"updated_at"`
	ExpiresAt    time.Time     `json:"expires_at"`
	Name         string        `json:"name"`
	ScopeListInt ScopeListInt  `json:"scope_list"`
	Extra        ApiTokenExtra `json:"extra"`
	Exists       bool
	CachedAt     time.Time
}

// ивент для инвалидации токена
type InvalidationEvent struct {
	UserId    int64     `json:"user_id"`
	ApiToken  string    `json:"api_token"`
	Reason    string    `json:"reason"` // "update", "delete", "create"
	CreatedAt time.Time `json:"created_at"`
}

// структура кеша для токенов
type ApiTokenCache struct {
	mu        sync.RWMutex
	apiTokens map[ApiTokenKey]*ApiToken
	ttl       time.Duration
	negTTL    time.Duration
}

// структура менеджера кеша
type ApiTokenCacheManager struct {
	apiTokenCache *ApiTokenCache
	db            *database.Database
	invalidateCh  chan InvalidationEvent
	stopCh        chan struct{}
	wg            sync.WaitGroup
}

// Ошибки
var (
	ErrApiTokenNotFound = errors.New("api token not found")
	ErrApiTokenExpired  = errors.New("api token expired")
	ErrCacheMiss        = errors.New("cache miss")
	ErrCacheExpired     = errors.New("cache expired")
	ErrInvalidKey       = errors.New("invalid key")
	ErrInvalidScopeList = errors.New("invalid scope list")
	ErrDatabase         = errors.New("error with database")
)

// GenerateToken сгенерировать токен
func GenerateToken() (string, error) {

	token := make([]byte, 16)
	_, err := rand.Read(token)

	if err != nil {
		return "", err
	}

	return hex.EncodeToString(token), nil
}
