package event

const (
	NeedVerifyThreadOpenedEventName = "event.need_verify_thread_opened"
)

type needVerifyThreadOpenedV1Struct struct {
	ThreadKey string `json:"thread_key"`
}

// формируем все версии события event.need_verify_thread_opened
func MakeNeedVerifyThreadOpened(threadKey string) map[int]interface{} {

	output := make(map[int]interface{})
	output[1] = needVerifyThreadOpenedV1Struct{
		ThreadKey: threadKey,
	}

	return output
}
