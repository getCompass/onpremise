package dbConversationMeta

import "encoding/json"

// пакет для работы с полем дата в таблице
type UsersRow struct {
	MetaId   int64           `json:"meta_id"`
	Year     int             `json:"year"`
	UsersRaw json.RawMessage `json:"users"`
	Users    map[int64]UserStruct
}

type UserStruct struct {
	Version   int   `json:"version"`
	Role      int   `json:"role"`
	CreatedAt int64 `json:"created_at"`
	UpdatedAt int64 `json:"updated_at"`
}
