package gatewayGoActivity

import (
	"fmt"
	"go_sender/api/conf"
	"go_sender/api/includes/type/curl"
	"google.golang.org/grpc/status"
)

// методя для отправки запроса
func doSendRequest(request []byte, method string) error {

	// получаем адрес
	config, _ := conf.GetShardingConfig()

	// формируем запрос
	requestMap := map[string]string{
		"method":  method,
		"request": string(request),
	}

	// формируем ссылку
	apiUrl := fmt.Sprintf(config.Go["activity"].Protocol + "://" + config.Go["activity"].Host + ":" + config.Go["activity"].Port)

	// осуществляем запрос
	_, isSuccess := curl.SimplePost(apiUrl, requestMap)
	if !isSuccess {
		return status.Error(400, "post request failed")
	}

	return nil
}
