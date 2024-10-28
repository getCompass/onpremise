<?php

namespace Compass\Pivot;

/**
 * задача класса общаться между php_pivot -> php_jitsi
 */
class Gateway_Socket_Jitsi extends Gateway_Socket_Default{

	/**
	 * Удаляем все конференции пользователя
	 */
	public static function removeAllPermanentConference(int $user_id, int $company_id):void {

		$ar_post = [
			"user_id"  => $user_id,
			"space_id" => $company_id,
		];
		self::_doCallSocket("conference.removeAllPermanentConference", $ar_post);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Выполняем socket запрос в jitsi
	 */
	protected static function _doCallSocket(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketJitsiUrl();
		return self::_doCall($url, $method, $params, $user_id);
	}
}
