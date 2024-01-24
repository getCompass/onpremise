<?php

namespace Compass\Conversation;

use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс для работы с сингл инвайтами
 */
class Type_Invite_Single extends Type_Invite_Default {

	// создать приглашение
	#[ArrayShape(["invite_map" => "string", "single_conversation_map" => "string", "created_at" => "int", "updated_at" => "int", "user_id" => "int", "sender_user_id" => "int", "conversation_name" => "mixed", "avatar_file_map" => "mixed", "group_conversation_map" => "mixed"])]
	public static function create(int $sender_user_id, int $user_id, string $single_conversation_map, array $group_meta_row, bool $is_need_increment_count):array {

		$created_at = time();
		$meta_id    = Type_Autoincrement_Main::getNextId(Type_Autoincrement_Main::INVITE);

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// инкрементим счетчик если нужно, и пытаемся создать инвайт
		try {

			if ($is_need_increment_count) {
				self::_doIncrementCountSenderActiveInviteIfNeed($group_meta_row["conversation_map"], $sender_user_id);
			}
			$invite_row = self::_tryCreateInviteIfPossible($meta_id, $created_at, $sender_user_id, $user_id, $single_conversation_map, $group_meta_row);
		} catch (cs_ErrorSocketRequest|ReturnFatalException|cs_InviteActiveSendLimitIsExceeded|cs_InviteIsDuplicated $e) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new $e();
		}

