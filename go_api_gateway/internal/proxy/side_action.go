package proxy

import (
	"go_api_gateway/internal/analytics"
	"net/http"
	"strconv"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

// FinalHandler выполняем побочные действия после выполнения каждого запроса
type SideActionHandler func(*ProxyRequest)

type SideActionManager struct {
	sideActions  []SideActionHandler
	sideActionCh chan *ProxyRequest
}

// sideActionIncHttpRequests записать аналитику по http запросам
func (ph *ProxyHandler) sideActionIncHttpRequests(pr *ProxyRequest) {

	analytics.HttpRequestsTotal.Inc(pr.authSrc, pr.target, strconv.Itoa(pr.responseCode))
}

// sideActionWriteProcessTime записать время работы гейтвея в аналитику
func (ph *ProxyHandler) sideActionWriteProcessTime(pr *ProxyRequest) {

	// пишем полное время обработки запроса в приложении
	analytics.FullRequestTime.Observe(
		pr.fullRequestDuration,
		pr.authSrc,
		pr.target,
		strconv.Itoa(pr.responseCode),
	)

	// пишем время обработки запроса в гейтвее перед отдачей в конечный модуль
	analytics.GatewayProcessTime.Observe(
		pr.gatewayProcessDuration,
		pr.authSrc,
		pr.target,
		strconv.Itoa(pr.responseCode),
	)
}

// sideActionWriteLogs записать логи запроса
func (ph *ProxyHandler) sideActionWriteLogs(pr *ProxyRequest) {

	// если ошибки нет, то логировать нечего
	if pr.requestError == nil {
		return
	}

	// для ошибок дисконнекта клиента обрабатываем как предупреждение
	if pr.requestError.statusCode == -1 {

		log.Warningf("request error! Method: %s, URL: %s, Target: %s, RemoteAddr: %s, Error: %v",
			pr.r.Method,
			pr.r.URL.String(),
			pr.targetURL.String(),
			pr.r.RemoteAddr,
			pr.requestError)
	}

	// ошибки с HTTP 50x - фатальные
	if pr.requestError.statusCode >= http.StatusInternalServerError {

		log.Errorf("FATAL request error! Method: %s, URL: %s, Target: %s, RemoteAddr: %s, Error: %v",
			pr.r.Method,
			pr.r.URL.String(),
			pr.targetURL.String(),
			pr.r.RemoteAddr,
			pr.requestError)
	} else {
		log.Infof("request error! Method: %s, URL: %s, Target: %s, RemoteAddr: %s, Error: %v",
			pr.r.Method,
			pr.r.URL.String(),
			pr.targetURL.String(),
			pr.r.RemoteAddr,
			pr.requestError)
	}

}

// InitSideActionManager инициализируем менеджер для сайд экшенов
func InitSideActionManager() *SideActionManager {

	sm := &SideActionManager{
		sideActions:  make([]SideActionHandler, 0),
		sideActionCh: make(chan *ProxyRequest, 1000),
	}

	// запускаем обработку сайд экшенов
	go sm.processSideActions()

	return sm
}

// AddSideAction добавить сайд экшен
// мьютекса нет, так как предполагается, что работа будет вестись в одной рутине
func (sm *SideActionManager) addSideAction(sideAction SideActionHandler) {

	sm.sideActions = append(sm.sideActions, sideAction)
}

// выполнять сайд экшены для запросов
func (sm *SideActionManager) processSideActions() {

	for request := range sm.sideActionCh {
		sm.handleSideActions(request)
	}
}

// обрабатываем side экшены для реквеста
func (sm *SideActionManager) handleSideActions(pr *ProxyRequest) {

	for _, sideAction := range sm.sideActions {
		sideAction(pr)
	}
}
