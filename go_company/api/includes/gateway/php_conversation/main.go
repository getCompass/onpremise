package gatewayPhpConversation

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/includes/type/socket"
	socketAuthKey "go_company/api/includes/type/socket/auth"
)

type sendReadMessageWsRequest struct {
	ConversationMapList []string `json:"conversation_map_list"`
}

// SendReadMessageWs отправить ws о прочтении последнего сообщения
func SendReadMessageWs(companyId int64, socketKeyMe string, conversationMapList []string) error {

	request := &sendReadMessageWsRequest{
		ConversationMapList: conversationMapList,
	}

	jsonData, err := json.Marshal(request)

	if err != nil {

		log.Errorf("error socket request: %s", err.Error())
		return err
	}

	// получаем подпись модуля
	signature := socketAuthKey.GetCompanySignature(socketKeyMe, []byte(jsonData))

	// делаем запрос в php модуль
	response, err := socket.DoCall("php_conversation", "conversations.sendReadMessageWs", signature, string(jsonData), 0, companyId)
	if err != nil {

		log.Errorf("error socket request: %s", err.Error())
		return err
	}

	if response.Status != "ok" {
		return fmt.Errorf("response status not ok")
	}

	return nil
}
