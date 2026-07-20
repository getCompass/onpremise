package ratelimiter

import (
	"sync"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

type RateLimiter struct {
	mu              sync.RWMutex
	limits          map[string]*apiLimit
	MaxRequests     int
	windowSize      time.Duration
	cleanupInterval time.Duration
	LimitHeader     string
	RemainingHeader string
}

type apiLimit struct {
	mu          sync.Mutex
	count       int
	windowStart time.Time
}

// InitRateLimiter создает новый rate limiter
func InitRateLimiter(maxRequests int, windowSize, cleanupInterval time.Duration, limitHeader string, remainingHeader string) *RateLimiter {

	defer log.Success("Initialized Rate Limiter")

	rl := &RateLimiter{
		limits:          make(map[string]*apiLimit),
		MaxRequests:     maxRequests,
		windowSize:      windowSize,
		cleanupInterval: cleanupInterval,
		LimitHeader:     limitHeader,
		RemainingHeader: remainingHeader,
	}

	// Запускаем горутину для периодической очистки
	go rl.cleanupExpired()

	return rl
}

// Allow проверяет, разрешен ли запрос для данного API ключа
func (rl *RateLimiter) Allow(apiKey string) bool {

	rl.mu.RLock()
	limit, exists := rl.limits[apiKey]
	rl.mu.RUnlock()

	if !exists {

		rl.mu.Lock()

		// двойная проверка для избежания гонки
		limit, exists = rl.limits[apiKey]
		if !exists {
			limit = &apiLimit{
				windowStart: time.Now(),
				count:       0,
			}
			rl.limits[apiKey] = limit
		}
		rl.mu.Unlock()
	}

	limit.mu.Lock()
	defer limit.mu.Unlock()

	// проверяем, не истекло ли текущее окно
	if time.Since(limit.windowStart) > rl.windowSize {

		// сбрасываем счетчик для нового окна
		limit.count = 0
		limit.windowStart = time.Now()
	}

	// проверяем, не превышен ли лимит
	if limit.count >= rl.MaxRequests {
		return false
	}

	limit.count++
	return true
}

// GetRemaining возвращает оставшееся количество запросов для API ключа
func (rl *RateLimiter) GetRemaining(apiKey string) int {

	rl.mu.RLock()
	limit, exists := rl.limits[apiKey]
	rl.mu.RUnlock()

	if !exists {
		return rl.MaxRequests
	}

	limit.mu.Lock()
	defer limit.mu.Unlock()

	// Если окно истекло, возвращаем полный лимит
	if time.Since(limit.windowStart) > rl.windowSize {
		return rl.MaxRequests
	}

	return rl.MaxRequests - limit.count
}

// cleanupExpired периодически очищает старые записи
func (rl *RateLimiter) cleanupExpired() {

	ticker := time.NewTicker(rl.cleanupInterval)
	defer ticker.Stop()

	for range ticker.C {
		rl.mu.Lock()
		for key, limit := range rl.limits {
			limit.mu.Lock()
			if time.Since(limit.windowStart) > rl.windowSize*2 {
				delete(rl.limits, key)
			}
			limit.mu.Unlock()
		}
		rl.mu.Unlock()
	}
}
