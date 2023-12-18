package controller

import (
	"github.com/getCompassUtils/go_base_frame"
	"go_company/api/includes/methods/reactions"
	"go_company/api/includes/type/request"
	"go_company/api/includes/type/structures"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// пакет, реализующий добавление и удаление реакций в диалогах, группах, тредах
// -------------------------------------------------------

type reactionController struct{}

// поддерживаемые методы
var reactionMethods = methodMap{
	"addInConversation":    reactionController{}.AddInConversation,
	"removeInConversation": reactionController{}.RemoveInConversation,
	"addInThread":          reactionController{}.AddInThread,
	"removeInThread":       reactionController{}.RemoveInThread,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// структура запроса для реакции в диалоге
type conversationReactionStruct struct {
	ConversationMap    string                                `json:"conversation_map"`
	MessageMap         string                                `json:"message_map"`
	BlockId            int64                                 `json:"block_id"`
	ReactionName       string                                `json:"reaction_name"`
	ShardId            string                                `json:"shard_id"`
	UserId             int64                                 `json:"user_id"`
	UpdatedAtMs        int64                                 `json:"updated_at_ms"`
	WsUserList         interface{}                           `json:"ws_user_list"`
	WsEventVersionList []structures.WsEventVersionItemStruct `json:"ws_event_version_list"`
	WsUsers            interface{}                           `json:"ws_users,omitempty"`
}

// добавляем реакцию пользователя в диалоге
func (reactionController) AddInConversation(data *request.Data) ResponseStruct {

	// парсим входящий запрос
	conversationReactionRequest := conversationReactionStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &conversationReactionRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = reactions.AddInConversation(
		data.CompanyIsolation,
		conversationReactionRequest.ConversationMap,
		conversationReactionRequest.MessageMap,
		conversationReactionRequest.BlockId,
		conversationReactionRequest.ReactionName,
		conversationReactionRequest.UserId,
		conversationReactionRequest.UpdatedAtMs,
		conversationReactionRequest.WsUserList,
		conversationReactionRequest.WsEventVersionList,
	)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	// отдаем ответ
	return Ok()
}

// снимаем реакцию пользователя в диалоге
func (reactionController) RemoveInConversation(data *request.Data) ResponseStruct {

	// парсим входящий запрос
	removeInConversationRequest := conversationReactionStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &removeInConversationRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = reactions.RemoveInConversation(
		data.CompanyIsolation,
		removeInConversationRequest.ConversationMap,
		removeInConversationRequest.MessageMap,
		removeInConversationRequest.BlockId,
		removeInConversationRequest.ReactionName,
		removeInConversationRequest.UserId,
		removeInConversationRequest.UpdatedAtMs,
		removeInConversationRequest.WsUserList,
		removeInConversationRequest.WsEventVersionList,
	)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	// отдаем ответ
	return Ok()
}

// структура запроса для реакции в треде
type threadReactionStruct struct {
	ThreadMap          string                                `json:"thread_map"`
	MessageMap         string                                `json:"message_map"`
	BlockId            int64                                 `json:"block_id"`
	ReactionName       string                                `json:"reaction_name"`
	ShardId            string                                `json:"shard_id"`
	UserId             int64                                 `json:"user_id"`
	UpdatedAtMs        int64                                 `json:"updated_at_ms"`
	WsUserList         interface{}                           `json:"ws_user_list"`
	WsEventVersionList []structures.WsEventVersionItemStruct `json:"ws_event_version_list"`
	WsUsers            interface{}                           `json:"ws_users,omitempty"`
	IsWsEnabled        int64                                 `json:"is_ws_enabled,omitempty"`
}

// добавляем реакцию пользователя в треде
func (reactionController) AddInThread(data *request.Data) ResponseStruct {

	// парсим входящий запрос
	addInThreadRequest := threadReactionStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &addInThreadRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// добавляем задачу на добавление реакции
	err = reactions.AddInThread(
		data.CompanyIsolation,
		addInThreadRequest.ThreadMap,
		addInThreadRequest.MessageMap,
		addInThreadRequest.BlockId,
		addInThreadRequest.ReactionName,
		addInThreadRequest.UserId,
		addInThreadRequest.UpdatedAtMs,
		addInThreadRequest.WsUserList,
		addInThreadRequest.WsEventVersionList,
	)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// снимаем реакцию пользователя в треде
func (reactionController) RemoveInThread(data *request.Data) ResponseStruct {

	// парсим входящий запрос
	removeInThreadRequest := threadReactionStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &removeInThreadRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// добавляем задачу на снятие реакции
	err = reactions.RemoveInThread(
		data.CompanyIsolation,
		removeInThreadRequest.ThreadMap,
		removeInThreadRequest.MessageMap,
		removeInThreadRequest.BlockId,
		removeInThreadRequest.ReactionName,
		removeInThreadRequest.UserId,
		removeInThreadRequest.UpdatedAtMs,
		removeInThreadRequest.WsUserList,
		removeInThreadRequest.WsEventVersionList,
	)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}
