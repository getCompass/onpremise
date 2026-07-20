package proxy

import (
	"crypto/tls"
	"crypto/x509"
	"encoding/pem"
	"fmt"
	"go_api_gateway/internal/apikey"
	"net/http"
	"net/url"
	"regexp"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

// источник данных для авторизации
const (
	authSrcClient  = "client"
	authSrcGateway = "gateway"
)

// реквест
type ProxyRequest struct {
	authSrc      string
	apiKeyB58    string
	apiKeyData   *apikey.ApiKeyData
	jwtToken     string
	target       string
	targetURL    *url.URL
	scopeListInt map[int64]int64

	r *http.Request
	w http.ResponseWriter

	responseCode int
	requestError *RequestError

	startTime              time.Time
	gatewayProcessDuration float64
	fullRequestDuration    float64
}

type RequestError struct {
	statusCode int
	message    string
}

// InitHttpServer инициализируем объект http сервера
func InitHttpServer(ph *ProxyHandler, mh *MonitoringHandler, caCertPEM []byte, certPEM []byte, keyPEM []byte) (*http.Server, error) {

	port := 443
	defer log.Successf("Initialized HTTP Proxy Server at %d", port)

	// инициализируем пул сертификатов
	certPoolItem := x509.NewCertPool()

	// парсим сертификат

	caCertPEMBlock, _ := pem.Decode(caCertPEM)
	caCertItem, err := x509.ParseCertificate(caCertPEMBlock.Bytes)
	if err != nil {
		return nil, fmt.Errorf("cant decode ca cert. Error: %v", err)
	}

	certPoolItem.AddCert(caCertItem)

	cert, err := tls.X509KeyPair(certPEM, keyPEM)

	if err != nil {
		return nil, fmt.Errorf("cant decode cert with key. Error: %v", err)
	}

	serverTLSConfig := &tls.Config{
		InsecureSkipVerify: false,
		MinVersion:         tls.VersionTLS12,
		GetCertificate: func(hello *tls.ClientHelloInfo) (*tls.Certificate, error) {
			return &cert, nil
		},
		RootCAs: certPoolItem,
	}

	// реализуем хендлер
	mux := http.NewServeMux()
	mux.HandleFunc("/health", healthcheck)
	mux.Handle("/api/monit", mh)
	mux.Handle("/", ph)

	return &http.Server{
		Addr:         fmt.Sprintf(":%d", port),
		ReadTimeout:  10 * time.Second,
		WriteTimeout: 10 * time.Second,
		TLSConfig:    serverTLSConfig,
		Handler:      cleanPath(mux),
	}, nil
}

// cleanPath для удаления двойных слешей
func cleanPath(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {

		// заменяем множественные слеши на одинарные
		r.URL.Path = regexp.MustCompile(`/{2,}`).ReplaceAllString(r.URL.Path, "/")
		next.ServeHTTP(w, r)
	})
}

// ендпоинт для хелсчека
func healthcheck(w http.ResponseWriter, r *http.Request) {

	w.WriteHeader(http.StatusOK)
}
