<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\ActionNotAllowed;
use CompassApp\Domain\Member\Exception\UserIsGuest;

/**
 * Контроллер, отвечающий за подгрузку ленты сообщений в треде
 */
class Apiv2_Threads_Feed extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getMessages",
		"repost",
		"getMessageReadParticipants",
	];

	/**
	 * Возвращает запрошенные блоки сообщений.
	 * @throws CaseException
	 */
	public function getMessages():array {

		$thread_key    = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$block_id_list = $this->post("?ai", "block_id_list", []);

		$thread_key = \CompassApp\Pack\Thread::tryDecrypt($thread_key);

		try {
			$result = Domain_Thread_Scenario_Feed_Api::getMessages($this->user_id, $thread_key, $block_id_list);
		} catch (cs_Message_HaveNotAccess) {
			return $this->error(2229008, "access denied to the parent");
		} catch (cs_Thread_UserNotMember) {
			return $this->error(2229009, "you're not a thread member");
		}

		[$message_list, $thread_meta, $previous_block_id_list, $next_block_id_list, $user_list] = $result;
		$this->action->users($user_list);

		return $this->ok([
			"thread_meta"            => (object) $thread_meta,
			"message_list"           => array_map(static fn(array $el) => Apiv1_Format::threadMessage($el), $message_list),
			"previous_block_id_list" => $previous_block_id_list,
			"next_block_id_list"     => $next_block_id_list,
		]);
	}

	/**
	 * Сделать репост
	 * Версия метода 3
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @long Большое количество ошибок для обработки
	 */
	public function repost():array {

		$thread_key              = $this->post(\Formatter::TYPE_STRING, "thread_key");
		$source_key              = $this->post(\Formatter::TYPE_STRING, "source_key");
		$source_type             = $this->post(\Formatter::TYPE_STRING, "source_type");
		$client_message_id       = $this->post(\Formatter::TYPE_UUID, "client_message_id");
		$text                    = $this->post(\Formatter::TYPE_STRING, "text", "");
		$source_message_key_list = $this->post(\Formatter::TYPE_ARRAY, "source_message_key_list");

		// получаем платформу
		try {
			$platform = Type_Api_Platform::getPlatform();
		} catch (cs_PlatformNotFound) {
			throw new ParamException("invalid platform");
		}

		// проверяем блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::THREADS_ADD_REPOST);

		$receiver_thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);
		$output              = [];
		try {

			[$source_map, $message_map_list] = match ($source_type) {
				Domain_Thread_Entity_Repost::REPOST_FROM_CONVERSATION_TYPE => $this->_decryptConversationMessageList($source_key, $source_message_key_list),
				Domain_Thread_Entity_Repost::REPOST_FROM_THREAD_TYPE, Domain_Thread_Entity_Repost::REPOST_FROM_THREAD_WITH_PARENT_TYPE =>
				$this->_decryptThreadMessageList($source_key, $source_message_key_list),
				default => throw new ParamException("unknown source type"),
			};

			Domain_Member_Entity_Permission::check($this->user_id, $this->method_version, Permission::IS_REPOST_MESSAGE_ENABLED);

			[$thread_meta, $message_list] = Domain_Thread_Scenario_Feed_Api::addRepost(
				$this->user_id, $source_type, $source_map, $receiver_thread_map, $message_map_list, $text, $client_message_id, $platform, $this->method_version);
		} catch (Domain_Thread_Exception_Message_IsDuplicated) {
			throw new ParamException("Passed duplicated message in repost");
		} catch (cs_Message_DuplicateClientMessageId) {

			if (Type_System_Legacy::isDuplicateClientMessageIdError()) {
				return $this->error(2229007, "duplicate client_message_id");
			}
			throw new ParamException("Passed duplicated client_message_id in repost");
		} catch (Domain_Thread_Exception_Message_IsFromDifferentSource) {
			throw new ParamException("passed keys from different sources");
		} catch (Domain_Thread_Exception_Message_RepostLimitExceeded) {
			throw new CaseException(2229004, "Too many messages for repost");
		} catch (Domain_Thread_Exception_UserHaveNoAccess) {
			throw new CaseException(2229002, "User have no access to thread");
		} catch (Domain_Thread_Exception_User_IsAccountDeleted) {
			throw new CaseException(2129001, "User delete his account");
		} catch (Domain_Thread_Exception_NoAccessUserbotDisabled) {
			throw new CaseException(2134001, "No access to thread, because userbot is disabled");
		} catch (Domain_Thread_Exception_NoAccessUserbotDeleted) {
			throw new CaseException(2134002, "No access to thread, because userbot is deleted");
		} catch (Domain_Thread_Exception_UserHaveNoAccessToSource) {
			throw new CaseException(2229003, "User have no access to source");
		} catch (Domain_Thread_Exception_Message_IsNotFromThread) {
			throw new ParamException("passed conversation message key is for thread");
		} catch (Domain_Thread_Exception_Message_NotExistThread) {
			throw new ParamException("message_list from another thread");
		} catch (Domain_Thread_Exception_Message_IsTooLong) {
			throw new ParamException("repost message is too long");
		} catch (cs_ThreadIsReadOnly) {
			throw new CaseException(2229005, "Thread is read-only");
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParamException("message list is empty");
		} catch (cs_ParentMessage_IsDeleted) {
			throw new CaseException(2229006, "Parent message is deleted");
		} catch (cs_ParentMessage_IsRespect) {
			throw new ParamException("cant repost respect message");
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Group_Exception_NotEnoughRights) {
			return $this->error(2129003, "not enough right");
		}

		foreach ($message_list as $message) {
			$output[] = Apiv1_Format::threadMessage($message);
		}

		return $this->ok([
			"thread_meta"  => (object) Apiv1_Format::threadMeta($thread_meta),
			"message_list" => (array) $output,
		]);
	}

	/**
	 * Получить список прочитавших сообщение
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws CaseException
	 * @throws ControllerMethodNotFoundException
	 * @throws ParamException
	 * @throws QueryFatalException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 */
	public function getMessageReadParticipants():array {

		$message_key = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map = \CompassApp\Pack\Message::tryDecrypt($message_key);

		try {
			[$read_participants, $read_participant_count] = Domain_Thread_Scenario_Feed_Api::getMessageReadParticipants(
				$this->user_id, $message_map, $this->role, $this->method_version);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|Gateway_Socket_Exception_Conversation_UserIsNotMember) {
			return $this->error(2229002, "You are not allow to do this action");
		} catch (ActionNotAllowed|UserIsGuest|Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Thread_Exception_Message_IsNotFromThread) {
			throw new ParamException("not thread message key");
		} catch (Domain_Thread_Exception_Message_ExpiredForGetReadParticipants) {
			return $this->error(2229010, "message sent 2 weeks ago");
		}

		return $this->ok([
			"message_read_participants"      => (array) $read_participants,
			"message_read_participant_count" => (int) $read_participant_count,
		]);
	}

	/**
	 * Расшифровать ключ чата и его сообщения для репоста
	 *
	 * @param string $conversation_key
	 * @param array  $message_key_list
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	protected function _decryptConversationMessageList(string $conversation_key, array $message_key_list):array {

		$message_map_list = [];
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		foreach ($message_key_list as $message_key) {

			if (!is_string($message_key)) {
				throw new ParamException("incorrect message key type");
			}
			$message_map_list[] = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		}

		return [$conversation_map, $message_map_list];
	}

	/**
	 * Расшифровать ключ треда и его сообщения для репоста
	 *
	 * @param string $thread_key
	 * @param array  $message_key_list
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected function _decryptThreadMessageList(string $thread_key, array $message_key_list):array {

		$message_map_list = [];
		$thread_map       = \CompassApp\Pack\Thread::tryDecrypt($thread_key);
		foreach ($message_key_list as $message_key) {

			if (!is_string($message_key)) {
				throw new ParamException("incorrect message key type");
			}
			$message_map_list[] = \CompassApp\Pack\Message\Thread::tryDecrypt($message_key);
		}

		return [$thread_map, $message_map_list];
	}
}
