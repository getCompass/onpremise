package structures

// структура для ответа в методе session.getInfo
type SessionInfoStruct struct {
	UserID        int64  `json:"user_id"`
	UaHash        string `json:"ua_hash"`
	NpcType       int32  `json:"npc_type"`
	AvatarFileMap string `json:"avatar_file_map"`
	FullName      string `json:"full_name"`
	Status        int32  `json:"status"`
	Extra         string `json:"extra"`
}

// структура для ответа в методе user.getInfo
type UserInfoStruct struct {
	UserId               int64  `json:"user_id"`
	NpcType              int32  `json:"npc_type"`
	InvitedByPartnerId   int64  `json:"invited_by_partner_id"`
	LastActiveDayStartAt int64  `json:"last_active_day_start_at"`
	InvitedByUserId      int64  `json:"invited_by_user_id"`
	CountryCode          string `json:"country_code"`
	FullName             string `json:"full_name"`
	AvatarFileMap        string `json:"avatar_file_map"`
	CreatedAt            int64  `json:"created_at"`
	UpdatedAt            int64  `json:"updated_at"`
	FullNameUpdatedAt    int64  `json:"full_name_updated_at"`
	Extra                string `json:"extra"`
}

// структура для ответа в методе user.getListInfo
type UserInfoListStruct struct {
	UserList []UserInfoStruct `json:"user_list"`
}
