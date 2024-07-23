<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * класс-интерфейс для работы с модулем conversation
 */
class Gateway_Socket_Conversation extends Gateway_Socket_Default {

	/**
	 * Добавляем массив репостнутых сообщений в диалог получателя
	 *
	 * @param string $conversation_map
	 * @param array  $reposted_message_list
	 * @param string $client_message_id
	 * @param string $text
	 * @param int    $user_id
	 * @param array  $message_data
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_Conversation_BlockedAnOpponent
	 * @throws Gateway_Socket_Exception_Conversation_BlockedByOpponent
	 * @throws Gateway_Socket_Exception_Conversation_DuplicateMessageClientId
	 * @throws Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled
	 * @throws Gateway_Socket_Exception_Conversation_IsLocked
	 * @throws Gateway_Socket_Exception_Conversation_IsNotAllowed
	 * @throws Gateway_Socket_Exception_Conversation_OpponentBlockedInSystem
	 * @throws Gateway_Socket_Exception_Conversation_UserIsNotMember
	 * @throws ReturnFatalException
	 * @throws cs_PlatformNotFound
	 * @long
	 */
	public static function addRepostFromThreadBatchingLegacy(string $conversation_map, array $reposted_message_list, string $client_message_id, string $text, int $user_id, array $message_data):array {

		[$status, $response] = self::doCall("conversations.addRepostFromThreadBatching", [
			"conversation_map"      => $conversation_map,
			"reposted_message_list" => $reposted_message_list,
			"client_message_id"     => $client_message_id,
			"text"                  => $text,
			"parent_message_data"   => $message_data,
			"platform"              => Type_Api_Platform::getPlatform(),
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10010   => new Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled("conversation is blocked or disabled", $response),
				10043   => new Gateway_Socket_Exception_Conversation_BlockedByOpponent("user is blocked by opponent"),
				10044   => new Gateway_Socket_Exception_Conversation_BlockedAnOpponent("user blocked an opponent"),
				10021   => new Gateway_Socket_Exception_Conversation_OpponentBlockedInSystem("opponent is blocked in system"),
				10011   => new Gateway_Socket_Exception_Conversation_UserIsNotMember("user is not conversation member"),
				10013   => new Gateway_Socket_Exception_Conversation_IsNotAllowed("conversation action is not allowed"),
				10018   => new Gateway_Socket_Exception_Conversation_IsLocked("conversation is locked"),
				10511   => new Gateway_Socket_Exception_Conversation_DuplicateMessageClientId("message client id is duplicated"),
				10024   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_SPACE_RESIDENT),
				10025   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_GUEST),
				10027   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_BOT),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * Добавляем массив репостнутых сообщений в диалог получателя
	 *
	 * @param string $conversation_map
	 * @param array  $reposted_message_list
	 * @param string $client_message_id
	 * @param string $text
	 * @param int    $user_id
	 * @param array  $message_data
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_Conversation_BlockedAnOpponent
	 * @throws Gateway_Socket_Exception_Conversation_BlockedByOpponent
	 * @throws Gateway_Socket_Exception_Conversation_DuplicateMessageClientId
	 * @throws Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled
	 * @throws Gateway_Socket_Exception_Conversation_IsLocked
	 * @throws Gateway_Socket_Exception_Conversation_IsNotAllowed
	 * @throws Gateway_Socket_Exception_Conversation_OpponentBlockedInSystem
	 * @throws Gateway_Socket_Exception_Conversation_UserIsNotMember
	 * @throws ReturnFatalException
	 * @throws cs_PlatformNotFound
	 * @long
	 */
	public static function addRepostFromThread(string $conversation_map, array $reposted_message_list, string $client_message_id, string $text, int $user_id, array $message_data):array {

		[$status, $response] = self::doCall("conversations.addRepostFromThread", [
			"conversation_map"      => $conversation_map,
			"reposted_message_list" => $reposted_message_list,
			"client_message_id"     => $client_message_id,
			"text"                  => $text,
			"parent_message_data"   => $message_data,
			"is_add_repost_quote"   => 1,
			"platform"              => Type_Api_Platform::getPlatform(),
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10010   => new Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled("user blocked or disabled", $response),
				10043   => new Gateway_Socket_Exception_Conversation_BlockedByOpponent("user blocked by opponent"),
				10044   => new Gateway_Socket_Exception_Conversation_BlockedAnOpponent("user blocked an opponent"),
				10021   => new Gateway_Socket_Exception_Conversation_OpponentBlockedInSystem("opponent blocked in system"),
				10011   => new Gateway_Socket_Exception_Conversation_UserIsNotMember("user is not member of conversation"),
				10013   => new Gateway_Socket_Exception_Conversation_IsNotAllowed("conversation action is not allowed"),
				10018   => new Gateway_Socket_Exception_Conversation_IsLocked("conversation is locked"),
				10511   => new Gateway_Socket_Exception_Conversation_DuplicateMessageClientId("message client id duplicated"),
				10024   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_SPACE_RESIDENT),
				10025   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_GUEST),
				10027   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_BOT),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * Добавляем массив репостнутых сообщений в диалог получателя - версии V2
	 *
	 * @param string $conversation_map
	 * @param array  $repost_list
	 * @param string $client_message_id
	 * @param string $text
	 * @param int    $user_id
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_Conversation_BlockedAnOpponent
	 * @throws Gateway_Socket_Exception_Conversation_BlockedByOpponent
	 * @throws Gateway_Socket_Exception_Conversation_DuplicateMessageClientId
	 * @throws Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled
	 * @throws Gateway_Socket_Exception_Conversation_IsLocked
	 * @throws Gateway_Socket_Exception_Conversation_IsNotAllowed
	 * @throws Gateway_Socket_Exception_Conversation_OpponentBlockedInSystem
	 * @throws Gateway_Socket_Exception_Conversation_UserIsNotMember
	 * @throws ReturnFatalException
	 * @throws cs_PlatformNotFound
	 */
	public static function addRepostFromThreadV2(string $conversation_map, array $repost_list, string $client_message_id, string $text, int $user_id):array {

		[$status, $response] = self::doCall("conversations.addRepostFromThreadV2", [
			"conversation_map"  => $conversation_map,
			"repost_list"       => $repost_list,
			"client_message_id" => $client_message_id,
			"text"              => $text,
			"platform"          => Type_Api_Platform::getPlatform(),
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10010   => new Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled("user blocked or disabled", $response),
				10043   => new Gateway_Socket_Exception_Conversation_BlockedByOpponent("user blocked by opponent"),
				10044   => new Gateway_Socket_Exception_Conversation_BlockedAnOpponent("user blocked an opponent"),
				10021   => new Gateway_Socket_Exception_Conversation_OpponentBlockedInSystem("opponent blocked in system"),
				10011   => new Gateway_Socket_Exception_Conversation_UserIsNotMember("user is not member of conversation"),
				10013   => new Gateway_Socket_Exception_Conversation_IsNotAllowed("conversation action is not allowed"),
				10018   => new Gateway_Socket_Exception_Conversation_IsLocked("conversation is locked"),
				10511   => new Gateway_Socket_Exception_Conversation_DuplicateMessageClientId("message client id duplicated"),
				10024   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_SPACE_RESIDENT),
				10025   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_GUEST),
				10027   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_BOT),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	// получаем список сообщений из диалога
	public static function getConversationMessageList(array $message_map_list, int $user_id):array {

		$ar_post = [
			"message_map_list" => $message_map_list,
		];
		return self::doCall("conversations.getMessageList", $ar_post, $user_id);
	}

