package event

const (
	ConversationTypingEventName = "event.conversation_typing"
)

type conversationTypingV1Struct struct {
	UserID          int64  `json:"user_id"`
	Type            int    `json:"type"`
	ConversationKey string `json:"conversation_key"`
}

// формируем все версии события event.conversation_typing
func MakeConversationTyping(UserID int64, Type int, ConversationKey string) map[int]interface{} {

	output := make(map[int]interface{})
	output[1] = conversationTypingV1Struct{
		UserID:          UserID,
		Type:            Type,
		ConversationKey: ConversationKey,
	}

	return output
}
