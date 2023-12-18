package reactions

import (
	Isolation "go_company/api/includes/type/isolation"
	reactionStorage "go_company/api/includes/type/reaction_storage"
	"go_company/api/includes/type/structures"
	"google.golang.org/grpc/status"
)

// AddInConversation добавление задачи на реакцию в диалоге
func AddInConversation(isolation *Isolation.Isolation, ConversationMap string, MessageMap string, BlockId int64, ReactionName string, UserId int64, UpdatedAtMs int64, WsUserList interface{}, WsEventData []structures.WsEventVersionItemStruct) error {

	isOverFlow, err := isolation.ReactionStore.AddAddedReactionTask(
		isolation.Context,
		isolation.CompanyConversationConn,
		isolation.CompanyThreadConn,
		ConversationMap,
		reactionStorage.ConversationEntityType,
		MessageMap,
		BlockId,
		ReactionName,
		UserId,
		UpdatedAtMs,
		WsUserList,
		WsEventData,
	)

	if err != nil {
		return status.Error(906, "can't add reaction")
	}
	if isOverFlow {
		return status.Error(907, "overflow reactions")
	}
	return nil
}

// RemoveInConversation добавление задачи на удаление реакции из диалога
func RemoveInConversation(isolation *Isolation.Isolation, ConversationMap string, MessageMap string, BlockId int64, ReactionName string, UserId int64, UpdatedAtMs int64, WsUserList interface{}, WsEventData []structures.WsEventVersionItemStruct) error {

	isolation.ReactionStore.AddRemovedReactionTask(
		ConversationMap,
		reactionStorage.ConversationEntityType,
		MessageMap,
		BlockId,
		ReactionName,
		UserId,
		UpdatedAtMs,
		WsUserList,
		WsEventData,
	)

	return nil
}

// AddInThread добавление задачи на реакцию в треде
func AddInThread(isolation *Isolation.Isolation, ThreadMap string, MessageMap string, BlockId int64, ReactionName string, UserId int64, UpdatedAtMs int64, WsUserList interface{}, WsEventData []structures.WsEventVersionItemStruct) error {

	isOverFlow, err := isolation.ReactionStore.AddAddedReactionTask(
		isolation.Context,
		isolation.CompanyConversationConn,
		isolation.CompanyThreadConn,
		ThreadMap,
		reactionStorage.ThreadEntityType,
		MessageMap,
		BlockId,
		ReactionName,
		UserId,
		UpdatedAtMs,
		WsUserList,
		WsEventData,
	)

	if err != nil {
		return status.Error(906, "can't add reaction")
	}
	if isOverFlow {
		return status.Error(907, "overflow reactions")
	}
	return nil
}

// RemoveInThread добавление задачи на удаление реакции из треда
func RemoveInThread(isolation *Isolation.Isolation, ThreadMap string, MessageMap string, BlockId int64, ReactionName string, UserId int64, UpdatedAtMs int64, WsUserList interface{}, WsEventData []structures.WsEventVersionItemStruct) error {

	isolation.ReactionStore.AddRemovedReactionTask(
		ThreadMap,
		reactionStorage.ThreadEntityType,
		MessageMap,
		BlockId,
		ReactionName,
		UserId,
		UpdatedAtMs,
		WsUserList,
		WsEventData,
	)

	return nil
}
