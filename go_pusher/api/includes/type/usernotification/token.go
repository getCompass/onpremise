package usernotification

const (
	soundTypeDefault = 0
	soundType1       = 1
	soundType2       = 2
	soundType3       = 3
)

var SoundTypeAliasMap = map[int]string{
	soundTypeDefault: "sound.aiff",
	soundType1:       "sound1.wav",
	soundType2:       "sound2.wav",
	soundType3:       "sound3.wav",
}

type UserNotificationStruct struct {
	UserId        int64      `json:"user_id"`
	SnoozedUntil  int64      `json:"snoozed_until"`
	DeviceList    []string   `json:"device_list"`
	Token         string     `json:"token"`
	ExtraField    extraField `json:"extra"`
	NeedForcePush int        `json:"need_force_push"`
}
