package device

import (
	"context"
	"encoding/json"
	"go_pusher/api/includes/type/analyticspush"
)

const (
	TokenTypeFirebaseLegacy = 1
	TokenTypeApns           = 2
	TokenTypeVoipApns       = 3
	TokenTypeHuawei         = 4
	TokenTypeFirebaseV1     = 5

	// дефолтное приложение, куда отправляются пуши
	DefaultAppName = "comteam"

	SoundType1 = 0
	SoundType2 = 1
	SoundType3 = 2
	SoundType4 = 3
	SoundType5 = 4
	SoundType6 = 5
)

var SoundTypeAliasMap = map[int]string{
	SoundType1: "sound1.wav",
	SoundType2: "sound2.wav",
	SoundType3: "sound3.wav",
	SoundType4: "sound4.wav",
	SoundType5: "sound5.wav",
	SoundType6: "",
}

// разрешенные приложения для отправки пушей
var AllowedAppNameList = []string{"comteam", "compass"}

type DeviceStruct struct {
	UserId     int64      `json:"user_id"`
	DeviceId   string     `json:"device_id"`
	ExtraField extraField `json:"extra"`
}

// список токенов, сгруппированный по token_type, который улетает в очередь на отправку пуша
type PushTokenListGroupedByTokenType struct {
	SoundType string
	AppName   string
	Uuid      string
	TokenList map[string]string
	Version   int

	// мапа, где ключ – токен, а значение – аналитика по отправке пуша на этот токен
	//
	// это необходимо, чтобы при батчинг отправке пуш уведомлений (через firebase legacy, firebase v1, huawei)
	// аналитика по отправке записалась для
	PushAnalytics map[string]analyticspush.PushStruct
}

type TokenItem struct {
	Version           int    `json:"version"`
	Token             string `json:"token"`
	TokenType         int    `json:"token_type"`
	SessionUniq       string `json:"session_uniq"`
	DeviceId          string `json:"device_id"`
	SoundType         int    `json:"sound_type"`
	IsNewFirebasePush int    `json:"is_new_firebase_push"`
	AppName           string `json:"app_name"`
	rawMessage        json.RawMessage
}

// метод инициализирует необходимые подключения и объекты модели
func Init(ctx context.Context) {

	// запускаем в отдельной горутине функцию для актуализации пользовательских токенов
	go doWork(ctx)
}
