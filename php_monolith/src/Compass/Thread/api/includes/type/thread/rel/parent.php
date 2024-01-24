<?php

namespace Compass\Thread;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с родительской сущностью треда
 * описание и указатель на родтиельскую сущность треда хранится в поле parent_rel
 * в таблице meta
 */
class Type_Thread_Rel_Parent {

	// получаем объект родительской сущности треда
	public static function getEntityData(array $thread_parent_rel, int $user_id):array {

		// получаем тип и идентификатор родительской сущности треда
		$parent_type = Type_Thread_ParentRel::getType($thread_parent_rel);
		$parent_map  = Type_Thread_ParentRel::getMap($thread_parent_rel);

		// в зависимости от типа родительской сущности треда
		return match ($parent_type) {

			PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE => self::_getConversationEntityData($parent_map, $user_id),

			PARENT_ENTITY_TYPE_HIRING_REQUEST, PARENT_ENTITY_TYPE_DISMISSAL_REQUEST => self::_getHiringRequestEntityData($parent_type, $parent_map, $user_id),

			default => throw new ParseFatalException("unhandled thread parent type in method " . __METHOD__),
		};
	}

	/**
	 * Получаем объект родительской сущности треда из диалога
	 *
	 * @param string $parent_map
	 * @param int    $user_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_ParentEntityNotFound
	 */
	protected static function _getConversationEntityData(string $parent_map, int $user_id):array {

		try {
			$response = Gateway_Socket_Conversation::getMessage($user_id, $parent_map);
		} catch (Gateway_Socket_Exception_ParentEntityNotFound) {
			throw new cs_Thread_ParentEntityNotFound(__METHOD__ . " thread message not found");
		} catch (Gateway_Socket_Exception_Conversation_MessageHaveNotAccess) {
			throw new cs_Message_HaveNotAccess(__METHOD__ . " user cant get this message");
		}

		return self::_makeConversationMessageDataOutput($response, PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE);
	}

	// создаем массив ответа
	protected static function _makeConversationMessageDataOutput(array $parent_entity_data, int $parent_type):array {

		$output = [
			"users"         => $parent_entity_data["users"],
			"parent_entity" => $parent_entity_data["message"],
			"parent_type"   => $parent_type,
		];

		if (isset($parent_entity_data["reaction_user_list"])) {
			$output["reaction_user_list"] = $parent_entity_data["reaction_user_list"];
		}
		if (isset($parent_entity_data["hidden_by_users_id"])) {
			$output["hidden_by_users_id"] = $parent_entity_data["hidden_by_users_id"];
		}

		return $output;
	}

	// получаем объект родительской сущности треда из заявки
	protected static function _getHiringRequestEntityData(int $parent_type, string $parent_map, int $user_id):array {

		$parent_name_type = Apiv1_Format::parentType($parent_type);

		$ar_post = [
			"request_type" => (string) $parent_name_type,
			"request_id"   => (int) $parent_map,
		];
		[$status, $response] = Gateway_Socket_Company::doCall("hiring.hiringrequest.getRequestData", $ar_post, $user_id);

		// если статус ответа не равен "ok"
		self::_throwIfSocketGetHiringRequestEntityDataFail($status, $response);

		return self::_makeHiringRequestDataOutput($response, $parent_type);
	}

	// выбрасываем исключения, если сокет не вернул ok
	protected static function _throwIfSocketGetHiringRequestEntityDataFail(string $status, array $response):void {

		if ($status == "ok") {
			return;
		}

		if (!isset($response["error_code"])) {
			throw new ReturnFatalException(__METHOD__ . ": failure socket request");
		}

		throw match ($response["error_code"]) {

			1010 => new cs_Thread_ParentEntityNotFound(__METHOD__ . " hiring request not found"),
			1011 => new cs_Message_HaveNotAccess(__METHOD__ . " user cant get this request"),
			default => new ReturnFatalException(__METHOD__ . ": hiringrequest.getRequestData call return not 'ok'"),
		};
	}

	// создаем массив ответа
	protected static function _makeHiringRequestDataOutput(array $parent_entity_data, int $parent_type):array {

		return [
			"users"         => $parent_entity_data["users"],
			"parent_entity" => $parent_entity_data["request"],
			"parent_type"   => $parent_type,
		];
	}

