package voippush

// параметры для пуша, приходящие в запросе
type PushStruct struct {
	CompanyId             int64       `json:"company_id"`
	Call                  interface{} `json:"call"`
	Action                string      `json:"action"`
	IsNeedSendApns        int         `json:"is_need_send_apns"`
	NodeList              interface{} `json:"node_list"`
	TimeToLive            int64       `json:"time_to_live"`
	UserId                int64       `json:"user_id"`
	UserInfo              UserInfo    `json:"user_info"`
	ConferenceData        interface{} `json:"conference_data,omitempty"`
	ConferenceJoiningData interface{} `json:"conference_joining_data,omitempty"`
	ConferenceMemberData  interface{} `json:"conference_member_data,omitempty"`
	ConferenceCreatorData interface{} `json:"conference_creator_data,omitempty"`
}

type UserInfo struct {
	FullName      string `json:"full_name"`
	AvatarFileKey string `json:"avatar_file_key"`
	AvatarColor   string `json:"avatar_color"`
}
