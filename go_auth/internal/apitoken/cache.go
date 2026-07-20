package apitoken

import (
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

// инициализировать кеш токенов
func InitCache(ttl, negTTL time.Duration) *ApiTokenCache {

	defer log.Success("api token cache loaded")

	return &ApiTokenCache{
		apiTokens: make(map[ApiTokenKey]*ApiToken),
		ttl:       ttl,
		negTTL:    negTTL,
	}
}

// получить токен из кеша
func (c *ApiTokenCache) Get(key ApiTokenKey) (*ApiToken, error) {

	c.mu.RLock()
	defer c.mu.RUnlock()

	apiToken, found := c.apiTokens[key]

	if !found {
		return nil, ErrCacheMiss
	}

	ttl := c.ttl

	if !apiToken.Exists {
		ttl = c.negTTL
	}

	if time.Since(apiToken.CachedAt) > ttl {
		return nil, ErrCacheExpired
	}

	return apiToken, nil
}

// установить значение токена в кеше
func (c *ApiTokenCache) Set(key ApiTokenKey, apiToken *ApiToken) {

	c.mu.Lock()
	defer c.mu.Unlock()

	apiToken.CachedAt = time.Now()
	c.apiTokens[key] = apiToken
}

// удалить токен из кеша
func (c *ApiTokenCache) Delete(key ApiTokenKey) {

	c.mu.Lock()
	defer c.mu.Unlock()

	delete(c.apiTokens, key)
}

// очистить кеш от неиспользуемых токенов
func (c *ApiTokenCache) Cleanup() {

	c.mu.Lock()
	defer c.mu.Unlock()

	now := time.Now()

	for key, apiToken := range c.apiTokens {

		ttl := c.ttl

		if !apiToken.Exists {
			ttl = c.negTTL
		}

		if now.Sub(apiToken.CachedAt) > ttl {
			delete(c.apiTokens, key)
		}
	}
}

// удалить из кеша просроченные токены
func (c *ApiTokenCache) DeleteExpired() {

	c.mu.Lock()
	defer c.mu.Unlock()

	for key, apiToken := range c.apiTokens {

		if time.Since(apiToken.ExpiresAt) > 0 {
			delete(c.apiTokens, key)
		}
	}

}
