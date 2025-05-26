package dbConversationMessageReadParticipants

// пакет для работы с записью в таблице
type Row struct {
	ConversationMap          string `json:"conversation_map"`
	ConversationMessageIndex int64  `json:"conversation_message_index"`
	UserId                   int64  `json:"user_id"`
	ReadAt                   int64  `json:"read_at"`
	MessageCreatedAt         int64  `json:"message_created_at"`
	CreatedAt                int64  `json:"created_at"`
	UpdatedAt                int64  `json:"updated_at"`
	MessageMap               string `json:"message_map"`
}