		Gateway_Db_CompanyConversation_Main::commitTransaction();
		return $invite_row;
	}

	// пытаемся создать инвайт, если возможно
	#[ArrayShape(["invite_map" => "string", "single_conversation_map" => "string", "created_at" => "int", "updated_at" => "int", "user_id" => "int", "sender_user_id" => "int", "conversation_name" => "mixed", "avatar_file_map" => "mixed", "group_conversation_map" => "mixed"])]
	protected static function _tryCreateInviteIfPossible(int $meta_id, int $created_at, int $sender_user_id, int $user_id, string $single_conversation_map, array $group_meta_row):array {

		// получаем мету и формируем invite_row
		$shard_id = \CompassApp\Pack\Invite::getShardIdByTime($created_at);
		Gateway_Db_CompanyConversation_InviteList::insert($meta_id, SINGLE_INVITE_TO_GROUP, $created_at, $shard_id);
		$invite_map = \CompassApp\Pack\Invite::doPack($shard_id, $meta_id, SINGLE_INVITE_TO_GROUP);

		// создаем запись во всех базах
		$invite_row = Gateway_Db_CompanyConversation_InviteGroupViaSingle::create($user_id, $sender_user_id, $group_meta_row, $single_conversation_map, $invite_map, $created_at);
		Gateway_Db_CompanyConversation_ConversationInviteList::insert(
			$user_id,
			$sender_user_id,
			$invite_map,
			$created_at,
			$invite_row["status"],
			$group_meta_row["conversation_map"]
		);
		self::_createInviteInUserInvite($invite_row);
		return $invite_row;
	}

	// пометить приглашение активным
	public static function setActive(array $invite_row, int $user_id, array $meta_row):void {

		// если статус инвайта и так active
		if ($invite_row["status"] == Type_Invite_Handler::STATUS_ACTIVE) {
			return;
		}

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// инкрементим счетчик активных инвайтов и апдейтим все записи инвайтов
		try {

			self::_doIncrementCountSenderActiveInviteIfNeed($invite_row["conversation_map"], $invite_row["sender_user_id"]);
			self::_updateInvite($invite_row, $user_id, $meta_row, Type_Invite_Handler::STATUS_ACTIVE);
		} catch (cs_InviteStatusIsNotExpected | \ErrorException | cs_InviteActiveSendLimitIsExceeded $e) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new $e();
		}

		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}

	// пометить приглашение неактивным
	public static function setInactive(array $invite_row, int $user_id, array $meta_row, int $inactive_reason, bool $is_remove_user):void {

		// если статус инвайта и так inactive
		$status_inactive = Type_Invite_Handler::STATUS_INACTIVE;
		if ($invite_row["status"] == $status_inactive) {
			return;
		}

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// декрементим счетчик активных инвайтов и апдейтим все записи инвайтов
		try {

			self::_doDecrementCountSenderActiveInviteIfNeed($invite_row["conversation_map"], $invite_row["sender_user_id"], $invite_row["status"]);
			self::_updateInvite($invite_row, $user_id, $meta_row, $status_inactive, $inactive_reason, $is_remove_user);
		} catch (cs_InviteStatusIsNotExpected | \ErrorException | cs_InviteActiveSendLimitIsExceeded $e) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new $e();
		}

		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}

	// пометить приглашение принятым
	public static function setAccepted(array $invite_row, int $user_id, array $meta_row):void {

		// если статус инвайта и так accepted
		if ($invite_row["status"] == Type_Invite_Handler::STATUS_ACCEPTED) {
			return;
		}

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// декрементим счетчик активных инвайтов и апдейтим все записи инвайтов
		try {

			self::_doDecrementCountSenderActiveInviteIfNeed($invite_row["group_conversation_map"], $invite_row["sender_user_id"], $invite_row["status"]);
			self::_updateInvite($invite_row, $user_id, $meta_row, Type_Invite_Handler::STATUS_ACCEPTED);
		} catch (cs_InviteStatusIsNotExpected | \ErrorException | cs_InviteActiveSendLimitIsExceeded $e) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new $e();
		}

		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}

	// пометить приглашение отклоненным
	public static function setDeclined(string $conversation_map, array $invite_row, int $user_id, array $meta_row):void {

		// если статус инвайта и так declined
		if ($invite_row["status"] == Type_Invite_Handler::STATUS_DECLINED) {
			return;
		}

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// декрементим счетчик активных инвайтов и апдейтим все записи инвайтов
		try {

			self::_doDecrementCountSenderActiveInviteIfNeed($conversation_map, $invite_row["sender_user_id"], $invite_row["status"]);
			self::_updateInvite($invite_row, $user_id, $meta_row, Type_Invite_Handler::STATUS_DECLINED);
		} catch (cs_InviteStatusIsNotExpected | \ErrorException | cs_InviteActiveSendLimitIsExceeded $e) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new $e();
		}

		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}

	// пометить приглашение отозванным
	public static function setRevoked(array $invite_row, int $user_id, array $meta_row):void {

		// если статус инвайта и так revoked
		if ($invite_row["status"] == Type_Invite_Handler::STATUS_REVOKED) {
			return;
		}

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// апдейтим все записи инвайтов и декрементим счетчик активных инвайтов
		try {

			self::_doDecrementCountSenderActiveInviteIfNeed($invite_row["conversation_map"], $invite_row["sender_user_id"], $invite_row["status"]);
			self::_updateInvite($invite_row, $user_id, $meta_row, Type_Invite_Handler::STATUS_REVOKED);
		} catch (cs_InviteStatusIsNotExpected | \ErrorException | cs_InviteActiveSendLimitIsExceeded $e) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new $e();
		}

		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}

	// получает количество активных инвайтов для отправителя
	public static function getCountSenderActiveInvite(int $sender_user_id, string $conversation_map):int {

		$active_invite_row = Gateway_Db_CompanyConversation_UserDynamic::get($conversation_map, $sender_user_id);

		return $active_invite_row["count_sender_active_invite"] ?? 0;
	}

	// получает количество активных инвайтов для отправителя в списке групп
	public static function getAllCountSenderActiveInviteListForGroupList(int $sender_user_id, array $conversation_map_list):array {

		// получаем информацию о диалогах из базы
		return self::_getCountSenderActiveInviteList($sender_user_id, $conversation_map_list);
	}

	// получаем записи из базы
	protected static function _getCountSenderActiveInviteList(int $sender_user_id, array $conversation_map_list):array {

		$count_sender_active_invite_list = Gateway_Db_CompanyConversation_UserDynamic::getAll($sender_user_id, $conversation_map_list);

		$output = [];
		foreach ($count_sender_active_invite_list as $item) {
			$output[$item["conversation_map"]] = $item;
		}

		return $output;
	}

	// записываем инвайт в таблицу
	public static function insertInviteDataForUserInviteRel(array $invite_row):void {

		Gateway_Db_CompanyConversation_UserInviteRel::insert(
			$invite_row["user_id"],
			$invite_row["sender_user_id"],
			$invite_row["invite_map"],
			$invite_row["created_at"],
			$invite_row["status"],
			$invite_row["group_conversation_map"]
		);
	}

	// получить информацию о приглашении
	public static function get(string $invite_map):array {

		return Gateway_Db_CompanyConversation_InviteGroupViaSingle::getOne($invite_map);
	}

	// получаем записи
	public static function getInviteList(array $invite_map_list, int $user_id):array {

		// получаем новые приглашения
		$invite_list = Type_Invite_Single::getAll($invite_map_list);

		// фильтруем список новых приглашений
		$output = [];
		foreach ($invite_list as $item) {

			// проверяем имеет ли пользователь доступ к инвайту
			if ($user_id != $item["user_id"] && $user_id != $item["sender_user_id"]) {
				continue;
			}
			$output[$item["group_conversation_map"]][] = $item;
		}
		return $output;
	}

	// получаем все записи
	public static function getAll(array $invite_map_list):array {

		// получаем список инвайтов из базы
		return Gateway_Db_CompanyConversation_InviteGroupViaSingle::getAll($invite_map_list);
	}

	// обновляем инвайт
	public static function updateInviteDataForUserInvite(int $user_id, string $invite_map, int $expected_status, int $new_status, int $updated_at):void {

		Gateway_Db_CompanyConversation_UserInviteRel::set($user_id, $invite_map, $expected_status, [
			"status"     => $new_status,
			"updated_at" => $updated_at,
		]);
	}

	// получаем список инвайтов по id пользователя и статусу инвайта в cloud_user_invite
	public static function getInviteListByUserIdAndStatusInUserInvite(int $user_id, int $status, int $limit, int $offset):array {

		return Gateway_Db_CompanyConversation_UserInviteRel::getByUserIdAndStatus($user_id, $status, $limit, $offset);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// апдейтим все записи инвайтов
	protected static function _updateInvite(array $invite_row, int $user_id, array $meta_row, int $new_status, int $inactive_reason = 0, bool $is_remove_user = false):void {

		$updated_at = time();

		// обновляем запись с инвайтом на invite dpc
		$update_count = self::_updateInviteDataInInvite($invite_row["invite_map"], $invite_row["status"], $new_status, $updated_at, $meta_row, $inactive_reason);

		// если не одна строка не была обновлена и ситуация не с увольнением сотрудника
		if (!$is_remove_user && $update_count < 1) {
			throw new cs_InviteStatusIsNotExpected();
		}

		// обновляем в других базах
		self::_updateInviteDataInConversation($invite_row["invite_map"], $invite_row["status"], $new_status, $updated_at);
		self::_updateInviteDataInUserInvite($user_id, $invite_row["invite_map"], $invite_row["status"], $new_status, $updated_at);
	}

	// обновляем запись с инвайтом в cloud_invite
	protected static function _updateInviteDataInInvite(string $invite_map, int $expected_status, int $new_status, int $updated_at, array $meta_row, int $inactive_reason):int {

		return Gateway_Db_CompanyConversation_InviteGroupViaSingle::set($invite_map, $expected_status, [
			"inactive_reason"   => $inactive_reason,
			"status"            => $new_status,
			"updated_at"        => $updated_at,
			"conversation_name" => $meta_row["conversation_name"],
			"avatar_file_map"   => $meta_row["avatar_file_map"],
		]);
	}

	// обновляем запись с инвайтом в company_conversation
	protected static function _updateInviteDataInConversation(string $invite_map, int $expected_status, int $new_status, int $updated_at):void {

		Gateway_Db_CompanyConversation_ConversationInviteList::set($invite_map, $expected_status, [
			"status"     => $new_status,
			"updated_at" => $updated_at,
		]);
	}

	// инкрементим счетчик активных инвайтов
	protected static function _doIncrementCountSenderActiveInviteIfNeed(string $group_conversation_map, int $sender_user_id):void {

		// получаем запись на обновление, если запись не существует - создаем
		$conversation_user_dynamic_row = Gateway_Db_CompanyConversation_UserDynamic::getForUpdate($group_conversation_map, $sender_user_id);
		if (!isset($conversation_user_dynamic_row["user_id"])) {

			// откатываем транзакцию, создаем запись и начинаем новую транзакцию
			Gateway_Db_CompanyConversation_Main::rollback();
			Gateway_Db_CompanyConversation_UserDynamic::insert($group_conversation_map, $sender_user_id);
			Gateway_Db_CompanyConversation_Main::beginTransaction();
			$conversation_user_dynamic_row = Gateway_Db_CompanyConversation_UserDynamic::getForUpdate($group_conversation_map, $sender_user_id);
		}

		// бросаем ошибку если достигнут лимит
		if ($conversation_user_dynamic_row["count_sender_active_invite"] == Type_Invite_Handler::getSendActiveInviteLimit()) {
			throw new cs_InviteActiveSendLimitIsExceeded();
		}

		// обновляем запись в таблице, если запись не обновилась, значит достигнут лимит - бросаем исключение
		$update_count = Gateway_Db_CompanyConversation_UserDynamic::incActiveInviteCount($group_conversation_map, $sender_user_id);
		if ($update_count < 1) {
			throw new cs_InviteActiveSendLimitIsExceeded();
		}
	}

	// декриментим счетчик активных инвайтов
	protected static function _doDecrementCountSenderActiveInviteIfNeed(string $group_conversation_map, int $sender_user_id, int $actual_status = 0):void {

		// получаем запись на обновление, если запись не существует - создаем
		$conversation_user_dynamic_row = Gateway_Db_CompanyConversation_UserDynamic::getForUpdate($group_conversation_map, $sender_user_id);
		if (!isset($conversation_user_dynamic_row["user_id"])) {

			// откатываем транзакцию, создаем запись и начинаем новую транзакцию
			Gateway_Db_CompanyConversation_Main::rollback();
			Gateway_Db_CompanyConversation_UserDynamic::insert($group_conversation_map, $sender_user_id);
			Gateway_Db_CompanyConversation_Main::beginTransaction();
			$conversation_user_dynamic_row = Gateway_Db_CompanyConversation_UserDynamic::getForUpdate($group_conversation_map, $sender_user_id);
		}

		// если инвайт не активен - декремент счетчика не нужен
		if ($actual_status != Type_Invite_Handler::STATUS_ACTIVE) {
			return;
		}

		// учитываем отправленные инвайты до существования счетчика
		if ($conversation_user_dynamic_row["count_sender_active_invite"] < 1) {
			return;
		}

		// декриментим счетчик активных инвайтов
		Gateway_Db_CompanyConversation_UserDynamic::decActiveInviteCount($group_conversation_map, $sender_user_id);
	}
}