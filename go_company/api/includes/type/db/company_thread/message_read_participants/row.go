package dbThreadMessageReadParticipants

// пакет для работы с записью в таблице
type Row struct {
	ThreadMap          string `json:"thread_map"`
	ThreadMessageIndex int64  `json:"thread_message_index"`
	UserId             int64  `json:"user_id"`
	ReadAt             int64  `json:"read_at"`
	MessageCreatedAt   int64  `json:"message_created_at"`
	CreatedAt          int64  `json:"created_at"`
	UpdatedAt          int64  `json:"updated_at"`
	MessageMap         string `json:"message_map"`
}
