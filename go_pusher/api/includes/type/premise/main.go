package premise

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pusher/api/conf"
	"go_pusher/api/includes/type/socket"
	"math"
	"sync"
)

// ServerInfo структура данных сервера
type ServerInfo struct {
	Domain    string
	ServerUid string
	SecretKey string
}

var currentServerInfo *ServerInfo = nil
var mx = sync.Mutex{}

// GetCurrent возвращает данные для текущего сервера,
// для работы необходимо наличие premise модуля на сервере
func GetCurrent() (*ServerInfo, error) {

	if currentServerInfo == nil {

		mx.Lock()
		defer mx.Unlock()

		// если это saas, то хардкодим данные, тут наверное как-то
		// по-другому надо сделать, но какой server uid у saas непонятно
		if conf.GetConfig().ServerAccommodation == "saas" {
			currentServerInfo = &ServerInfo{Domain: "getcompass.com", ServerUid: ""}
		}

		// если вдруг много рутин запросили данные,
		// то запрос в модуль premise будет выполнен только
		// один раз, остальные рутины получат уже готовое значение
		if currentServerInfo != nil {
			return currentServerInfo, nil
		}

		fetchedServerInfo, err := socket.GetServerInfo()
		if err != nil {
			return nil, fmt.Errorf("can not fetch server info: %v", err)
		}

		if len(fetchedServerInfo.Response.SecretKey) < 1 {
			return nil, fmt.Errorf("empty server secret_key %v", fetchedServerInfo.Response)
		}

		log.Infof(
			"fetched server info: domain '%s', server_uid '%s' partial_secret_key '%s'",
			fetchedServerInfo.Response.Domain,
			fetchedServerInfo.Response.ServerUid,
			fetchedServerInfo.Response.SecretKey[:int(math.Min(6, float64(len(fetchedServerInfo.Response.SecretKey))))],
		)

		currentServerInfo = &ServerInfo{
			Domain:    fetchedServerInfo.Response.Domain,
			ServerUid: fetchedServerInfo.Response.ServerUid,
			SecretKey: fetchedServerInfo.Response.SecretKey,
		}
	}

	return currentServerInfo, nil
}
