package badge

// параметры для пуша, приходящие в запросе
type PushStruct struct {
	ConversationKeyList []string `json:"conversation_key_list"`
	ThreadKeyList       []string `json:"thread_key_list"`
	BadgeCount          int64    `json:"badge_count"`
}
