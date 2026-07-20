package proxy

import (
	"fmt"
	"go_api_gateway/internal/apikey"
	"go_api_gateway/internal/grpc"
	"net/http"
	"net/url"
	"strconv"
	"strings"
	"time"

	"google.golang.org/grpc/status"
)

// Middleware тип для middleware с http.Handler
type MiddlewareHandler func(*ProxyRequest) *RequestError

// middlewareSetStartTime определяем время старта выполнения запроса
func (ph *ProxyHandler) middlewareSetStartTime(pr *ProxyRequest) *RequestError {

	pr.startTime = time.Now()
	return nil
}

// middlewareWriteProcessTime записать время обработки запроса
func (ph *ProxyHandler) middlewareSetGatewayProcessDuration(pr *ProxyRequest) *RequestError {

	pr.gatewayProcessDuration = time.Since(pr.startTime).Seconds()
	return nil
}

// middlewareReturnOptionsOk возвращаем Ok на OPTIONS запрос
func (ph *ProxyHandler) middlewareSetCorsHeaders(pr *ProxyRequest) *RequestError {

	// устанавливаем хедеры CORS
	pr.w.Header().Set("Access-Control-Allow-Origin", "*")
	pr.w.Header().Set("Access-Control-Allow-Credentials", "true")
	pr.w.Header().Set("Access-Control-Allow-Methods", "*")
	pr.w.Header().Set("Access-Control-Allow-Headers", "*")
	pr.w.Header().Set("Access-Control-Max-Age", "86400")

	if pr.r.Method != http.MethodOptions {
		return nil
	}

	return &RequestError{
		statusCode: http.StatusOK,
	}
}

// middlewareDefineAuthSource опредлеляем источник авторизационных данных
func (ph *ProxyHandler) middlewareDefineAuthSource(pr *ProxyRequest) *RequestError {

	authSrc := authSrcClient
	authHeader := pr.r.Header.Get("Authorization")
	apiKeyB58, found := strings.CutPrefix(authHeader, "Api-Key ")

	if found {
		authSrc = authSrcGateway
	}

	pr.authSrc = authSrc
	pr.apiKeyB58 = apiKeyB58

	return nil
}

// middlewareAuthorize авторизация по ключу
func (ph *ProxyHandler) middlewareApiKeyAuthorize(pr *ProxyRequest) *RequestError {

	// для клиентов мидлвар не нужен
	if pr.authSrc == authSrcClient {
		return nil
	}

	if pr.apiKeyB58 == "" {
		return &RequestError{http.StatusInternalServerError, "api key not set in header"}
	}

	apiKeyData, err := apikey.Decrypt([]byte(pr.apiKeyB58), ph.secretKey)

	if err != nil {
		return &RequestError{http.StatusUnauthorized, fmt.Sprintf("invalid api key passed %v", err)}
	}

	authResponse, err := ph.authClient.ApikeyAuthenticate(apiKeyData.UserId, apiKeyData.Token)

	if err != nil {
		if e, ok := status.FromError(err); ok {
			switch e.Code() {
			case grpc.ErrTokenExpired, grpc.ErrTokenNotFound:
				return &RequestError{http.StatusUnauthorized, "token not found or expired"}
			default:
				return &RequestError{http.StatusInternalServerError, "unknown error on auth server"}
			}

		} else {
			return &RequestError{http.StatusInternalServerError, "unknown error on auth server"}
		}
	}

	pr.scopeListInt = authResponse.ScopeListInt
	pr.apiKeyData = apiKeyData

	return nil

}

// MiddlewareGenerateJWT Сгенерировать JWT токен
func (ph *ProxyHandler) middlewareGenerateJWT(pr *ProxyRequest) *RequestError {

	// для клиентов мидлвар не нужен
	if pr.authSrc == authSrcClient {
		return nil
	}

	jwtStr, err := ph.jwtManager.Generate(pr.apiKeyData.UserId, pr.scopeListInt)

	if err != nil {
		return &RequestError{http.StatusInternalServerError, "cant generate jwt token"}
	}

	pr.jwtToken = jwtStr

	return nil
}

// MiddlewareCheckRateLimit проверить лимит запросов
func (ph *ProxyHandler) middlewareCheckRateLimit(pr *ProxyRequest) *RequestError {

	// для клиентов мидлвар не нужен
	if pr.authSrc == authSrcClient {
		return nil
	}

	if pr.apiKeyB58 == "" {
		return &RequestError{http.StatusInternalServerError, "api key not found in request"}
	}
	isAllowed := ph.rateLimiter.Allow(pr.apiKeyB58)

	// устанавливаем хедеры
	pr.w.Header().Set(ph.rateLimiter.LimitHeader, strconv.Itoa(ph.rateLimiter.MaxRequests))
	pr.w.Header().Set(ph.rateLimiter.RemainingHeader, strconv.Itoa(ph.rateLimiter.GetRemaining(pr.apiKeyB58)))

	// если превысили rate limit - возвращаем ответ, что гейтвей закрыт для ключа
	if !isAllowed {
		return &RequestError{http.StatusLocked, "reached rate limit"}
	}

	return nil
}

// MiddlewareRouteChanger мидлвар для изменения маршрута
func (ph *ProxyHandler) middlewareRouteChanger(pr *ProxyRequest) *RequestError {

	// изначально думаем, что роут не определен
	subDomain := ""
	urlPath := ""
	targetName := ""
	var targetURL *url.URL

	appDomainIndex := strings.Index(pr.r.Host, ph.appDomain)

	// если нашли поддомен, получаем его
	if appDomainIndex > 0 {

		subDomain = pr.r.Host[:appDomainIndex-1]

		// для домино saas надо отрезать id компании
		subdomainParts := strings.SplitN(subDomain, "-", 2)

		if len(subdomainParts) == 2 {
			subDomain = subdomainParts[1]
		}
	}

	// нас интересуют только api вызовы
	// в гейтвей не должны приходить больше никакие вызовы
	apiPathIndex := strings.Index(pr.r.URL.Path, "api/v")

	if apiPathIndex == -1 {
		return &RequestError{http.StatusUnauthorized, "Passed not api path"}
	}

	// получаем первый подпуть, все остальное в него включается
	var urlPathParts []string
	if apiPathIndex > 0 {
		urlPathParts = strings.SplitN(pr.r.URL.Path[1:apiPathIndex], "/", 2)
	}

	if urlPathParts != nil {
		urlPath = urlPathParts[0]
	}

	// ищем среди таргетов нужный
	for name, target := range ph.targets {

		if target.Subdomain == subDomain && target.UrlPath == urlPath {

			targetURL = target.Entrypoint
			targetName = name
		}
	}

	if targetURL == nil {
		return &RequestError{http.StatusNotFound, "Locprefix not found"}
	}

	pr.targetURL = targetURL
	pr.target = targetName
	return nil
}
