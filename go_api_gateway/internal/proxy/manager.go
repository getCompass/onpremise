package proxy

import (
	"context"
	"crypto/tls"
	"crypto/x509"
	"encoding/json"
	"encoding/pem"
	"errors"
	"fmt"
	"go_api_gateway/internal/config"
	"go_api_gateway/internal/grpc"
	"go_api_gateway/internal/jwt"
	"go_api_gateway/internal/ratelimiter"
	"net"
	"net/http"
	"net/http/httputil"
	"strings"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/base64"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

// хендлер прокси
type ProxyHandler struct {
	secretKey             []byte
	appDomain             string
	jwtManager            *jwt.JWTManager
	rateLimiter           *ratelimiter.RateLimiter
	middlewares           []MiddlewareHandler
	getMiddlewares        []MiddlewareHandler
	targets               map[string]*config.TargetStruct
	clientTransportConfig *http.Transport
	authClient            *grpc.AuthClient
	sideActionManager     *SideActionManager // побочные действия срабатывают после выполнения запроса в отдельной рутине
}

// InitProxyHandler инициализировать хендлер прокси
func InitProxyHandler(caCertPEM []byte, targetConfig *config.TargetConfigStruct, jwtManager *jwt.JWTManager, rateLimiter *ratelimiter.RateLimiter, authClient *grpc.AuthClient, MaxIdleConns int, MaxIdleConnsPerHost int, MaxConnsPerHost int) (*ProxyHandler, error) {

	defer log.Success("Initialized Proxy Manager")

	// получаем secret_key из конфига
	secretKey, err := base64.Base64Decode(targetConfig.SecretKeyB64)

	if err != nil {
		return nil, fmt.Errorf("proxy manager not initialized! Error %v", err)
	}

	// инициализируем пул сертификатов
	certPoolItem := x509.NewCertPool()

	// парсим сертификат
	caCertPEMBlock, _ := pem.Decode(caCertPEM)
	caCertItem, err := x509.ParseCertificate(caCertPEMBlock.Bytes)
	if err != nil {
		return nil, fmt.Errorf("cant decode ca cert. Error: %v", err)
	}

	// добавляем сертификат в пул доверенных
	certPoolItem.AddCert(caCertItem)

	// инициализировать менеджер сайд экшенов
	sm := InitSideActionManager()

	ph := &ProxyHandler{
		secretKey:   secretKey,
		appDomain:   targetConfig.AppDomain,
		jwtManager:  jwtManager,
		rateLimiter: rateLimiter,
		middlewares: []MiddlewareHandler{},
		targets:     map[string]*config.TargetStruct{},
		clientTransportConfig: &http.Transport{
			DialContext: (&net.Dialer{
				Timeout: 100 * time.Millisecond, // таймаут на connect
			}).DialContext,
			MaxIdleConns:        MaxIdleConns,        // Общее число idle соединений в пуле
			MaxIdleConnsPerHost: MaxIdleConnsPerHost, // На один целевой хост
			MaxConnsPerHost:     MaxConnsPerHost,     // Макс активных соединений на хост
			TLSClientConfig: &tls.Config{
				RootCAs: certPoolItem,
			},
		},
		authClient:        authClient,
		sideActionManager: sm,
	}

	ph.targets = targetConfig.Targets

	// добавляем мидлвары
	ph.addMiddleware(ph.middlewareSetCorsHeaders)
	ph.addMiddleware(ph.middlewareSetStartTime)
	ph.addMiddleware(ph.middlewareDefineAuthSource)
	ph.addMiddleware(ph.middlewareCheckRateLimit)
	ph.addMiddleware(ph.middlewareRouteChanger)
	ph.addMiddleware(ph.middlewareApiKeyAuthorize)
	ph.addMiddleware(ph.middlewareGenerateJWT)
	ph.addMiddleware(ph.middlewareSetGatewayProcessDuration)

	// добавляем кастомные мидлвары для get запроса для api токена
	ph.addGetMiddleware(ph.middlewareSetCorsHeaders)
	ph.addGetMiddleware(ph.middlewareSetStartTime)
	ph.addGetMiddleware(ph.middlewareDefineAuthSource)
	ph.addGetMiddleware(ph.middlewareCheckRateLimit)

	// добавляем побочные действия
	sm.addSideAction(ph.sideActionIncHttpRequests)
	sm.addSideAction(ph.sideActionWriteProcessTime)
	sm.addSideAction(ph.sideActionWriteLogs)

	return ph, nil
}

// точка входа при получении всех остальных http запросов
func (ph *ProxyHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {

	pr := &ProxyRequest{
		r: r,
		w: w,
	}

	// отправляем в канал сайд экшенов запрос, чтобы произвести действия, не относящиеся напрямую в выполнению запроса
	defer func() {

		pr.fullRequestDuration = time.Since(pr.startTime).Seconds()
		ph.sideActionManager.sideActionCh <- pr
	}()

	// для get запроса свои мидлвары
	if strings.HasSuffix(r.URL.Path, "/api/v2/auth/apikey/getCurrent") {

		// прогоняем через цепочку middleware
		for _, middleware := range ph.getMiddlewares {

			if requestErr := middleware(pr); requestErr != nil {

				pr.responseCode = requestErr.statusCode
				http.Error(w, requestErr.message, pr.responseCode)
				return
			}
		}

		response, requestError := ph.GetCurrent(pr.r)

		if requestError != nil {
			http.Error(w, requestError.message, requestError.statusCode)
		}

		responseJson, _ := json.Marshal(response)

		w.Header().Set("Content-Type", "application/json")
		w.Write(responseJson)
		return
	}

	// прогоняем через цепочку middleware
	for _, middleware := range ph.middlewares {

		if requestErr := middleware(pr); requestErr != nil {

			pr.responseCode = requestErr.statusCode
			http.Error(w, requestErr.message, pr.responseCode)
			return
		}
	}

	// Создаем реверсивный прокси
	proxy := httputil.ReverseProxy{

		// добавляем для запрос конфиг с корневым сертификатом
		Transport: ph.clientTransportConfig,
		Director: func(req *http.Request) {

			// меняем хост на новый
			req.URL.Scheme = pr.targetURL.Scheme
			req.URL.Host = pr.targetURL.Host

			// добавляем хедеры для авторизации
			if pr.authSrc == authSrcGateway {
				req.Header.Set("Authorization", fmt.Sprintf("Bearer %s", pr.jwtToken))
			}
			req.Header.Add("X-Auth-Src", pr.authSrc)
		},
		ModifyResponse: func(resp *http.Response) error {

			pr.responseCode = resp.StatusCode
			return nil
		},
		ErrorHandler: func(w http.ResponseWriter, r *http.Request, err error) {

			if errors.Is(err, context.DeadlineExceeded) {

				pr.responseCode = http.StatusGatewayTimeout
				pr.requestError = &RequestError{
					statusCode: pr.responseCode,
					message:    fmt.Sprintf("gateway timeout error %v", context.Canceled.Error()),
				}

				http.Error(w, "Gateway timeout", pr.responseCode)
				return
			}

			if errors.Is(err, context.Canceled) {

				pr.responseCode = -1
				pr.requestError = &RequestError{
					statusCode: pr.responseCode,
					message:    fmt.Sprintf("client disconnected error %v", context.Canceled.Error()),
				}

				return
			}

			pr.responseCode = http.StatusBadGateway
			pr.requestError = &RequestError{
				statusCode: http.StatusBadGateway,
				message:    fmt.Sprintf("bad gateway error %v", err),
			}
			http.Error(w, "Proxy error", pr.responseCode)
		},
	}

	// перенаправляем запрос
	proxy.ServeHTTP(w, r)
}

// AddMiddleware добавляет middleware в цепочку
func (ph *ProxyHandler) addMiddleware(mw MiddlewareHandler) {
	ph.middlewares = append(ph.middlewares, mw)
}

// AddGetMiddleware добавляет middleware в цепочку
func (ph *ProxyHandler) addGetMiddleware(mw MiddlewareHandler) {
	ph.getMiddlewares = append(ph.getMiddlewares, mw)
}
