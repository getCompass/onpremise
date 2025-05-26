package eventThreadLastMessageRead

import "go_company/api/includes/type/structures"

type V1 struct {
	ThreadKey             string                        `json:"thread_key"`
	MessageKey            string                        `json:"message_key"`
	ThreadMessageIndex    int64                         `json:"thread_message_index"`
	ReadParticipantsCount int                           `json:"read_participants_count"`
	ReadParticipants      []*structures.ReadParticipant `json:"read_participants"`
}

func MakeV1(threadKey string, messageKey string, threadMessageIndex int64, readParticipantsCount int, readParticipants []*structures.ReadParticipant) *V1 {

	return &V1{
		ThreadKey:             threadKey,
		MessageKey:            messageKey,
		ThreadMessageIndex:    threadMessageIndex,
		ReadParticipantsCount: readParticipantsCount,
		ReadParticipants:      readParticipants,
	}
}

func (e *V1) GetData() interface{} {
	return e
}

func (e *V1) GetVersion() int {
	return 1
}
