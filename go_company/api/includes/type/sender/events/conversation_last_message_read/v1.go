package eventConversationLastMessageRead

import "go_company/api/includes/type/structures"

type V1 struct {
	ConversationKey          string                        `json:"conversation_key"`
	MessageKey               string                        `json:"message_key"`
	ConversationMessageIndex int64                         `json:"conversation_message_index"`
	ReadParticipantsCount    int                           `json:"read_participants_count"`
	ReadParticipants         []*structures.ReadParticipant `json:"read_participants"`
}

func MakeV1(conversationKey string, messageKey string, conversationMessageIndex int64, readParticipantsCount int, readParticipants []*structures.ReadParticipant) *V1 {

	return &V1{
		ConversationKey:          conversationKey,
		MessageKey:               messageKey,
		ConversationMessageIndex: conversationMessageIndex,
		ReadParticipantsCount:    readParticipantsCount,
		ReadParticipants:         readParticipants,
	}
}

func (e *V1) GetData() interface{} {
	return e
}

func (e *V1) GetVersion() int {
	return 1
}
