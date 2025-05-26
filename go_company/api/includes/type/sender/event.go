package sender

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/crypt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/conf"
	isolation "go_company/api/includes/type/isolation"
	"sort"
	"strconv"
)

const WS_CHANNEL = "domino"

type Event struct {
	Event            string                    `json:"event"`
	EventVersionList []*EventVersionItemStruct `json:"event_version_list"`
	UserIdList       []int64                   `json:"user_id_list"`
	PushData         string                    `json:"push_data"`
	UUID             string                    `json:"uuid"`
	CompanyId        int64                     `json:"company_id"`
	Channel          string                    `json:"channel"`
	WsUsers          string                    `json:"ws_users"`
}

type EventVersionItemStruct struct {
	Version int         `json:"version"`
	Data    interface{} `json:"data"`
}

type WsUsers struct {
	UserList  []int64 `json:"user_list"`
	Signature string  `json:"signature,omitempty"`
}

// сформировать событие для отправки в sender
func MakeEvent(isolation *isolation.Isolation, eventName string, eventVersionList []*EventVersionItemStruct, userList []int64, wsUserList []int64) *Event {

	w, _ := json.Marshal(MakeWsUsers(wsUserList))

	return &Event{
		Event:            eventName,
		EventVersionList: eventVersionList,
		CompanyId:        isolation.GetCompanyId(),
		UserIdList:       userList,
		Channel:          WS_CHANNEL,
		WsUsers:          string(w),
		PushData:         "{}",
		UUID:             functions.GenerateUuid(),
	}
}

// сформировать ws_users
func MakeWsUsers(userList []int64) WsUsers {

	if len(userList) == 0 {
		return WsUsers{
			UserList: make([]int64, 0),
		}
	}

	return WsUsers{
		UserList:  userList,
		Signature: getWsUsersSignature(userList, functions.GetCurrentTimeStamp()),
	}
}

// получить подпись ws_users
func getWsUsersSignature(userList []int64, time int64) string {

	// сортируем по возрастанию
	sort.Slice(userList, func(i, j int) bool { return userList[i] < userList[j] })

	// формируем json со списком пользователем
	userListJson, err := json.Marshal(userList)

	if err != nil {
		log.Errorf("Error marshalling user list: %v", err)
		return ""
	}

	// зашифровываем список пользователей
	config, err := conf.GetConfig()

	if err != nil {
		log.Errorf("Error getting config: %v", err)
		return ""
	}

	e, err := crypt.Encrypt(string(userListJson), "aes-256", config.EncryptPassphraseAction, config.EncryptIvAction)

	if err != nil {
		log.Errorf("Ws users encryption error: %v", err)
		return ""
	}

	// формируем подпись и возвращаем её
	return functions.GetMd5SumString(e) + "_" + strconv.FormatInt(time, 10)
}
