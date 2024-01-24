package devicetoken

import "encoding/json"

const (
	TokenTypeFirebase = 1
	TokenTypeApns     = 2
	TokenTypeVoipApns = 3

	soundTypeDefault = 0
	soundType1       = 1
	soundType2       = 2
)

var SoundTypeAliasMap = map[int]string{
	soundTypeDefault: "sound1.wav",
	soundType1:       "sound2.wav",
	soundType2:       "sound3.wav",
}

type RowStruct struct {
	UserId       int64       `json:"user_id"`
	SnoozedUntil int64       `json:"snoozed_until"`
	TokenList    []TokenItem `json:"token_list"`
	ExtraField   extraField  `json:"extra"`
}

type TokenItem struct {
	UserId            int64
	Version           int    `json:"version"`
	Token             string `json:"token"`
	Platform          string `json:"platform,omitempty"`
	TokenType         int    `json:"token_type,omitempty"`
	SessionUniq       string `json:"session_uniq"`
	DeviceId          string `json:"device_id,omitempty"`
	SoundType         int    `json:"sound_type,omitempty"`
	IsNewFirebasePush int    `json:"is_new_firebase_push,omitempty"`
	RawMessage        json.RawMessage
}
