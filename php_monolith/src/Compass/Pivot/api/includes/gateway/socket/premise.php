<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс-интерфейс для работы с модулем php_premise
 */
class Gateway_Socket_Premise extends Gateway_Socket_Default {

	/**
	 * Выполняется при регистрации пользователя.
	 */
	public static function userRegistered(int $user_id, int $npc_type, int $is_root):void {

		if (!ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"user_id"  => $user_id,
			"npc_type" => $npc_type,
			"is_root"  => $is_root,
		];
		self::_doCallSocket("premise.userRegistered", $ar_post);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// выполняем socket запрос
	protected static function _doCallSocket(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketPremiseUrl();
		return self::_doCall($url, $method, $params, $user_id);
	}
}
