package huawei

type SendInfo struct {
	TokenPushList []TokenPush `json:"token_push_list,omitempty"`
	Version       int         `json:"version,omitempty"`
}

type TokenPush struct {
	SoundType      string            `json:"sound_type,omitempty"`
	AppName        string            `json:"app_name,omitempty"`
	TokenDeviceMap map[string]string `json:"token_device_map,omitempty"`
}
