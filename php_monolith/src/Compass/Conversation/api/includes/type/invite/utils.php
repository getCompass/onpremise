<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * utils методы для работы с инвайтами
 */
class Type_Invite_Utils {

	// формируем инвайт в зависимости от структуры приглашения
	public static function prepareInvite(array $invite_row, array $left_menu_row, array $meta_row):array {

		// для новой логики
		$output = self::_makeInviteOutput($invite_row, $meta_row, $left_menu_row);
		$output = self::_addConversationDataToInviteToOutput($invite_row, $meta_row, $left_menu_row, $output);

		// добавляем поля, для соответствия типу invitation
		$output = self::_fillToInvitation($output, $invite_row, $meta_row);

		return $output;
	}

	// добавляем поля, общие для всех статусов инвайтов
	protected static function _makeInviteOutput(array $invite_row, array $meta_row, array $left_menu_row):array {

		$output["invite_map"] = $invite_row["invite_map"];
		$output["status"]     = $invite_row["status"];

		$output["type"]                     = self::getInviteType($invite_row["invite_map"]);
		$output["data"]["conversation_map"] = $meta_row["conversation_map"];
		$output["data"]["invited_user_id"]  = $invite_row["user_id"];

		// добавляем аватар если есть
		$output = self::_addAvatarIfExist($output, $meta_row, $left_menu_row, $invite_row);
		return $output;
	}

	// добавляем дополнительные поля в зависимости от статуса инвайта
	protected static function _addConversationDataToInviteToOutput(array $invite_row, array $meta_row, array $left_menu_row,
											   array $output):array {

		$output["data"]["conversation_type"] = $meta_row["type"];
		$output["data"]["member_status"]     = self::_getMemberStatus($left_menu_row);

		// если статус active, или accepted/inactive и пользователь член группы
		if (self::_isUserCanGetConversationMetaDataInInvite($invite_row, $meta_row)) {

			$output["data"]["conversation_name"] = $meta_row["conversation_name"];
			$output["data"]["is_member"]         = self::_isMember($left_menu_row, $meta_row) ? 1 : 0;
			$output["data"]["members_count"]     = count($meta_row["users"]);
			$output["data"]["users"]             = array_keys($meta_row["users"]);

			return $output;
		}

		// добавляем название диалога из левого меню для статусов declined, revoked и accepted/inactive, если пользователь не член группы
		if (count($left_menu_row) > 0) {
			$output["data"]["conversation_name"] = $left_menu_row["conversation_name"];
		} else {
			$output["data"]["conversation_name"] = $invite_row["conversation_name"];
		}

		return $output;
	}

	// добавляем поля, для соответствия типу invitation
	protected static function _fillToInvitation(array $output, array $invite_row, array $meta_row):array {

		// докидываем название в нужное поле
		$output["data"]["name"] = $output["data"]["conversation_name"];

		$output["data"]["invited"] = [
			"type"       => "user",
			"invited_id" => (string) $invite_row["user_id"],
		];

		$output["data"]["destination"] = [
			"type"           => "conversation",
			"destination_id" => (string) $meta_row["conversation_map"],
		];

		return $output;
	}

	// получаем статус инвайта
	public static function getStatusTitle(int $status):string {

		return Type_Invite_Handler::STATUS_TITLE[$status];
	}

	// сортируем список инвайтов по дате обновления и возвращаем список пользователей
	public static function sortInvitedUserList(array $invite_list):array {

		// исправляем порядок элементов после вызова внешнего сервиса
		usort($invite_list, function(array $a, array $b):int {

			// оператор <=> возвращает -1, 0 или 1 в зависимости от сравнения ( меньше, равно или больше )
			return $a["updated_at"] <=> $b["updated_at"];
		});

		return array_column($invite_list, "user_id");
	}

