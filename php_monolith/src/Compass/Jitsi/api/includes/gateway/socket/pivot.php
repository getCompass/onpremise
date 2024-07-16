<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для общения с pivot.
 */
class Gateway_Socket_Pivot extends Gateway_Socket_Default {

	/**
	 * Проверяет возможность пользователя создать конференцию.
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function isMediaConferenceCreatingAllowed(int $user_id, int $space_id):array {

		$ar_post = [
			"user_id"    => $user_id,
			"company_id" => $space_id,
		];

		$method = "company.member.isMediaConferenceCreatingAllowed";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_JITSI);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		return [$response["is_allowed"], $response["error_code"]];
	}

	/**
	 * Получаем список компаний в которых состоят как user_id_1, так и user_id_2
	 * @return array
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getUsersIntersectSpaces(int $user_id_1, int $user_id_2):array {

		$ar_post = [
			"user_id_1" => $user_id_1,
			"user_id_2" => $user_id_2,
		];

		$method = "user.getUsersIntersectSpaces";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_JITSI);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		return $response["intersect_space_id_list"];
	}

	/**
	 * Проверяет возможность пользователя создать конференцию.
	 *
	 * @param int $user_id
	 * @param int $opponent_user_id
	 * @param int $space_id
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function checkIsAllowedForCall(int $user_id, int $opponent_user_id, int $space_id):array {

		$ar_post = [
			"user_id"          => $user_id,
			"space_id"         => $space_id,
			"opponent_user_id" => $opponent_user_id,
		];

		$method = "company.member.checkIsAllowedForCall";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_JITSI);
		$ecode = 0;

		if ($status !== "ok") {

			return [$response["error_code"], ""];
		}

		return [$ecode, $response["conversation_map"]];
	}

	/**
	 * инкрементим статистику участия пользователя в конференции
	 *
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function incConferenceMembershipRating(string $user_id, int $space_id):void {

		$ar_post = [
			"user_id"  => $user_id,
			"space_id" => $space_id,
		];

		$method = "user.incConferenceMembershipRating";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_JITSI);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем ссылку на модуль
	 *
	 */
	protected static function _getUrl():string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");

		return $socket_url_config["pivot"] . $socket_module_config["pivot"]["socket_path"];
	}
}
