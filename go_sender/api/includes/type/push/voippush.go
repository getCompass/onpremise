package push

// параметры для пуша, приходящие в запросе
type VoipPushStruct struct {
	CompanyId      int64       `json:"company_id,omitempty"`
	Call           interface{} `json:"call,omitempty"`
	Action         string      `json:"action,omitempty"`
	IsNeedSendApns int         `json:"is_need_send_apns,omitempty"`
	NodeList       interface{} `json:"node_list,omitempty"`
	TimeToLive     int64       `json:"time_to_live,omitempty"`
	UserId         int64       `json:"user_id,omitempty"`
}