	// получаем юзеров из списка диалогов
	public static function getUsersByConversationList(array $conversation_map_list, int $user_id):array {

		$ar_post = [
			"conversation_map_list" => $conversation_map_list,
		];
		return self::doCall("conversations.getUsersByConversationList", $ar_post, $user_id);
	}

	// проверяем, что диалог, в котором существует тред, пригоден для фиксации рабочего времени
	public static function isCanCommitWorkedHours(string $conversation_map):bool {

		$ar_post = [
			"conversation_map" => $conversation_map,
		];

		[, $response] = self::doCall("conversations.isCanCommitWorkedHours", $ar_post);

		if (!isset($response["is_can_commit_worked_hours"])) {
			throw new ParseFatalException(__METHOD__ . ": unexpected response");
		}

		return $response["is_can_commit_worked_hours"] == 1;
	}

	// пытаемся зафиксировать рабочие часы в чат "Личный Heroes", прикрепляя сообщения из треда
	public static function tryCommitWorkedHoursFromThread(int $user_id, float $worked_hours, array $selected_message_list, array $parent_message_data):array {

		return self::doCall("conversations.tryCommitWorkedHoursFromThread", [
			"selected_message_list" => $selected_message_list,
			"parent_message_data"   => $parent_message_data,
			"worked_hours"          => $worked_hours,
		], $user_id);
	}

