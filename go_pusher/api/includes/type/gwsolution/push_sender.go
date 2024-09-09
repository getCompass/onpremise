package gwsolution

import (
	"encoding/json"
	"go_pusher/api/includes/type/push"
)

// SendPush передает пуш-уведомление в сервис рассылки
func SendPush(pushTask push.PushTaskStruct) error {

	method := "push.send"

	// кодируем сообщение
	data, err := json.Marshal(pushTask)
	if err != nil {
		return err
	}

	requestUrl := resolveUrl("go_push_sender")
	if len(requestUrl) == 0 {
		return nil
	}

	// делаем запрос в php модуль
	_, err = call(requestUrl, method, data)
	return err
}
