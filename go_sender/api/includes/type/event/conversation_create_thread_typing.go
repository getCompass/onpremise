package event

const (
	ConversationCreateThreadTypingEventName = "event.conversation_create_thread_typing"
)

type conversationCreateThreadTypingV1Struct struct {
	UserID          int64  `json:"user_id"`
	Type            int    `json:"type"`
	MessageKey      string `json:"message_key"`
	ConversationKey string `json:"conversation_key"`
}

// формируем все версии события event.conversation_create_thread_typing
func MakeConversationCreateThreadTyping(UserID int64, Type int, MessageKey string, ConversationKey string) map[int]interface{} {

	output := make(map[int]interface{})
	output[1] = conversationCreateThreadTypingV1Struct{
		UserID:          UserID,
		Type:            Type,
		MessageKey:      MessageKey,
		ConversationKey: ConversationKey,
	}

	return output
}