	/**
	 * Пытаемся проявить требовательность из треда
	 *
	 * @param int   $user_id
	 * @param array $user_id_list
	 * @param array $selected_message_list
	 * @param array $parent_message_data
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_Conversation_MessageLimitExceeded
	 * @throws Gateway_Socket_Exception_Conversation_UserIsNotMember
	 * @throws ReturnFatalException
	 */
	public static function tryExactingFromThread(int $user_id, array $user_id_list, array $selected_message_list, array $parent_message_data):array {

		[$status, $response] = self::doCall("conversations.tryExactingFromThread", [
			"selected_message_list" => $selected_message_list,
			"parent_message_data"   => $parent_message_data,
			"user_id_list"          => $user_id_list,
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10011   => new Gateway_Socket_Exception_Conversation_UserIsNotMember("user is not conversation member"),
				6000    => new Gateway_Socket_Exception_Conversation_MessageLimitExceeded("message limit exceeded"),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * получаем conversation_map диалога "Наймы и увольнения"
	 *
	 * @return string
	 *
	 * @throws ReturnFatalException
	 */
	public static function getHiringConversationMap():string {

		[$status, $response] = self::doCall("conversations.getHiringConversationMap", []);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected error code");
		}

		return $response["conversation_map"];
	}

	/**
	 * получаем данные для создания треда заявки
	 *
	 * @return array
	 *
	 * @throws ReturnFatalException
	 */
	#[ArrayShape(["conversation_map" => "string", "conversation_type" => "int", "users" => "array", "user_mute_info" => "array", "user_clear_info" => "array"])]
	public static function getDataForCreateThreadOnHireRequest():array {

		[$status, $response] = self::doCall("conversations.getConversationDataForCreateThreadInHireRequest", []);

		return [
			"conversation_map"  => (string) $response["conversation_map"],
			"conversation_type" => (int) $response["conversation_type"],
			"users"             => (array) $response["users"],
			"user_mute_info"    => (array) $response["user_mute_info"],
			"user_clear_info"   => (array) $response["user_clear_info"],
		];
	}

	/**
	 * Спрятать тред у пользователя
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param string $parent_conversation_map
	 *
	 * @throws ReturnFatalException
	 */
	public static function hideThreadForUser(int $user_id, string $thread_map, string $parent_conversation_map):void {

		$ar_post = [
			"conversation_map" => $parent_conversation_map,
			"thread_map"       => $thread_map,
		];
		self::doCall("conversations.hideThreadForUser", $ar_post, $user_id);
	}

	/**
	 * раскрываем тред у пользователя
	 *
	 * @param string $thread_map
	 * @param string $parent_conversation_map
	 *
	 * @throws ReturnFatalException
	 */
	public static function revealThread(string $thread_map, string $parent_conversation_map):void {

		$ar_post = [
			"conversation_map" => $parent_conversation_map,
			"thread_map"       => $thread_map,
		];
		self::doCall("conversations.revealThread", $ar_post);
	}

	/**
	 * Добавить список файлов из треда в список файлов диалога
	 *
	 * @param string $conversation_map
	 * @param string $conversation_message_map
	 * @param string $message_map
	 * @param array  $need_add_file_list
	 *
	 * @throws ReturnFatalException
	 */
	public static function addFileListToConversation(string $conversation_map, string $conversation_message_map, string $message_map, array $need_add_file_list):void {

		$ar_post = [
			"conversation_map"         => $conversation_map,
			"conversation_message_map" => $conversation_message_map,
			"message_map"              => $message_map,
			"need_add_file_list"       => $need_add_file_list,
		];
		self::doCall("conversations.addThreadFileListToConversation", $ar_post);
	}

	/**
	 * Добавить список файлов из треда в список файлов диалога найма и увольнения
	 *
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param array  $need_add_file_list
	 * @param int    $created_at
	 *
	 * @throws ReturnFatalException
	 */
	public static function addFileListToHiringConversation(string $conversation_map, string $message_map, array $need_add_file_list, int $created_at):void {

		$ar_post = [
			"conversation_map"   => $conversation_map,
			"message_map"        => $message_map,
			"need_add_file_list" => $need_add_file_list,
			"created_at"         => $created_at,
		];
		self::doCall("conversations.addThreadFileListToHiringConversation", $ar_post);
	}

	/**
	 * Скрываем список файлов
	 *
	 * @param string $conversation_map
	 * @param array  $file_uuid_list
	 * @param int    $user_id
	 *
	 * @throws ReturnFatalException
	 */
	public static function hideThreadFileList(string $conversation_map, array $file_uuid_list, int $user_id):void {

		$ar_post = [
			"conversation_map" => $conversation_map,
			"file_uuid_list"   => $file_uuid_list,
		];
		self::doCall("conversations.hideThreadFileList", $ar_post, $user_id);
	}

	/**
	 * Удаляем список файлов из треда
	 *
	 * @param string $conversation_map
	 * @param array  $file_uuid_list
	 *
	 * @throws ReturnFatalException
	 */
	public static function deleteThreadFileList(string $conversation_map, array $file_uuid_list):void {

		$ar_post = [
			"conversation_map" => $conversation_map,
			"file_uuid_list"   => $file_uuid_list,
		];
		self::doCall("conversations.deleteThreadFileList", $ar_post);
	}

	/**
	 * Обновляем метку и версию обновления тредов в диалоге
	 *
	 * @param string $conversation_map
	 *
	 * @throws ReturnFatalException
	 */
	public static function updateThreadsUpdatedData(string $conversation_map):int {

		$ar_post = [
			"conversation_map" => $conversation_map,
		];
		[$status, $response] = self::doCall("conversations.updateThreadsUpdatedData", $ar_post);

		return $response["threads_updated_version"];
	}

	/**
	 * Получаем версию обновления тредов в диалоге
	 *
	 * @throws ReturnFatalException
	 */
	public static function getThreadsUpdatedVersion(string $conversation_map):int {

		$ar_post = [
			"conversation_map" => $conversation_map,
		];
		[$status, $response] = self::doCall("conversations.getThreadsUpdatedVersion", $ar_post);

		return $response["threads_updated_version"];
	}

	/**
	 * Получить мету для создания треда
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 *
	 * @throws Gateway_Socket_Exception_Conversation_MessageIsDeleted
	 * @throws Gateway_Socket_Exception_Conversation_NotFound
	 * @throws ReturnFatalException
	 * @throws Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled
	 * @throws Gateway_Socket_Exception_Conversation_IsLocked
	 * @throws Gateway_Socket_Exception_Conversation_MessageHaveNotAccess
	 */
	public static function getMetaForCreateThread(int $user_id, string $message_map):array {

		[$status, $response] = self::doCall("conversations.getMetaForCreateThread", [
			"message_map" => $message_map,
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10028               => new Gateway_Socket_Exception_Conversation_MessageIsDeleted("message is deleted"),
				10003, 10017, 10101 => new Gateway_Socket_Exception_Conversation_MessageHaveNotAccess("user have not access to message"),
				10018               => new Gateway_Socket_Exception_Conversation_IsLocked("conversation is locked"),
				10010               => new Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled("conversation is blocked or disabled", $response),
				10023               => new Gateway_Socket_Exception_Conversation_NotFound("conversation is not found"),
				10024               => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_SPACE_RESIDENT),
				10025               => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_GUEST),
				10027               => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_BOT),
				default             => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * Получить мету для создания треда
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 *
	 * @throws Gateway_Socket_Exception_Conversation_MessageIsDeleted
	 * @throws Gateway_Socket_Exception_Conversation_NotFound
	 * @throws ReturnFatalException
	 */
	public static function getMetaForMigrationCreateThread(int $user_id, string $message_map):array {

		[$status, $response] = self::doCall("conversations.getMetaForMigrationCreateThread", [
			"message_map" => $message_map,
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10028               => new Gateway_Socket_Exception_Conversation_MessageIsDeleted("message is deleted"),
				10023               => new Gateway_Socket_Exception_Conversation_NotFound("conversation is not found"),
				default             => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * Получить dynamic
	 *
	 * @param string $conversation_map
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	public static function getDynamic(string $conversation_map):array {

		[$status, $response] = self::doCall("conversations.getDynamicForThread", [
			"conversation_map" => $conversation_map,
		]);

		if ($status === "error") {
			throw new ParseFatalException("error get dynamic");
		}
		return [
			$response["location_type"],
			fromJson($response["user_clear_info"]),
			fromJson($response["user_mute_info"]),
			fromJson($response["conversation_clear_info"]),
		];
	}

	/**
	 * Добавить тред к сообщению
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 * @param string $thread_map
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_Conversation_IsLocked
	 * @throws ReturnFatalException
	 */
	public static function addThreadToMessage(int $user_id, string $message_map, string $thread_map, bool $is_thread_hidden_for_all_users):array {

		[$status, $response] = self::doCall("conversations.addThreadToMessage", [
			"message_map"                    => $message_map,
			"thread_map"                     => $thread_map,
			"is_thread_hidden_for_all_users" => $is_thread_hidden_for_all_users ? 1 : 0,
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10018   => new Gateway_Socket_Exception_Conversation_IsLocked("conversation is locked"),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * Получить юзеров чата
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled
	 * @throws ReturnFatalException
	 */
	public static function getUsers(int $user_id, string $conversation_map):array {

		[$status, $response] = self::doCall("conversations.getUsers", [
			"conversation_map" => $conversation_map,
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10010   => new Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled("conversation is blocked or disabled", $response),
				10024   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_SPACE_RESIDENT),
				10025   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_GUEST),
				10027   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_BOT),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * Получить информацию о сообщении
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_Conversation_IsNotAllowed
	 * @throws Gateway_Socket_Exception_Conversation_MessageHaveNotAccess
	 * @throws ReturnFatalException
	 */
	public static function getMessageData(int $user_id, string $message_map):array {

		[$status, $response] = self::doCall("conversations.getMessageData", [
			"message_map" => $message_map,
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10101   => new Gateway_Socket_Exception_Conversation_MessageHaveNotAccess("user doesn't have access to conversation message"),
				10013   => new Gateway_Socket_Exception_Conversation_IsNotAllowed("conversation action is not allowed"),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * получаем данные сообщения для отправки Напоминания
	 *
	 * @throws Gateway_Socket_Exception_Conversation_IsNotAllowed
	 * @throws Gateway_Socket_Exception_Conversation_MessageHaveNotAccess
	 * @throws ReturnFatalException
	 */
	public static function getMessageDataForSendRemind(int $user_id, string $message_map):array {

		[$status, $response] = self::doCall("conversations.getMessageDataForSendRemind", [
			"message_map" => $message_map,
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10101   => new Gateway_Socket_Exception_Conversation_MessageHaveNotAccess("user doesn't have access to conversation message"),
				10013   => new Gateway_Socket_Exception_Conversation_IsNotAllowed("conversation action is not allowed"),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * Получить сообщение чата
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_Conversation_MessageHaveNotAccess
	 * @throws Gateway_Socket_Exception_ParentEntityNotFound
	 * @throws ReturnFatalException
	 */
	public static function getMessage(int $user_id, string $message_map):array {

		[$status, $response] = self::doCall("conversations.getMessage", [
			"message_map" => $message_map,
		], $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10100   => new Gateway_Socket_Exception_ParentEntityNotFound("parent entity not found"),
				10101   => new Gateway_Socket_Exception_Conversation_MessageHaveNotAccess("user doesn't have access to conversation message"),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response;
	}

	/**
	 * Получаем список сообщений для репоста из чата
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param array  $message_map_list
	 * @param string $client_message_id
	 * @param string $text
	 * @param string $platform
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_Conversation_IsLocked
	 * @throws Gateway_Socket_Exception_Conversation_MessageListIsEmpty
	 * @throws Gateway_Socket_Exception_Conversation_UserIsNotMember
	 * @throws ReturnFatalException
	 */
	public static function getRepostMessages(int $user_id, string $conversation_map, array $message_map_list, string $client_message_id, string $text, string $platform):array {

		$ar_post = [
			"conversation_map"  => $conversation_map,
			"message_map_list"  => $message_map_list,
			"text"              => $text,
			"client_message_id" => $client_message_id,
			"platform"          => $platform,
		];

		[$status, $response] = self::doCall("conversations.getRepostMessages", $ar_post, $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				10011   => new Gateway_Socket_Exception_Conversation_UserIsNotMember("user is not conversation member"),
				10018   => new Gateway_Socket_Exception_Conversation_IsLocked("conversation is locked"),
				2418001 => new Gateway_Socket_Exception_Conversation_MessageListIsEmpty("message list is empty"),
				552     => new Domain_Thread_Exception_Message_RepostLimitExceeded("exceeded the limit on the number of quoted messages"),
				default => new ReturnFatalException("unexpected error code")
			};
		}
		return $response["message_list"];
	}

	/**
	 * Подтверждаем репост в тред
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param string $thread_map
	 * @param array  $message_map_list
	 *
	 * @return void
	 * @throws ReturnFatalException
	 */
	public static function confirmThreadRepost(int $user_id, string $conversation_map, string $thread_map, array $message_map_list):void {

		$ar_post = [
			"conversation_map" => $conversation_map,
			"message_map_list" => $message_map_list,
			"thread_map"       => $thread_map,
		];

		[$status] = self::doCall("conversations.confirmThreadRepost", $ar_post, $user_id);

		if ($status === "error") {
			throw new ReturnFatalException("unexpected error code");
		}
	}

	/**
	 * создаём Напоминание к сообщению диалога
	 *
	 * @throws Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled
	 * @throws Gateway_Socket_Exception_Conversation_MessageIsDeleted
	 * @throws Gateway_Socket_Exception_Conversation_MessageIsNotExist
	 * @throws Gateway_Socket_Exception_Conversation_MessageNotAllowForRemind
	 * @throws Gateway_Socket_Exception_Conversation_MessageTextIsTooLong
	 * @throws Gateway_Socket_Exception_Conversation_RemindAlreadyExist
	 * @throws Gateway_Socket_Exception_Conversation_UserIsNotMember
	 * @throws ReturnFatalException
	 */
	public static function createRemindOnConversationMessage(int $user_id, string $message_map, int $remind_at, string $comment):int {

		$ar_post = [
			"message_map" => $message_map,
			"remind_at"   => $remind_at,
			"comment"     => $comment,
		];

		[$status, $response] = self::doCall("conversations.createRemindOnMessage", $ar_post, $user_id);

		if ($status === "error") {

			throw match ($response["error_code"]) {
				2418003 => new Gateway_Socket_Exception_Conversation_MessageIsDeleted("message is deleted"),
				2418004 => new Gateway_Socket_Exception_Conversation_MessageNotAllowForRemind("message not allow for remind"),
				2418005 => new Gateway_Socket_Exception_Conversation_MessageTextIsTooLong("message text is too long"),
				2418001 => new Gateway_Socket_Exception_Conversation_UserIsNotMember("user is not conversation member"),
				2435001 => new Gateway_Socket_Exception_Conversation_RemindAlreadyExist("remind already exist for message"),
				2418007 => new Gateway_Socket_Exception_Conversation_MessageIsNotExist("message is not exist"),
				10010   => new Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled("conversation is blocked or disabled", $response),
				10024   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_SPACE_RESIDENT),
				10025   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_GUEST),
				10027   => new Domain_Thread_Exception_Guest_AttemptInitialThread(Domain_Thread_Exception_Guest_AttemptInitialThread::OPPONENT_TRAIT_BOT),
				default => new ReturnFatalException("unexpected error code")
			};
		}

		return $response["remind_id"];
	}

	/**
	 * Прикрепляем превью к чату
	 *
	 * @param int    $user_id
	 * @param string $thread_message_map
	 * @param string $conversation_message_map
	 * @param string $preview_map
	 * @param int    $message_created_at
	 * @param array  $link_list
	 *
	 * @throws ReturnFatalException
	 */
	public static function attachPreviewToConversation(int    $user_id, string $thread_message_map,
									   string $conversation_message_map, string $preview_map, int $message_created_at, array $link_list):void {

		$ar_post = [
			"thread_message_map"       => $thread_message_map,
			"conversation_message_map" => $conversation_message_map,
			"message_created_at"       => $message_created_at,
			"preview_map"              => $preview_map,
			"link_list"                => $link_list,
		];

		[$status, $_] = self::doCall("conversations.attachPreview", $ar_post, $user_id);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected error code");
		}
	}

	/**
	 * Удаляем превью из чата
	 *
	 * @param array $thread_message_map_list
	 *
	 * @throws ReturnFatalException
	 */
	public static function deletePreviewListFromConversation(array $thread_message_map_list):void {

		$ar_post = [
			"thread_message_map_list" => $thread_message_map_list,
		];

		[$status, $_] = self::doCall("conversations.deletePreviewList", $ar_post);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected error code");
		}
	}

	/**
	 * Прячем превью в чате
	 *
	 * @param int   $user_id
	 * @param array $thread_message_map_list
	 *
	 * @throws ReturnFatalException
	 */
	public static function hidePreviewListFromConversation(int $user_id, array $thread_message_map_list):void {

		$ar_post = [
			"thread_message_map_list" => $thread_message_map_list,
		];

		[$status, $_] = self::doCall("conversations.hidePreviewList", $ar_post, $user_id);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected error code");
		}
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// формируем сообщение
		$ar_post  = [
			"method"        => $method,
			"company_id"    => COMPANY_ID,
			"user_id"       => $user_id,
			"sender_module" => CURRENT_MODULE,
			"json_params"   => toJson($params),
			"signature"     => "",
		];
		$response = \Application\Entrypoint\Socket::processRequest("Conversation", $method, "conversation", $ar_post, true);
		$response = fromJson(toJson($response));

		// проверяем, пришел ли нормальный запрос
		if (!isset($response["status"])
			|| !in_array($response["status"], ["ok", "error"])
			|| ($response["status"] === "error" && !isset($response["response"]["error_code"]))) {

			throw new ReturnFatalException($method . ": socket call returns bad response");
		}

		return [$response["status"], $response["response"]];
	}
}
