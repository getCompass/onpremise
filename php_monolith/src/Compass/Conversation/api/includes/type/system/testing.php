<?php

namespace Compass\Conversation;

/**
 * класс для поддержки testing-кода
 * работает через заголовки, чтобы удобно рулить все в одном месте
 */
class Type_System_Testing {

	/**
	 * функция срабатывает перед тем как вызвать любой из методов
	 *
	 * @throws \parseException
	 */
	public static function __callStatic(string $name, array $arguments):void {

		assertTestServer();
	}

	// удаление сообщения в любом случае
	public static function isForceSetDeleted():bool {

		$force_allow_deleted = getHeader("HTTP_FORCE_SET_DELETED");
		$force_allow_deleted = intval($force_allow_deleted);

		if ($force_allow_deleted == 1) {
			return true;
		}

		return false;
	}

	// вышло время удаления сообщения
	public static function isForceExpireTimeDelete():bool {

		$force_disallow_delete = getHeader("HTTP_FORCE_EXPIRE_TIME_TO_DISALLOW_DELETE");
		$force_disallow_delete = intval($force_disallow_delete);

		if ($force_disallow_delete == 1) {
			return true;
		}

		return false;
	}

	// редактирование сообщения при вышедшем времени
	public static function isForceExpireTimeEdit():bool {

		$value = getHeader("HTTP_FORCE_EXPIRE_TIME_TO_DISALLOW_EDIT");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}

	// получить лимит активных инвайтов для тестов
	public static function getForceActiveSendInviteLimit():int {

		$value = getHeader("HTTP_FORCE_ACTIVE_SEND_INVITE_LIMIT");
		return (int) $value;
	}

}