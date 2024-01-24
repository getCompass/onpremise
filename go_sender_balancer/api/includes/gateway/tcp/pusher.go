package tcp

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

// SendPush метод для отправки пуша
func SendPush(userIdList []int64, pushData interface{}) {

	request := struct {
		Method     string      `json:"method"`
		PushData   interface{} `json:"push_data"`
		UserIdList []int64     `json:"user_id_list"`
	}{
		Method:     "pusher.sendPivotPush",
		PushData:   pushData,
		UserIdList: userIdList,
	}

	response := struct {
		Status   string   `json:"status"`
		Response struct{} `json:"response"`
	}{}
	err := doCallPusher(request, &response)
	if err != nil {

		log.Errorf("%v", err)
	}
}
