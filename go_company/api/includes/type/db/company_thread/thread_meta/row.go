package dbThreadMeta

import "encoding/json"

// пакет для работы с полем дата в таблице
type UsersRow struct {
	MetaId   int64           `json:"meta_id"`
	Year     int             `json:"year"`
	UsersRaw json.RawMessage `json:"users"`
	Users    map[int64]UserStruct
}

type UserStruct struct {
	Version            int   `json:"version"`
	AccessMask         int   `json:"access_mask"`
	CountHiddenMessage int64 `json:"count_hidden_message"`
}
