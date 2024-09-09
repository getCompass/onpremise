<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * основной класс для работы с инвайтами
 */
class Type_Invite_Handler {

	// лимит на отправку активных инвайтов
	public const LIMIT_SEND_ACTIVE_INVITE = 100;

	// статусы приглашения
	public const STATUS_ACTIVE        = 0;
	public const STATUS_ACCEPTED      = 1;
	public const STATUS_DECLINED      = 2;
	public const STATUS_REVOKED       = 3;
	public const STATUS_INACTIVE      = 4;
	public const STATUS_AUTO_ACCEPTED = 5;

	public const STATUS_TITLE = [
		self::STATUS_ACTIVE        => "active",
		self::STATUS_ACCEPTED      => "accepted",
		self::STATUS_DECLINED      => "declined",
		self::STATUS_REVOKED       => "revoked",
		self::STATUS_INACTIVE      => "inactive",
		self::STATUS_AUTO_ACCEPTED => "auto_accepted",
	];

	// статусы нахождения юзера в группе
	public const MEMBER_STATUS_NOT_ATTACHED = "not_attached"; // пользователь не прикреплен
	public const MEMBER_STATUS_MEMBER       = "member"; // обычный участник диалога
	public const MEMBER_STATUS_ADMIN        = "admin"; // участник с правами администратора
	public const MEMBER_STATUS_OWNER        = "owner"; // создатель группового диалога (верховный)
	public const MEMBER_STATUS_KICKED       = "kicked"; // пользователя кикнули
	public const MEMBER_STATUS_LEAVED       = "leaved"; // пользователь вышел

	// причины статуса inactive
	public const INACTIVE_REASON_ACCEPTED      = 0; // пользователь принял другое приглашение в группу
	public const INACTIVE_REASON_SENDER_LEAVED = 1; // отправитель покинул группу
	public const INACTIVE_REASON_BLOCKED       = 2; // пользователь был заблокирован

	// помечаем неактивным
	public static function setInactive(string $invite_map, array $invite_row, int $user_id, array $meta_row, int $inactive_reason, bool $is_remove_user):void {

		$type = \CompassApp\Pack\Invite::getType($invite_map);

		switch ($type) {

			case SINGLE_INVITE_TO_GROUP :
				Type_Invite_Single::setInactive($invite_row, $user_id, $meta_row, $inactive_reason, $is_remove_user);
				break;
			default:
				throw new ParseFatalException("Unknown invite type");
		}
	}

	// принимаем инвайт
	public static function setAccepted(string $invite_map, array $invite_row, int $user_id, array $meta_row):void {

		$type = \CompassApp\Pack\Invite::getType($invite_map);

		switch ($type) {

			case SINGLE_INVITE_TO_GROUP :
				Type_Invite_Single::setAccepted($invite_row, $user_id, $meta_row);
				break;
			default:
				throw new ParseFatalException("Unknown invite type");
		}
	}

	// отзываем инвайт
	public static function setRevoked(string $invite_map, array $invite_row, int $user_id, array $meta_row):void {

		$type = \CompassApp\Pack\Invite::getType($invite_map);

		switch ($type) {

			case SINGLE_INVITE_TO_GROUP :
				Type_Invite_Single::setRevoked($invite_row, $user_id, $meta_row);
				break;
			default:
				throw new ParseFatalException("Unknown invite type");
		}
	}

	// отклоняем инвайт
	public static function setDeclinedByDpcInvite(string $invite_map, array $invite_row, int $user_id, array $meta_row):void {

		$type = \CompassApp\Pack\Invite::getType($invite_map);

		switch ($type) {

			case SINGLE_INVITE_TO_GROUP :
				Type_Invite_Single::setDeclined($invite_row["group_conversation_map"], $invite_row, $user_id, $meta_row);
				break;
			default:
				throw new ParseFatalException("Unknown invite type");
		}
	}

	// отклоняем инвайт
	public static function setDeclinedByDpcConversation(string $invite_map, array $invite_row, int $user_id, array $meta_row):void {

		$type = \CompassApp\Pack\Invite::getType($invite_map);

		switch ($type) {

			case SINGLE_INVITE_TO_GROUP :
				Type_Invite_Single::setDeclined($invite_row["conversation_map"], $invite_row, $user_id, $meta_row);
				break;
			default:
				throw new ParseFatalException("Unknown invite type");
		}
	}

	// получаем инвайт
	public static function get(string $invite_map):array {

		$type = \CompassApp\Pack\Invite::getType($invite_map);

		// получаем по типу инвайта
		switch ($type) {

			case SINGLE_INVITE_TO_GROUP :
				return Type_Invite_Single::get($invite_map);
			default:
				throw new ParseFatalException("Unknown invite type");
		}
	}

	// если статус инвайта отклонен
	public static function isDeclined(int $status):bool {

		return in_array($status, [Type_Invite_Handler::STATUS_DECLINED, Type_Invite_Handler::STATUS_REVOKED]);
	}

	/**
	 * получаем лимит на отправку инвайтов
	 *
	 */
	public static function getSendActiveInviteLimit():int {

		if (isTestServer() === false) {
			return self::LIMIT_SEND_ACTIVE_INVITE;
		}

		$test_send_active_invite_limit = Type_System_Testing::getForceActiveSendInviteLimit();

		if ($test_send_active_invite_limit > 0) {
			return $test_send_active_invite_limit;
		}

		return self::LIMIT_SEND_ACTIVE_INVITE;
	}
}