package proxy

import (
	"go_api_gateway/internal/analytics"
	"net/http"
)

type MonitoringHandler struct {
	prometheusUsername string
	prometheusPassword string
}

// инициализировать хендлер для мониторинга
func InitMonitoringHandler(prometheusUsername string, prometheusPassword string) *MonitoringHandler {

	return &MonitoringHandler{
		prometheusUsername: prometheusUsername,
		prometheusPassword: prometheusPassword,
	}
}

// http хендлер
func (mh *MonitoringHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {

	username, password, ok := r.BasicAuth()

	if !ok || username != mh.prometheusUsername || password != mh.prometheusPassword {

		http.Error(w, "", http.StatusUnauthorized)
		return
	}

	w.Write([]byte(analytics.DefaultMetricRegistry.String()))
}
