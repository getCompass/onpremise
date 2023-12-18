package EventData

/* Пакет для работы с данными события.
   Важно — имя каждого типа должно начинаться с категории, к оторой принаждлежит событие */
/* В этом файле описаны структуры данных событий категории user */

// данные события пользователь зарегистрирован
type UserAuthAuthorizationSuccessEventData struct {
	UserId           int64  `json:"user_id"`
	Device           string `json:"device"`
	Location         string `json:"location"`
	IpAddress        string `json:"ip_address"`
	Company          string `json:"company"`
	FirstTime        bool   `json:"first_time"`
	IsFirstCompany   bool   `json:"is_first_company"`
	IsCompanyCreator bool   `json:"is_creator"`
}

// данные события пользователь зарегистрирован
type UserAuthLogoutSuccessEventData struct {
	Device    string `json:"device"`
	Location  string `json:"location"`
	IpAddress string `json:"ip_address"`
}

// данные события пользователь сменил телефон
type UserSecurityPhoneChangedEventData struct {
}

// данные события пользователь сменил мыло
type UserSecurityEmailChangedEventData struct {
}

// данные события пользователь сменил имя
type UserProfileNameChangedEventData struct {
	PreviousName string `json:"previous_name,omitempty"`
	NewName      string `json:"new_name,omitempty"`
}

// данные события пользователь сменил имя
type UserProfilePhotoChangedEventData struct {
	PreviousAvatarFileMap string `json:"previous_avatar_file_map,omitempty"`
	NewAvatarFileMap      string `json:"new_avatar_file_map,omitempty"`
}
