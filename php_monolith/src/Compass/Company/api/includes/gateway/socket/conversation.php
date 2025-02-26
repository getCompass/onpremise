<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\CompanyIsHibernatedException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use Compass\Pivot\cs_CompanyIsHibernate;
use Compass\Pivot\Gateway_Socket_Exception_CompanyIsNotServed;
use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Класс-интерфейс для работы с модулем conversation
 */
class Gateway_Socket_Conversation extends Gateway_Socket_Default {

	/**
	 * отправляем сообщение с файлом
	 *
	 * @param int    $sender_id
	 * @param string $file_key
	 * @param string $conversation_map
	 *
	 * @throws ReturnFatalException
	 */
	public static function sendMessageWithFile(int $sender_id, string $file_key, string $conversation_map):void {

		$ar_post = [
			"sender_id"        => $sender_id,
			"file_key"         => $file_key,
			"conversation_map" => $conversation_map,
		];
		[$status,] = self::doCall("system.sendMessageWithFile", $ar_post);

		// если вернулась ошибка при удалении
		if ($status != "ok") {

			throw new ReturnFatalException(__METHOD__ . ": request to socket method is failed");
		}
	}

	/**
	 * Отправляем сообщение от имени бота поддержки в чат поддержки
	 *
	 * @param int    $receiver_user_id
	 * @param string $text
	 *
	 * @throws ReturnFatalException
	 */
	public static function addMessageFromSupportBot(int $receiver_user_id, string $text):void {

		$ar_post = [
			"receiver_user_id" => $receiver_user_id,
			"text"             => $text,
		];
		[$status,] = self::doCall("intercom.addMessageFromSupportBot", $ar_post);

		// если вернулась ошибка при удалении
		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method is failed");
		}
	}

	/**
	 * коммитим автоматические рабочие часы
	 *
	 * @param array $user_id_list
	 * @param float $worked_hours
	 * @param int   $auto_commit_time_at
	 *
	 * @throws ReturnFatalException
	 */
	public static function autoCommitWorkedHours(array $user_id_list, float $worked_hours, int $auto_commit_time_at):void {

		$ar_post = [
			"user_id_list"        => $user_id_list,
			"worked_hours"        => $worked_hours,
			"auto_commit_time_at" => $auto_commit_time_at,
		];

		[$status] = self::doCall("conversations.doAutoCommitWorkedHours", $ar_post);

		// если вернулась ошибка при автофиксации рабочих часов
		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method conversations.doAutoCommitWorkedHours is failed");
		}
	}

	/**
	 * добавляем в группы
	 *
	 * @param int   $inviter_user_id
	 * @param int   $invited_user_id
	 * @param array $conversation_map_list
	 *
	 * @return array|mixed
	 *
	 * @throws ReturnFatalException
	 */
	public static function joinToGroupConversationList(int $inviter_user_id, int $invited_user_id, array $conversation_map_list):array|false {

		if (count($conversation_map_list) < 1) {
			return false;
		}

		$ar_post = [
			"user_id"               => $invited_user_id,
			"conversation_map_list" => $conversation_map_list,
		];

		[$status, $response] = self::doCall("conversations.joinToGroupConversationList", $ar_post, $inviter_user_id);

		// если вернулась ошибка
		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 10120) {

				return [
					"ok_list"           => $response["ok_list"],
					"is_not_exist_list" => $response["is_not_exist_list"],
					"is_not_owner_list" => $response["is_not_owner_list"],
					"is_not_group_list" => $response["is_not_group_list"],
					"is_leaved_list"    => $response["is_leaved_list"],
					"is_kicked_list"    => $response["is_kicked_list"],
				];
			}

			throw new ReturnFatalException(__METHOD__ . ": request to socket method conversations.joinToGroupConversationList is failed");
		}
		return false;
	}

	/**
	 * отправляем сообщение-респект из карточки
	 *
	 * @param int    $sender_user_id
	 * @param int    $receiver_user_id
	 * @param int    $respect_id
	 * @param string $text
	 *
	 * @return string
	 * @throws \blockException
	 * @throws ReturnFatalException
	 * @throws paramException
	 */
	public static function addRespectToConversation(int $sender_user_id, int $receiver_user_id, int $respect_id, string $text):string {

		$ar_post = [
			"respect_id"       => $respect_id,
			"respect_text"     => $text,
			"receiver_user_id" => $receiver_user_id,
		];

		[$status, $response] = self::doCall("conversations.addRespect", $ar_post, $sender_user_id);

		if ($status != "ok") {

			// если не задан код ошибки
			if (!isset($response["error_code"])) {
				throw new ReturnFatalException(__METHOD__ . ": request to socket method conversations.addMessageFromEmployeeCard is failed");
			}

			// работаем над кодом ошибки в ответе
			self::_workWithErrorCodeForAddMessageFromEmployeeCard($response["error_code"]);
		}

		return $response["message_map"];
	}

	/**
	 * работаем с кодом ошибки в ответе
	 *
	 * @param int $error_code
	 *
	 * @throws \blockException
	 * @throws ReturnFatalException
	 * @throws paramException
	 */
	protected static function _workWithErrorCodeForAddMessageFromEmployeeCard(int $error_code):void {

		switch ($error_code) {

			case 10017:
				throw new Domain_EmployeeCard_Exception_Respect_NotConversationMember("user is not member of group");
			case 10018:
				throw new BlockException("Conversation is locked");
			case 10022:
				throw new ParamException("Dialog does not have type is group");
		}

		throw new ReturnFatalException("Socket request status != ok.");
	}

	/**
	 * редактируем текст респекта в группе Респекты
	 */
	public static function editRespectText(int $creator_user_id, string $message_map, string $new_text):void {

		$ar_post = [
			"user_id"       => $creator_user_id,
			"message_map"   => $message_map,
			"new_text"      => $new_text,
			"is_force_edit" => 1, // не обращаем внимание на timeout для сообщения
		];
		[$status, $response] = self::doCall("conversations.tryEditMessageText", $ar_post, $creator_user_id);

		// если вернулась ошибка при редактировании респекта в диалоге, то ничего не делаем
		if ($status != "ok") {

			Type_System_Admin::log("respect_edit_fail", [
				"message_map"     => $message_map,
				"creator_user_id" => $creator_user_id,
				"socket_response" => $response,
			]);

			// работаем над кодом ошибки в ответе
			if (isset($response["error_code"]) && $response["error_code"] == 10017) {
				throw new Domain_EmployeeCard_Exception_Respect_NotConversationMember("user is not member of group");
			}
		}
	}

	/**
	 * редактируем текст достижения в группе Достижения
	 */
	public static function editAchievementText(int $creator_user_id, string $message_map, string $new_header, string $new_description):void {

		$message_text = Type_User_Card_Achievement::initConversationMessage($new_header, $new_description);

		$ar_post = [
			"user_id"       => $creator_user_id,
			"message_map"   => $message_map,
			"new_text"      => $message_text,
			"is_force_edit" => 1, // не обращаем внимание на timeout для сообщения
		];
		[$status, $response] = self::doCall("conversations.tryEditMessageText", $ar_post, $creator_user_id);

		// если вернулась ошибка при редактировании достижения в диалоге, то ничего не делаем
		if ($status != "ok") {

			Type_System_Admin::log("achievement_edit_fail", [
				"message_map"     => $message_map,
				"creator_user_id" => $creator_user_id,
				"socket_response" => $response,
			]);
		}
	}

	/**
	 *  Создаем дефолтные группы
	 */
	public static function createCompanyExtendedEmployeeCardGroups(int $creator_user_id):void {

		$ar_post = [
			"locale" => \BaseFrame\System\Locale::getLocale(),
		];

		[$status,] = self::doCall("groups.createCompanyExtendedEmployeeCardGroups", $ar_post, $creator_user_id);

		// если вернулась ошибка при редактировании достижения в диалоге, то ничего не делаем
		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method groups.createCompanyExtendedEmployeeCardGroups is failed");
		}
	}

	/**
	 * пробуем удалить список сообщений
	 *
	 * @param array  $message_map_list
	 * @param string $conversation_map
	 * @param int    $user_id
	 * @param bool   $is_force_delete
	 *
	 * @throws ReturnFatalException
	 * @throws cs_Action_TimeIsOver
	 * @throws \cs_UserIsNotMember
	 * @throws paramException
	 * @throws \returnException
	 */
	public static function tryDeleteMessageList(array $message_map_list, string $conversation_map, int $user_id, bool $is_force_delete = false):void {

		$ar_post = [
			"message_map_list" => $message_map_list,
			"conversation_map" => $conversation_map,
			"is_force_delete"  => $is_force_delete === true ? 1 : 0, // не обращаем внимание на свою роль в группе и timeout для сообщения?
		];
		[$status, $response] = self::doCall("conversations.tryDeleteMessageList", $ar_post, $user_id);

		if ($status != "ok") {

			// работаем над кодом ошибки в ответе
			if (isset($response["error_code"])) {

				self::_workWithErrorCodeForDeleteMessageList($response["error_code"]);
				return;
			}

			throw new ReturnFatalException(__METHOD__ . ": request to socket method conversations.tryDeleteMessageList is failed");
		}
	}

	/**
	 * работаем с кодом ошибки при удалении списка сообщений в диалоге
	 *
	 * @param int $error_code
	 *
	 * @throws ReturnFatalException
	 * @throws cs_Action_TimeIsOver
	 * @throws \cs_UserIsNotMember
	 * @throws paramException
	 */
	protected static function _workWithErrorCodeForDeleteMessageList(int $error_code):void {

		switch ($error_code) {

			case 10004: // сообщение заархивировано
				return;
			case 10011: // пользователь не участник диалога
				throw new \cs_UserIsNotMember();
			case 10050: // сообщение из тех что недоступны для удаления
				throw new ParamException("incorrect message type for delete");
			case 10051: // увы, время для удаления истекло
				throw new cs_Action_TimeIsOver();
			case 10101: // пользователь не является админом группы и отправителем сообщения
				return;
		}

		throw new ReturnFatalException("Socket request status != ok.");
	}

	/**
	 * ищем ссылки в тексте
	 *
	 * @throws \returnException
	 */
	public static function getLinkListFromText(string $text, int $opposite_user_id, int $creator_user_id, int $entity_type, int $entity_id):array {

		// формируем user list для отправки ws
		$user_list[] = [
			"user_id" => $creator_user_id,
		];
		$user_list[] = [
			"user_id" => $opposite_user_id,
		];

		// делаем сокет запрос на php_conversation, чтобы получить linked list
		$params = [
			"text"             => $text,
			"opposite_user_id" => $opposite_user_id,
			"creator_user_id"  => $creator_user_id,
			"user_list"        => $user_list,
			"entity_type"      => $entity_type,
			"entity_id"        => $entity_id,
		];
		[$status, $response] = self::doCall("conversations.getLinkListFromText", $params);

		// если произошла ошибка
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}

		return $response["link_list"];
	}

	/**
	 * получить ключ диалога по его типу
	 *
	 * @param int $user_id
	 *
	 * @return string
	 * @throws paramException
	 * @throws \returnException
	 */
	public static function getPublicHeroesConversationMap(int $user_id):string {

		$params = [
			"user_id" => $user_id,
		];
		[$status, $response] = self::doCall("conversations.getPublicHeroesConversationMap", $params);

		// если произошла ошибка
		if ($status != "ok") {

			// работаем над кодом ошибки в ответе
			if (isset($response["error_code"])) {
				self::_workWithErrorCodeForGetPublicHeroesConversationMap($response["error_code"]);
			}

			throw new ReturnFatalException(__METHOD__ . ": request to socket method conversations.getPublicHeroesConversationMap is failed");
		}

		return $response["conversation_map"];
	}

	/**
	 * Получаем нужную информацию по чатам для карточки
	 *
	 * @throws ReturnFatalException
	 * @throws \returnException
	 * @throws ParamException
	 */
	public static function getConversationCardList(int $executor_user_id, int $opponent_user_id):array {

		$params = [
			"executor_user_id" => $executor_user_id,
			"opponent_user_id" => $opponent_user_id,
		];
		[$status, $response] = self::doCall("conversations.getConversationCardList", $params);

		// если произошла ошибка
		if ($status != "ok") {

			// работаем над кодом ошибки в ответе
			if (isset($response["error_code"])) {
				self::_workWithErrorCodeForGetPublicHeroesConversationMap($response["error_code"]);
			}

			throw new ReturnFatalException(__METHOD__ . ": request to socket method conversations.getConversationCardList is failed");
		}

		return [$response["single_conversation"], $response["heroes_conversation"]];
	}

	/**
	 * работаем с кодом ошибки при получении чата heroes
	 *
	 * @throws paramException
	 * @throws \returnException
	 */
	protected static function _workWithErrorCodeForGetPublicHeroesConversationMap(int $error_code):void {

		switch ($error_code) {

			case 10023: // не нашли чат heroes для пользователя
				throw new ParamException("public conversation not found");
		}

		throw new ReturnFatalException("Socket request getPublicHeroesConversationMap status != ok.");
	}

	/**
	 * создаем дефолтные группы компании
	 *
	 * @throws \returnException
	 */
	public static function createDefaultGroups(int $creator_user_id, Struct_File_Default $default_file_key_list, string $locale):void {

		$ar_post = [
			"default_file_key_list" => $default_file_key_list->convertToArray(),
			"locale"                => $locale,
		];

		[$status] = self::doCall("groups.createCompanyDefaultGroups", $ar_post, $creator_user_id);

		// если вернулась ошибка
		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method groups.createCompanyDefaultGroups is failed");
		}
	}

	/**
	 * Добавить пользователя в дефолтные группы компании
	 *
	 * @throws ReturnFatalException
	 */
	public static function addToDefaultGroups(int $user_id, bool $is_owner, int $role, string $locale):void {

		$ar_post = [
			"is_owner" => $is_owner ? 1 : 0,
			"role"     => $role,
			"locale"   => $locale,
		];

		[$status] = self::doCall("groups.addToDefaultGroups", $ar_post, $user_id);

		// если вернулась ошибка
		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method groups.addToDefaultGroups is failed");
		}
	}

	/**
	 * получаем дефолтные группы компании
	 *
	 * @return string[]
	 *
	 * @throws \returnException
	 */
	public static function getDefaultGroups():array {

		[$status, $response] = self::doCall("groups.getCompanyDefaultGroups", []);
		if ($status != "ok") {
			throw new ReturnFatalException("module conversation socket response status is not ok: " . ($response["message"] ?? "unknown reason"));
		}

		return $response;
	}

	/**
	 * получаем дефолтные группы компании
	 *
	 * @return string[]
	 *
	 * @throws \returnException
	 */
	public static function getHiringGroups():array {

		[$status, $response] = self::doCall("groups.getCompanyHiringGroups", []);
		if ($status != "ok") {
			throw new ReturnFatalException("module conversation socket response status is not ok: " . ($response["message"] ?? "unknown reason"));
		}

		return $response;
	}

	/**
	 * добавляем достижение в диалог
	 *
	 * @throws \blockException
	 * @throws paramException
	 * @throws \returnException
	 */
	public static function addAchievementToConversation(int $sender_user_id, int $receiver_user_id, int $achievement_id, string $header_text, string $description_text):string {

		$message_text = Type_User_Card_Achievement::initConversationMessage($header_text, $description_text);

		$ar_post = [
			"achievement_id"   => $achievement_id,
			"message_text"     => $message_text,
			"receiver_user_id" => $receiver_user_id,
		];

		[$status, $response] = self::doCall("conversations.addAchievement", $ar_post, $sender_user_id);

		if ($status != "ok") {

			// если не задан код ошибки
			if (!isset($response["error_code"])) {
				throw new ReturnFatalException(__METHOD__ . ": request to socket method conversations.addAchievement is failed");
			}

			// работаем над кодом ошибки в ответе
			self::_workWithErrorCodeForAddMessageFromEmployeeCard($response["error_code"]);
		}

		return $response["message_map"];
	}

	/**
	 * Очистить все блокировки, если передан user_id, то очистить все блокировки, только для пользователя
	 *
	 * @throws \returnException
	 */
	public static function clearAllBlocks(int $user_id = 0):void {

		$params = [];
		if ($user_id > 0) {
			$params["user_id"] = $user_id;
		}

		[$status] = self::doCall("antispam.clearAll", $params);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method antispam.clearAll is failed");
		}
	}

	/**
	 * Получить список управляемых диалогов по массиву ключей
	 *
	 * @param int   $user_id
	 * @param array $conversation_map_list
	 *
	 * @return array
	 *
	 * @throws \returnException
	 */
	#[ArrayShape(["can_send_invite_conversation_map_list" => "mixed", "cannot_send_invite_conversation_map_list" => "mixed", "leaved_member_conversation_map_list" => "mixed", "kicked_member_conversation_map_list" => "mixed", "not_exist_in_company_conversation_map_list" => "mixed", "not_group_conversation_map_list" => "mixed"])]
	public static function getManagedByMapList(int $user_id, array $conversation_map_list):array {

		[$status, $response] = self::doCall("conversations.getManagedByMapList", [
			"user_id"               => $user_id,
			"conversation_map_list" => $conversation_map_list,
		]);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method conversations.getManagedByMapList is failed");
		}

		return [
			"can_send_invite_conversation_map_list"      => $response["can_send_invite_conversation_map_list"],
			"cannot_send_invite_conversation_map_list"   => $response["cannot_send_invite_conversation_map_list"],
			"leaved_member_conversation_map_list"        => $response["leaved_member_conversation_map_list"],
			"kicked_member_conversation_map_list"        => $response["kicked_member_conversation_map_list"],
			"not_exist_in_company_conversation_map_list" => $response["not_exist_in_company_conversation_map_list"],
			"not_group_conversation_map_list"            => $response["not_group_conversation_map_list"],
		];
	}

	/**
	 * провереям есть ли доступ к диалогам из списка
	 *
	 * @throws \parseException|cs_IncorrectConversationKeyListToJoin
	 */
	public static function isUserCanSendInvitesInGroups(array $conversation_key_list_to_join, int $user_id):array {

		$ar_post = [
			"conversation_key_list_to_join" => $conversation_key_list_to_join,
		];

		[$status, $response] = self::doCall("conversations.isUserCanSendInvitesInGroups", $ar_post, $user_id);

		if ($status != "ok") {

			if ($response["error_code"] === 10019) {
				throw new cs_IncorrectConversationKeyListToJoin();
			} elseif ($response["error_code"] === 10120) {
				throw new ParseFatalException($response["message"]);
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response;
	}

	/**
	 * получаем данные для создания треда заявки
	 *
	 * @return array
	 *
	 * @throws \parseException
	 */
	#[ArrayShape(["conversation_map" => "string", "conversation_type" => "int", "users" => "array", "user_mute_info" => "array", "user_clear_info" => "array"])]
	public static function getDataForCreateThreadOnHireRequest():array {

		[$status, $response] = self::doCall("conversations.getConversationDataForCreateThreadInHireRequest", []);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return [
			"conversation_map"  => (string) $response["conversation_map"],
			"conversation_type" => (int) $response["conversation_type"],
			"users"             => (array) $response["users"],
			"user_mute_info"    => (array) $response["user_mute_info"],
			"user_clear_info"   => (array) $response["user_clear_info"],
		];
	}

	/**
	 * отправляем сообщение о увольнении в диалог
	 *
	 * @param int $user_id
	 * @param int $dismissal_request_id
	 * @param int $dismissal_user_id
	 *
	 * @return array
	 *
	 * @throws \parseException
	 */
	public static function addDismissalRequestMessage(int $user_id, int $dismissal_request_id, int $dismissal_user_id):array {

		$ar_post = [
			"dismissal_request_id" => $dismissal_request_id,
			"dismissal_user_id"    => $dismissal_user_id,
		];
		[$status, $response] = self::doCall("conversations.addDismissalRequestMessage", $ar_post, $user_id);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return [$response["conversation_map"], $response["message_map"], $response["thread_map"]];
	}

	/**
	 * получить информацию о нескольких диалогах
	 *
	 * @return Struct_Socket_Conversation_Info[]
	 *
	 * @throws \returnException
	 */
	public static function getConversationInfoList(array $conversation_key_list, int $is_group_by_key = 0):array {

		$params = [
			"conversation_key_list" => $conversation_key_list,
		];
		[$status, $response] = self::doCall("conversations.getConversationInfoList", $params);

		// если произошла ошибка
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}

		$output = [];
		foreach ($response["conversation_info_list"] as $conversation_info) {

			$info = new Struct_Socket_Conversation_Info(
				$conversation_info["conversation_key"],
				$conversation_info["name"],
				$conversation_info["member_count"],
				$conversation_info["avatar_file_map"],
			);
			if ($is_group_by_key) {
				$output[$conversation_info["conversation_key"]] = $info;
			} else {
				$output[] = $info;
			}
		}
		return $output;
	}

	/**
	 * Получаем список id участников чата найм и увольнения
	 *
	 * @throws \parseException
	 */
	public static function getHiringConversationUserIdList():array {

		[$status, $response] = self::doCall("conversations.getHiringConversationUserIdList", []);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return $response;
	}

	/**
	 * Выполняет запрос на удаленной вызов скрипта обновления.
	 *
	 * @throws \returnException
	 *
	 * @noinspection DuplicatedCode
	 */
	public static function execCompanyUpdateScript(string $script_name, array $script_data, int $flag_mask):array {

		$params["script_data"] = $script_data;
		$params["script_name"] = $script_name;
		$params["flag_mask"]   = $flag_mask;

		// отправим запрос на удаление из списка
		[$status, $response] = self::doCall("system.execCompanyUpdateScript", $params);

		if ($status != "ok") {
			throw new ReturnFatalException($response["message"]);
		}

		return [$response["script_log"], $response["error_log"]];
	}

	/**
	 * вызовем метод для удаления
	 *
	 * @throws \returnException
	 */
	public static function clearAfterExitUser(string $method, array $ar_post, int $user_id):bool {

		[$status, $response] = self::doCall($method, $ar_post, $user_id);

		// если вернулась ошибка при удалении
		if ($status != "ok") {

			throw new ReturnFatalException(__METHOD__ . ": request to socket method {$method} is failed");
		}

		return $response["is_complete"];
	}

	/**
	 * вызовем метод для проверки удаления
	 *
	 * @throws \returnException
	 */
	public static function checkAfterExitUser(string $method, array $ar_post, int $user_id):bool {

		[$status, $response] = self::doCall($method, $ar_post, $user_id);

		// если вернулась ошибка при удалении
		if ($status != "ok") {

			throw new ReturnFatalException(__METHOD__ . ": request to socket method {$method} is failed");
		}

		return $response["is_cleared"];
	}

	/**
	 * получить информацию по группам пользовательского бота, в которых он состоит
	 *
	 * @throws ReturnFatalException
	 * @throws \cs_DecryptHasFailed
	 */
	public static function getUserbotGroupInfoList(array $conversation_map_list):array {

		$params = [
			"conversation_map_list" => $conversation_map_list,
		];
		[$status, $response] = self::doCall("groups.getUserbotGroupInfoList", $params);

		// если произошла ошибка
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}

		$output = [];
		foreach ($response["group_info_list"] as $group_info) {

			$avatar_file_key = "";
			if (!isEmptyString($group_info["avatar_file_map"])) {
				$avatar_file_key = \CompassApp\Pack\File::doEncrypt($group_info["avatar_file_map"]);
			}

			$output[] = [
				"conversation_key" => $group_info["conversation_key"],
				"group_name"       => $group_info["name"],
				"avatar_file_key"  => $avatar_file_key,
			];
		}
		return $output;
	}

	/**
	 * добавляем ботов в группу
	 *
	 * @throws Domain_Conversation_Exception_User_NotMember
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws ReturnFatalException
	 */
	public static function addUserbotToGroup(array $userbot_id_list_by_user_id, array $first_add_userbot_id_list, string $conversation_key, int $developer_user_id):void {

		$params = [
			"userbot_id_list_by_user_id" => $userbot_id_list_by_user_id,
			"first_add_userbot_id_list"  => $first_add_userbot_id_list,
			"conversation_key"           => $conversation_key,
		];
		[$status, $response] = self::doCall("groups.addUserbotToGroup", $params, $developer_user_id);

		// если произошла ошибка
		if ($status != "ok") {

			switch ($response["error_code"]) {

				case 2418002:
					throw new Domain_Conversation_Exception_User_NotMember("user is not member of conversation");

				case 2418003:
					throw new ParamException("conversation is not group");
			}

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * убираем бота из группы
	 *
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public static function removeFromGroup(array $userbot_id_by_user_id, string $conversation_key):void {

		$params = [
			"userbot_id_by_user_id" => $userbot_id_by_user_id,
			"conversation_key"      => $conversation_key,
		];
		[$status, $response] = self::doCall("groups.removeUserbotFromGroup", $params);

		// если произошла ошибка
		if ($status != "ok") {

			switch ($response["error_code"]) {

				case 2418003:
					throw new ParamException("conversation is not group");
			}

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * отправляем сообщение в диалог
	 *
	 * @throws ReturnFatalException
	 */
	public static function sendMessageToConversation(int $sender_user_id, string $conversation_key, string $text):void {

		$params = [
			"sender_user_id"   => $sender_user_id,
			"conversation_key" => $conversation_key,
			"text"             => $text,
		];
		[$status, $response] = self::doCall("conversations.sendMessage", $params);

		// если произошла ошибка
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * отправляем сообщение от бота в группу
	 *
	 * @throws ReturnFatalException
	 */
	public static function sendUserbotMessageToGroup(int $userbot_user_id, string $conversation_key, string $message_text):void {

		$params = [
			"userbot_user_id"  => $userbot_user_id,
			"conversation_key" => $conversation_key,
			"text"             => $message_text,
		];
		[$status, $response] = self::doCall("userbot.sendMessageToGroup", $params);

		// если произошла ошибка
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * добавляем реакцию к сообщению
	 *
	 * @throws ReturnFatalException
	 */
	public static function userbotAddReaction(int $userbot_user_id, string $message_key, string $reaction):void {

		$params = [
			"userbot_user_id" => $userbot_user_id,
			"message_key"     => $message_key,
			"reaction"        => $reaction,
		];
		[$status, $response] = self::doCall("userbot.addReaction", $params);

		// если произошла ошибка
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * убираем реакцию у сообщения
	 *
	 * @throws ReturnFatalException
	 */
	public static function userbotRemoveReaction(int $userbot_user_id, string $message_key, string $reaction):void {

		$params = [
			"userbot_user_id" => $userbot_user_id,
			"message_key"     => $message_key,
			"reaction"        => $reaction,
		];
		[$status, $response] = self::doCall("userbot.removeReaction", $params);

		// если произошла ошибка
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * Установить статус компании в конфиге
	 *
	 * @throws \returnException
	 */
	public static function setCompanyStatus(int $status):void {

		$method = "system.setCompanyStatus";

		[$status] = self::doCall($method, ["status" => $status]);

		// если вернулась ошибка при запросе
		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method {$method} is failed");
		}
	}

	/**
	 * отправляем сообщение пользователю
	 *
	 * @throws \parseException
	 * @throws ReturnFatalException
	 */
	public static function sendMessageToUser(int $user_id, int $receiver_user_id, string $text):void {

		assertTestServer();

		$ar_post = [
			"user_id"          => $user_id,
			"opponent_user_id" => $receiver_user_id,
			"text"             => $text,
		];
		[$status, $response] = self::doCall("conversations.sendMessageToUser", $ar_post);

		// если произошла ошибка
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * Возвращает список мап сообщений, которые связаны с указанными заявками на наем.
	 *
	 * @param array $hiring_request_id_list список ид заявок
	 * @param int   $date_from              дата, с которой начинаем разбирать сообщения
	 * @param int   $date_to                дата, по которую разбираем сообщения
	 *
	 * @return array <hiring_request_id => conversation_map>
	 */
	public static function getHiringRequestMessageMaps(array $hiring_request_id_list, int $date_from, int $date_to):array {

		$ar_post = [
			"hiring_request_id_list" => $hiring_request_id_list,
			"date_from"              => $date_from,
			"date_to"                => $date_to,
		];

		[$status, $response] = self::doCall("conversations.getHiringRequestMessageMaps", $ar_post);

		// если произошла ошибка
		if ($status !== "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}

		return $response["hiring_request_conversation_map_rel"];
	}

	/**
	 * Возвращает список мап сообщений, которые связаны с указанными заявками на увольнение.
	 *
	 * @param array $dismissal_request_id_list список ид заявок
	 * @param int   $date_from                 дата, с которой начинаем разбирать сообщения
	 * @param int   $date_to                   дата, по которую разбираем сообщения
	 *
	 * @return array <dismissal_request_id => conversation_map>
	 */
	public static function getDismissalRequestMessageMaps(array $dismissal_request_id_list, int $date_from, int $date_to):array {

		$ar_post = [
			"dismissal_request_id_list" => $dismissal_request_id_list,
			"date_from"                 => $date_from,
			"date_to"                   => $date_to,
		];

		[$status, $response] = self::doCall("conversations.getDismissalRequestMessageMaps", $ar_post);

		// если произошла ошибка
		if ($status !== "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}

		return $response["dismissal_request_conversation_map_rel"];
	}

	/**
	 * отправляем сообщение-Напоминание в чат
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function sendRemindMessage(int $remind_id, string $recipient_id, string $comment):void {

		$ar_post = [
			"remind_id"   => $remind_id,
			"message_map" => $recipient_id,
			"comment"     => $comment,
		];

		[$status, $response] = self::doCall("conversations.sendRemindMessage", $ar_post);

		// если произошла ошибка
		if ($status !== "ok") {

			// работаем над кодом ошибки в ответе
			self::_handleErrorCodeForSendRemindMessage($response["error_code"]);

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * ошибки при отправке сообщения-Напоминания
	 *
	 * @throws ParseFatalException
	 */
	protected static function _handleErrorCodeForSendRemindMessage(int $error_code):void {

		switch ($error_code) {

			case 2418005:
				throw new ParseFatalException("passed comment text is too long");
		}
	}

	/**
	 * актуализируем данные Напоминания в сообщении-оригинале
	 *
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	public static function actualizeTestRemindForMessage(string $recipient_id):void {

		assertTestServer();

		$ar_post = [
			"message_map" => $recipient_id,
		];

		[$status, $response] = self::doCall("conversations.actualizeTestRemindForMessage", $ar_post);

		// если произошла ошибка
		if ($status !== "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * Пытается выполнить переиндексацию.
	 */
	public static function tryReindex():void {

		[$status, $response] = self::doCall("search.tryReindex", []);

		// если произошла ошибка
		if ($status !== "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * Создаем чат "Спасибо"
	 *
	 * @param int    $creator_user_id
	 * @param string $respect_conversation_avatar_file_key
	 *
	 * @return int
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function createRespectConversation(int $creator_user_id, string $respect_conversation_avatar_file_key):int {

		// формируем параметры для запроса
		$params = [
			"creator_user_id"                      => $creator_user_id,
			"respect_conversation_avatar_file_key" => $respect_conversation_avatar_file_key,
		];
		[$status, $response] = self::doCall("groups.createRespectConversation", $params);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["is_created"];
	}

	/**
	 * Добавляем участников пространства в чат "Спасибо"
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function addMembersToRespectConversation():void {

		// формируем параметры для запроса
		$params = [];
		[$status, $response] = self::doCall("groups.addMembersToRespectConversation", $params);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Метод для проверки можем ли звонить пользователю
	 *
	 * @param int $user_id
	 * @param int $opponent_user_id
	 * @param int $method_version
	 *
	 * @return string
	 * @throws CompanyIsHibernatedException
	 * @throws CompanyNotServedException
	 * @throws Gateway_Socket_Exception_Conversation_GuestInitiator
	 * @throws Gateway_Socket_Exception_Conversation_NotAllowed
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function checkIsAllowedForCall(int $user_id, int $opponent_user_id, int $method_version):string {

		$request = [
			"user_id"          => $user_id,
			"opponent_user_id" => $opponent_user_id,
			"method_version"   => $method_version,
		];

		[$status, $response] = self::doCall("conversations.checkIsAllowedForCall", $request, $user_id);

		if ($status !== "ok") {

			// сокет-запрос вернул код ошибки?
			if (!isset($response["error_code"])) {
				throw new ReturnFatalException(__CLASS__ . ": request return call not \"ok\"");
			}

			throw match ($response["error_code"]) {
				10022 => new Gateway_Socket_Exception_Conversation_NotAllowed("conversation not allowed"),
				10024, 10027, 10025 => new Gateway_Socket_Exception_Conversation_GuestInitiator("guest is initiator"),
				default => throw new ReturnFatalException("unexpected error code")
			};
		}

		return $response["conversation_map"];
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("conversation");

		try {
			return self::_doCall($url, $method, $params, $user_id);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 404) {
				throw new \BaseFrame\Exception\Request\CompanyNotServedException("company is not served");
			}
			if ($e->getHttpStatusCode() == 503) {
				throw new \BaseFrame\Exception\Request\CompanyIsHibernatedException("company is hibernated");
			}
			throw $e;
		}
	}
}
