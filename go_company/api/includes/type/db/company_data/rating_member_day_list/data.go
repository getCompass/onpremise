package dbRatingMemberDayList

// пакет для работы с полем дата в таблице
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

// текущая версия
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

// конвертируем data в map
func ConvertToMap(data Data) map[string]int64 {

	return map[string]int64{
		"version":                    int64(data.Version),
		"general_count":              int64(data.GeneralCount),
		"conversation_message_count": int64(data.ConversationMessageCount),
		"thread_message_count":       int64(data.ThreadMessageCount),
		"file_count":                 int64(data.FileCount),
		"reaction_count":             int64(data.ReactionCount),
		"voice_count":                int64(data.VoiceCount),
		"call_count":                 int64(data.CallCount),
		"respect_count":              int64(data.RespectCount),
		"exactingness_count":         int64(data.ExactingnessCount),
	}
}