	// получаем тип приглашения и переводим его в формат string для фронтенда
	public static function getInviteType(string $invite_map):string {

		$type = \CompassApp\Pack\Invite::getType($invite_map);

		switch ($type) {

			case SINGLE_INVITE_TO_GROUP :
				return "single_invite_to_group";
			default:
				throw new ParseFatalException("Unknown invite type");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем поле member_status
	protected static function _getMemberStatus(array $left_menu_row):string {

		// если юзер никогда не был участником диалога
		if (!isset($left_menu_row["user_id"])) {
			return Type_Invite_Handler::MEMBER_STATUS_NOT_ATTACHED;
		}

		// если юзер является участником диалога
		if ($left_menu_row["is_leaved"] == 0) {
			return self::_getMemberStatusForMember($left_menu_row);
		}

		// для юзера покивнувшего диалог
		return self::_getMemberStatusForNotMember($left_menu_row);
	}

	// получаем поле member_status для юзера - участника диалога
	// @long - switch..case
	protected static function _getMemberStatusForMember(array $left_menu_row):string {

		switch ($left_menu_row["role"]) {

			case Type_Conversation_Meta_Users::ROLE_DEFAULT:
				return Type_Invite_Handler::MEMBER_STATUS_MEMBER;

			// если администратор диалога
			case Type_Conversation_Meta_Users::ROLE_ADMIN:
			case Type_Conversation_Meta_Users::ROLE_OWNER:

				return Type_Invite_Handler::MEMBER_STATUS_OWNER;

			default:
				throw new ParseFatalException("Unhandled left menu role {$left_menu_row["role"]} in " . __METHOD__);
		}
	}

	// получаем поле member_status для юзера покинувшего диалог
	protected static function _getMemberStatusForNotMember(array $left_menu_row):string {

		switch ($left_menu_row["leave_reason"]) {

			case Type_Conversation_LeftMenu::LEAVE_REASON_LEAVED:
				return Type_Invite_Handler::MEMBER_STATUS_LEAVED;
			case Type_Conversation_LeftMenu::LEAVE_REASON_KICKED:
				return Type_Invite_Handler::MEMBER_STATUS_KICKED;
			default:
				throw new ParseFatalException("Unhandled left menu leave_reason {$left_menu_row["leave_reason"]} in " . __METHOD__);
		}
	}

	// отдаем аватар если есть
	protected static function _addAvatarIfExist(array $output, array $meta_row, array $left_menu_row, array $invite_row):array {

		$output["data"]["avatar_file_map"] = "";

		// отдаем актуальный аватар группы если юзер участник группы и статус инвайта подходит (и аватар существует)
		if (self::_isUserCanGetConversationMetaDataInInvite($invite_row, $meta_row) && mb_strlen($meta_row["avatar_file_map"]) > 0) {

			$output["data"]["avatar_file_map"] = $meta_row["avatar_file_map"];
			return $output;
		}

		// отдаем аватар сохраненый в инвайте - если он там есть
		if (mb_strlen($invite_row["avatar_file_map"]) > 0) {
			$output["data"]["avatar_file_map"] = $invite_row["avatar_file_map"];
		}

		// у юзера нет записи в левом меню
		if (count($left_menu_row) < 1) {
			return $output;
		}

		// отдаем аватар из левого меню - если он там есть (он приоритетней)
		if (isset($left_menu_row["avatar_file_map"]) && mb_strlen($left_menu_row["avatar_file_map"]) > 0) {
			$output["data"]["avatar_file_map"] = $left_menu_row["avatar_file_map"];
		}

		return $output;
	}

	// проверяем, может ли данный инвайт содержать полную актуальную информацию о групповом диалоге
	protected static function _isUserCanGetConversationMetaDataInInvite(array $invite_row, array $meta_row):bool {

		// если статус active
		if ($invite_row["status"] == Type_Invite_Handler::STATUS_ACTIVE) {
			return true;
		}

		$is_member = Type_Conversation_Meta_Users::isMember($invite_row["user_id"], $meta_row["users"]);

		// если статус accepted и is_member = 1
		if ($invite_row["status"] == Type_Invite_Handler::STATUS_ACCEPTED && $is_member) {
			return true;
		}

		// если статус inactive и is_member = 1
		if ($invite_row["status"] == Type_Invite_Handler::STATUS_INACTIVE && $is_member) {
			return true;
		}

		return false;
	}

	// проверяем, является ли пользователь членом группы
	protected static function _isMember(array $left_menu_row, array $meta_row):bool {

		if (isset($left_menu_row["user_id"])) {
			return Type_Conversation_Meta_Users::isMember($left_menu_row["user_id"], $meta_row["users"]);
		}
		return false;
	}
}