<?php

declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use JetBrains\PhpStorm\Pure;

/**
 * Action для создания треда к сообщению диалога
 */
class Domain_Thread_Action_AddToConversationMessage {

	/**
	 * добавляем тред к сообщению диалога
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 * @param bool   $is_thread_hidden_for_all_users
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws \parseException
	 * @throws cs_ConversationIsLocked
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Thread_ParentEntityNotFound
	 * @long
	 */
	public static function do(int $user_id, string $message_map, bool $is_thread_hidden_for_all_users = false):array {

		$response = self::_sendSocketGetMetaForCreateThread($user_id, $message_map);
		if ($response["is_exist"] == 1) {
			return Type_Thread_Meta::getOne($response["thread_map"]);
		}

		// в диалоге поддержки нельзя создавать тред
		if ($response["conversation_meta_row"]["type"] == CONVERSATION_TYPE_GROUP_SUPPORT) {
			throw new ParamException("action not allowed");
		}

		$conversation_map            = $response["conversation_meta_row"]["conversation_map"];
		$creator_user_id             = $response["creator_user_id"];
		$message_created_at          = $response["message_created_at"];
		$message_hidden_by_user_list = $response["message_hidden_by_user_list"];
		$creator_and_unfollow_users  = Gateway_Bus_CompanyCache::getMemberList(array_merge($message_hidden_by_user_list, [$creator_user_id,]));
		$unfollow_user_id_list_assoc = array_intersect_key($creator_and_unfollow_users, array_flip($message_hidden_by_user_list));
		$is_need_follow_creator      = self::_isNeedFollowUser($creator_user_id, $message_hidden_by_user_list, $response["creator_clear_until"], $message_created_at);

		// создаем тред, передавая пустой $user_mute_info, потому что треды на данный момент больше не мьютятся,
		// поэтому мы не обращаем внимания на то, что сам диалог/группа могли быть замьючены
		$thread_meta_row = self::_createPrivateThread(
			$conversation_map,
			$response["users"],
			$user_id,
			$creator_user_id,
			$message_map,
			$message_created_at,
			$is_need_follow_creator,
			$unfollow_user_id_list_assoc,
			$message_hidden_by_user_list
		);

		// отправляем сокет запрос на прикрепление треда к сообщению
		$response = self::_sendSocketAddThreadToMessage($user_id, $thread_meta_row["thread_map"], $message_map, $is_thread_hidden_for_all_users);
		if ($response["thread_map"] != $thread_meta_row["thread_map"]) {
			return Type_Thread_Meta::getOne($response["thread_map"]);
		}

		// прикрепляем пользоваетеля если нужно
		if ($is_need_follow_creator) {
			Type_Thread_Menu::setFollowUserList([$creator_user_id], $thread_meta_row["thread_map"], $thread_meta_row["parent_rel"]);
		}

		self::_sendEventOnThreadAddWithoutExcludedUsers($thread_meta_row, $conversation_map, $message_map, $unfollow_user_id_list_assoc, $user_id);

		// инкрементим количество действий
		Domain_User_Action_IncActionCount::incThreadCreated($user_id, $conversation_map);

		return $thread_meta_row;
	}

	/**
	 * получаем мету диалога для создания треда
	 *
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Thread_ParentEntityNotFound
	 * @throws ReturnFatalException
	 * @throws cs_ConversationIsLocked
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 */
	protected static function _sendSocketGetMetaForCreateThread(int $user_id, string $message_map):array {

		// запрос к php_conversation, спрашиваем можно ли прикрепить тред к этому сообщению
		try {
			return Gateway_Socket_Conversation::getMetaForCreateThread($user_id, $message_map);
		} catch (Gateway_Socket_Exception_Conversation_MessageIsDeleted) {
			throw new cs_Message_IsDeleted();
		} catch (Gateway_Socket_Exception_Conversation_MessageHaveNotAccess) {
			throw new cs_Message_HaveNotAccess();
		} catch (Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled $e) {
			throw new cs_Conversation_IsBlockedOrDisabled($e->getExtra()["allow_status"]);
		} catch (Gateway_Socket_Exception_Conversation_IsLocked) {
			throw new cs_ConversationIsLocked();
		} catch (Gateway_Socket_Exception_Conversation_NotFound) {
			throw new cs_Thread_ParentEntityNotFound();
		}
	}

	/**
	 * функция которая определяет нужно ли фолловить пользователя
	 */
	#[Pure] protected static function _isNeedFollowUser(int $user_id, array $message_hidden_by_user_list, int $clear_until, int $message_created_at):bool {

		// если юзер скрыл сообщение - нет
		if (in_array($user_id, $message_hidden_by_user_list)) {
			return false;
		}

		// если диалог очищен после отправки сообщения - нет
		if ($clear_until > $message_created_at) {
			return false;
		}

