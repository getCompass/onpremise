package dbRatingDayList

// пакет для работы с полем дата в таблице

type Data struct {
	Version                  int `json:"version"`
	ConversationMessageCount int `json:"conversation_message_count"`
	ThreadMessageCount       int `json:"thread_message_count"`
	FileCount                int `json:"file_count"`
	ReactionCount            int `json:"reaction_count"`
	VoiceCount               int `json:"voice_count"`
	CallCount                int `json:"call_count"`
	RespectCount             int `json:"respect_count"`
	ExactingnessCount        int `json:"exactingness_count"`
}

// последняя версия
var currentVersion = 2

// инициализируем дату
func InitData(conversationMessageCount int, threadMessageCount int, fileCount int, reactionCount int, voiceCount int, callCount int, respectCount int, exactingnessCount int) Data {

	return Data{
		Version:                  currentVersion,
		ConversationMessageCount: conversationMessageCount,
		ThreadMessageCount:       threadMessageCount,
		FileCount:                fileCount,
		ReactionCount:            reactionCount,
		VoiceCount:               voiceCount,
		CallCount:                callCount,
		RespectCount:             respectCount,
		ExactingnessCount:        exactingnessCount,
	}
}
