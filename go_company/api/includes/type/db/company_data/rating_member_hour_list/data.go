package dbEventHoursList

// -------------------------------------------------------
// пакет для работы с полем дата в таблице event_hour_list
// -------------------------------------------------------

// структура json поля data
type Data struct {
	Version                  int `json:"version"`
	GeneralCount             int `json:"general_count"`
	ConversationMessageCount int `json:"conversation_message_count"`
	ThreadMessageCount       int `json:"thread_message_count"`
	FileCount                int `json:"file_count"`
	ReactionCount            int `json:"reaction_count"`
	VoiceCount               int `json:"voice_count"`
	CallCount                int `json:"call_count"`
	RespectCount             int `json:"respect_count"`
	ExactingnessCount        int `json:"exactingness_count"`
}

// текущая версия data
var currentVersion = 2

// инициализируем дату
func InitData(generalCount int, conversationMessageCount int, threadMessageCount int, fileCount int, reactionCount int, voiceCount int, callCount int, respectCount int, exactingnessCount int) Data {

	return Data{
		Version:                  currentVersion,
		GeneralCount:             generalCount,
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