	// получаем список объектов родительской сущности тредов
	public static function getEntityDataList(array $thread_parent_rel_list, int $user_id):array {

		$from_conversation_parent_map_list = [];
		$request_id_list_by_type           = [];
		$parent_by_type_and_thread_map     = [];
		foreach ($thread_parent_rel_list as $thread_map => $parent_rel) {

			// получаем тип и идентификатор родительской сущности треда
			$parent_type = Type_Thread_ParentRel::getType($parent_rel);
			$parent_map  = Type_Thread_ParentRel::getMap($parent_rel);

			// в зависимости от типа родительской сущности треда
			switch ($parent_type) {

				case PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE:
					$from_conversation_parent_map_list[$thread_map] = $parent_map;
					break;

				case PARENT_ENTITY_TYPE_HIRING_REQUEST:
				case PARENT_ENTITY_TYPE_DISMISSAL_REQUEST:

					$parent_name_type                             = Apiv1_Format::parentType($parent_type);
					$request_id_list_by_type[$parent_name_type][] = $parent_map;

					$parent_by_type_and_thread_map[$parent_name_type][$parent_map] = $thread_map;
					break;

				default:
					throw new ParseFatalException("unhandled thread parent type in method " . __METHOD__);
			}
		}

		// получаем сущности диалогов
		[$conversation_list, $not_allowed_thread_map_list] = self::_getConversationEntityDataList($from_conversation_parent_map_list, $user_id);

		// получаем сущности заявок найма/увольнения
		[$hire_request_list, $temp_not_allowed_thread_map_list] = self::_getHireRequestEntityDataList($request_id_list_by_type, $parent_by_type_and_thread_map, $user_id);

		// мерджим списки недоступных тредов
		$not_allowed_thread_map_list = array_unique(array_merge($not_allowed_thread_map_list, $temp_not_allowed_thread_map_list));

		return [$conversation_list, $hire_request_list, $not_allowed_thread_map_list];
	}

	// получаем список объектов родительской сущности тредов из диалога
	protected static function _getConversationEntityDataList(array $parent_map_list, int $user_id):array {

		if (count($parent_map_list) < 1) {
			return [[], []];
		}

		// выполняем сокет-запрос для получения родительских сущностей тредов
		[$status, $response] = Gateway_Socket_Conversation::getConversationMessageList($parent_map_list, $user_id);
		self::_throwIfSocketForConversationEntityDataFail($status);

		$parent_message_list = $response["message_list"];

		// получение списка map тредов, родители которых недоступны для пользователя
		$not_allowed_thread_map_list = self::_getNotAllowedThreadMapList($parent_map_list, $response["not_access_message_map_list"]);

		$parent_list = self::_getPrepareParentDataList($parent_message_list, PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE);

		return [$parent_list, $not_allowed_thread_map_list];
	}

