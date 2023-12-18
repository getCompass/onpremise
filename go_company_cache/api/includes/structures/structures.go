package structures

import "go_company_cache/api/includes/type/db/company_data"

// SessionInfoStruct структура для ответа в методе session.getIfo
type SessionInfoStruct struct {
	UserID    int64                   `json:"user_id"`
	UserAgent string                  `json:"user_agent"`
	IpAddress string                  `json:"ip_address"`
	Status    int32                   `json:"status"`
	Extra     string                  `json:"extra"`
	Member    *company_data.MemberRow `json:"member"`
}
