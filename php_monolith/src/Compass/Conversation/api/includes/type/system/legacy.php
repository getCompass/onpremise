<?php

namespace Compass\Conversation;

/**
 * Класс для поддержки legacy-кода
 *
 * Работает через заголовки, чтобы удобно рулить все в одном месте
 */
class Type_System_Legacy {

	// новая ошибка для invites.doDecline
	public static function isNewDoDeclineError():bool {

		$value = getHeader("HTTP_IS_NEW_DECLINE_INVITE_ERROR");
		$value = intval($value);

		if ($value == 1) {

			Gateway_Bus_Statholder::inc("invites", "row69");
			return true;
		}

		Gateway_Bus_Statholder::inc("invites", "row70");
		return false;
	}

	// новая ошибка для invites.tryAccept
	public static function isNewTryAcceptError():bool {

		$value = getHeader("HTTP_IS_NEW_TRY_ACCEPT_INVITE_ERROR");
		$value = intval($value);

		if ($value == 1) {

			Gateway_Bus_Statholder::inc("invites", "row47");
			return true;
		}

		Gateway_Bus_Statholder::inc("invites", "row48");
		return false;
	}

	// новые ошибки для методов модуля php_conversation
	public static function isNewErrors():bool {

		$value = getHeader("HTTP_IS_NEW_ERRORS");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		Gateway_Bus_Statholder::inc("messages", "row945");
		return false;
	}

	// отключение автоназначения на администратора в группе
	public static function isDisabledAutoAssignmentOfAdministrator():bool {

		$value = getHeader("HTTP_X_COMPASS_IS_DISABLED_AUTO_ASSIGNMENT_OF_ADMINISTRATOR");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	/**
	 * отдаем ли новую ошибку когда все пользователи из приглашенных были удалены
	 */
	public static function is504ErrorThenAllUserWasKicked():bool {

		$value = getHeader("HTTP_IS_ALL_USER_WAS_KICKED_ERROR");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	/**
	 * отдаем ли новую ошибку при дубликате client_message_id
	 */
	public static function isDuplicateClientMessageIdError():bool {

		$value = getHeader("HTTP_IS_DUPLICATE_CLIENT_MESSAGE_ID_ERROR");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		Gateway_Bus_CollectorAgent::init()->inc("row1"); // количество вызовов legacy
		return false;
	}
}