	// выдаем exception если сокет не вернул ok
	protected static function _throwIfSocketForConversationEntityDataFail(string $status):void {

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": conversations.getMessageList call return not 'ok'");
		}
	}

	// получаем список map тех тредов, которые недоступны для юзера
	protected static function _getNotAllowedThreadMapList(array $parent_map_list, array $not_access_parent_map_list):array {

		// если нет тех родителей-сообщений, к которым юзер не смог получить доступ
		if (count($not_access_parent_map_list) < 1) {
			return [];
		}

		// получаем список map тредов, которые недоступны для пользователя
		$not_access_thread_map_list = [];
		foreach ($parent_map_list as $thread_map => $v) {

			if (in_array($v, $not_access_parent_map_list)) {
				$not_access_thread_map_list[] = $thread_map;
			}
		}

		return $not_access_thread_map_list;
	}

	// получить подготовленный список с информацией о родительских сущностях тредов
	protected static function _getPrepareParentDataList(array $parent_entity_data_list, int $parent_type):array {

		$parent_list = [];
		foreach ($parent_entity_data_list as $v) {

			$parent_message = $v["message"];

			$parent_data = [
				"users"         => $v["users"],
				"parent_entity" => $parent_message,
				"parent_type"   => $parent_type,
			];

			if (isset($v["reaction_user_list"])) {
				$parent_data["reaction_user_list"] = $v["reaction_user_list"];
			}

			if (isset($v["remind"])) {
				$parent_data["remind"] = $v["remind"];
			}

			$parent_list[$parent_message["message_map"]] = $parent_data;
		}

		return $parent_list;
	}

	// получаем список объектов родительской сущности тредов заявки найма/увольнения
	protected static function _getHireRequestEntityDataList(array $request_id_list_by_type, array $parent_by_type_and_thread_map, int $user_id):array {

		if (count($request_id_list_by_type) < 1) {
			return [[], []];
		}

		// вызываем сокет-запрос для получения данных о заявках
		[$request_list_by_type, $not_allowed_id_list_by_type, $users_by_type] = Gateway_Socket_Company::getRequestDataBatching($request_id_list_by_type, $user_id);

		// получение списка map тредов, родители которых недоступны для пользователя
		$not_allowed_thread_map_list = self::_getNotAllowedThreadMapListForRequestEntity($parent_by_type_and_thread_map, $not_allowed_id_list_by_type);

		$parent_list = self::_getPrepareParentDataListForRequestEntity($request_list_by_type, $users_by_type);

		return [$parent_list, $not_allowed_thread_map_list];
	}

	// получение списка map тредов, родители которых недоступны для пользователя
	protected static function _getNotAllowedThreadMapListForRequestEntity(array $parent_by_type_and_thread_map, array $not_allowed_id_list_by_type):array {

		// если нет тех родителей-сообщений, к которым юзер не смог получить доступ
		if (count($not_allowed_id_list_by_type) < 1) {
			return [];
		}

		// получаем список map тредов, которые недоступны для пользователя
		$not_access_thread_map_list = [];
		foreach ($parent_by_type_and_thread_map as $parent_name_type => $thread_map_by_parent_id) {

			foreach ($thread_map_by_parent_id as $parent_id => $thread_map) {

				if (in_array($parent_id, $not_allowed_id_list_by_type[$parent_name_type])) {
					$not_access_thread_map_list[] = $thread_map;
				}
			}
		}

		return $not_access_thread_map_list;
	}

	// получить подготовленный список с информацией о родительских сущностях тредов для заявок
	protected static function _getPrepareParentDataListForRequestEntity(array $request_list_by_type, array $users_by_type):array {

		$parent_list = [];
		foreach ($request_list_by_type as $parent_type => $request_list) {

			foreach ($request_list as $request) {

				$parent_data = [
					"users"         => $users_by_type[$parent_type],
					"parent_entity" => $request,
					"parent_type"   => $parent_type,
				];

				$parent_id                             = $request["hiring_request_id"] ?? $request["dismissal_request_id"];
				$parent_list[$parent_type][$parent_id] = $parent_data;
			}
		}

		return $parent_list;
	}

	/**
	 * Получаем родительское сообщения треда
	 *
	 * @param int   $user_id
	 * @param array $meta_row
	 * @param bool  $is_attach_parent
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws cs_ParentMessage_IsDeleted
	 * @throws cs_ParentMessage_IsRespect
	 */
	public static function getParentMessageIfNeed(int $user_id, array $meta_row, bool $is_attach_parent):array {

		if (!$is_attach_parent) {
			return [];
		}

		$parent_type = Type_Thread_ParentRel::getType($meta_row["parent_rel"]);
		$parent_map  = Type_Thread_ParentRel::getMap($meta_row["parent_rel"]);
		return match ($parent_type) {

			PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE => self::_getMessageFromConversation($user_id, $parent_map),

			default => throw new ParseFatalException("Unknown parent type"),
		};
	}

	/**
	 * Получаем сообщение из диалога
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_ParentMessage_IsDeleted
	 * @throws cs_ParentMessage_IsRespect
	 */
	protected static function _getMessageFromConversation(int $user_id, string $message_map):array {

		// делаем сокет запрос и получаем сообщение
		try {
			$response = Gateway_Socket_Conversation::getMessageData($user_id, $message_map);
		} catch (Gateway_Socket_Exception_Conversation_MessageHaveNotAccess) {
			throw new ParamException("User not have permissions for repost this message");
		} catch (Gateway_Socket_Exception_Conversation_IsNotAllowed) {
			throw new ParamException("Message is not exist");
		}

		if ($response["message_data"]["message"]["type"] == CONVERSATION_MESSAGE_TYPE_DELETED) {
			throw new cs_ParentMessage_IsDeleted("Parent message was deleted");
		}

		if ($response["message_data"]["message"]["type"] == CONVERSATION_MESSAGE_TYPE_RESPECT) {
			throw new cs_ParentMessage_IsRespect("Parent message is respect message");
		}

		return $response["message_data"]["message"];
	}
}