		// по умолчанию - да
		return true;
	}

	/**
	 * метод для создания треда
	 *
	 * @param string $conversation_map
	 * @param array  $users
	 * @param int    $user_id
	 * @param int    $creator_user_id
	 * @param string $message_map
	 * @param int    $message_created_at
	 * @param bool   $is_need_follow_creator
	 * @param array  $unfollowed_user_id_list_assoc
	 * @param array  $message_hidden_by_user_list
	 *
	 * @return array
	 * @long
	 */
	protected static function _createPrivateThread(
		string $conversation_map,
		array  $users,
		int    $user_id,
		int    $creator_user_id,
		string $message_map,
		int    $message_created_at,
		bool   $is_need_follow_creator,
		array  $unfollowed_user_id_list_assoc = [],
		array  $message_hidden_by_user_list = []
	):array {

		$users      = self::_makeThreadMetaUsers($users);
		$parent_rel = Type_Thread_ParentRel::create(
			PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE,
			$creator_user_id,
			$message_map,
			$message_created_at,
			$message_hidden_by_user_list
		);

		$un_followers_models = self::_makeUnfollowerMetaUsers($unfollowed_user_id_list_assoc);

		$source_parent_rel = Type_Thread_SourceParentRel::create($conversation_map, SOURCE_PARENT_ENTITY_TYPE_CONVERSATION);
		return Type_Thread_Private::create(
			$users,
			$source_parent_rel,
			$parent_rel,
			$user_id,
			$creator_user_id,
			$is_need_follow_creator,
			$un_followers_models
		);
	}

	/**
	 * формируем участников треда на основе участников meta сущности
	 *
	 */
	protected static function _makeThreadMetaUsers(array $users):array {

		$thread_meta_users = [];
		foreach ($users as $user_info) {
			$thread_meta_users[$user_info["user_id"]] = Type_Thread_Meta_Users::initUserSchema(THREAD_MEMBER_ACCESS_ALL);
		}

		return $thread_meta_users;
	}

	/**
	 * формируем участников треда на основе участников meta сущности
	 *
	 */
	protected static function _makeUnfollowerMetaUsers(array $users):array {

		$unfollower_meta_user = [];
		foreach ($users as $user_info) {
			$unfollower_meta_user[$user_info->user_id] = Gateway_Db_CompanyThread_ThreadFollowerList::initUnfollowerSchema();
		}

		return $unfollower_meta_user;
	}

	/**
	 * получаем мету диалога для создания треда
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param string $message_map
	 * @param bool   $is_thread_hidden_for_all_users
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws cs_ConversationIsLocked
	 */
	protected static function _sendSocketAddThreadToMessage(int $user_id, string $thread_map, string $message_map, bool $is_thread_hidden_for_all_users):array {

		// запрос к php_conversation, добавляем тред к сообщению
		try {
			return Gateway_Socket_Conversation::addThreadToMessage($user_id, $message_map, $thread_map, $is_thread_hidden_for_all_users);
		} catch (Gateway_Socket_Exception_Conversation_IsLocked) {
			throw new cs_ConversationIsLocked();
		}
	}

	/**
	 * отправляем событие о добавлении треда, кроме тех кто включен в список исключений
	 *
	 * @param array  $thread_meta_row
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param array  $unfollow_user_id_list_assoc
	 * @param int    $user_id
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	protected static function _sendEventOnThreadAddWithoutExcludedUsers(array $thread_meta_row, string $conversation_map, string $message_map, array $unfollow_user_id_list_assoc, int $user_id):void {

		$users_event_thread_add_assoc = $thread_meta_row["users"];

		if ($unfollow_user_id_list_assoc) {
			$users_event_thread_add_assoc = array_diff_key($users_event_thread_add_assoc, $unfollow_user_id_list_assoc);
		}

		self::_sendEventOnThreadAdd($thread_meta_row, $conversation_map, $message_map, $users_event_thread_add_assoc, $user_id);
	}

	/**
	 * отправляем ws-события при создании треда
	 *
	 * @param array  $thread_meta_row
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param array  $users
	 * @param int    $user_id
	 *
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 * @throws \parseException
	 */
	protected static function _sendEventOnThreadAdd(array $thread_meta_row, string $conversation_map, string $message_map, array $users, int $user_id):void {

		// формируем список пользователей для отправки в go_sender
		$talking_user_list = Type_Thread_Meta_Users::getTalkingUserList($users);

		// подготавливаем thread_meta_row для ws
		$prepared_meta_row  = Type_Thread_Utils::prepareThreadMetaForFormat($thread_meta_row, $user_id);
		$formatted_meta_row = Apiv1_Format::threadMeta($prepared_meta_row);
		$routine_key        = sha1($message_map);

		// отправляем ивент о прикреплении треда к сообщению
		Gateway_Bus_Sender::conversationMessageThreadAttached(
			$talking_user_list,
			$formatted_meta_row,
			$message_map,
			$thread_meta_row["thread_map"],
			$conversation_map,
			$routine_key
		);

		// привязываем пользователей к треду для тайпингов
		Gateway_Bus_Sender::addUsersToThread(array_keys($users), $thread_meta_row["thread_map"], $routine_key);
	}
}