package define

const (
	GeneralCounterId             = 1
	ConversationMessageCounterId = 2
	ThreadMessageCounterId       = 3
	ReactionCounterId            = 4
	FileCounterId                = 5
	CallCounterId                = 6
	VoiceCounterId               = 7
	RespectCounterId             = 8
	ExactingnessCounterId        = 9
)

// константы, заблокирован ли пользователь в рейтинге
const (
	UserRatingNotBlocked = 0
	UserRatingBlocked    = 1
	UserRatingNotExist   = 2
)

var EventCountAliasId = map[string]int64{
	"conversation_message": ConversationMessageCounterId,
	"thread_message":       ThreadMessageCounterId,
	"reaction":             ReactionCounterId,
	"file":                 FileCounterId,
	"call":                 CallCounterId,
	"voice":                VoiceCounterId,
	"respect":              RespectCounterId,
	"exactingness":         ExactingnessCounterId,
	"general":              GeneralCounterId,
}

var EventIdAliasCount = map[int64]string{
	ConversationMessageCounterId: "conversation_message",
	ThreadMessageCounterId:       "thread_message",
	ReactionCounterId:            "reaction",
	FileCounterId:                "file",
	CallCounterId:                "call",
	VoiceCounterId:               "voice",
	RespectCounterId:             "respect",
	ExactingnessCounterId:        "exactingness",
	GeneralCounterId:             "general",
}

var (
	HOUR1 = 60 * 60
	DAY1  = HOUR1 * 24
	DAY7  = DAY1 * 7

	ObserverIntervalMinutes = 5 // время работы интервала observer
	MaxReactionCount        = 20
)

// ивенты для которых не трогаем General
var EventIdListForSkipGeneral = map[int64]bool{
	RespectCounterId:      true,
	ExactingnessCounterId: true,
}
