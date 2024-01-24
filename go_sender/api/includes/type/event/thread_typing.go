package event

const (
	ThreadTypingEventName = "event.thread_typing"
)

type ThreadTypingV1Struct struct {
	UserID    int64  `json:"user_id"`
	Type      int    `json:"type"`
	ThreadKey string `json:"thread_key"`
}

// формируем все версии события event.thread_typing
func MakeThreadTyping(UserID int64, Type int, ThreadKey string) map[int]interface{} {

	output := make(map[int]interface{})
	output[1] = ThreadTypingV1Struct{
		UserID:    UserID,
		Type:      Type,
		ThreadKey: ThreadKey,
	}

	return output
}
