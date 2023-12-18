<?php

namespace Compass\Company;

use BaseFrame\Server\ServerProvider;

/**
 * Класс-интерфейс для работы с модулем php_intercom
 */
class Gateway_Socket_Intercom extends Gateway_Socket_Default {

	// изменилось имя компании
	public static function spaceNameChanged(string $name):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"company_id" => COMPANY_ID,
			"name"       => $name,
		];
		self::_doCallSocket("space.nameChanged", $ar_post);
	}

	// пользователь вступил в пространство
	public static function userJoined(int $user_id, int $member_count):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"company_id"   => COMPANY_ID,
			"user_id"      => $user_id,
			"member_count" => $member_count,
		];
		self::_doCallSocket("space.userJoined", $ar_post);
	}

	// пользователь покинул пространство
	public static function userLeaved(int $user_id, int $member_count):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"company_id"   => COMPANY_ID,
			"user_id"      => $user_id,
			"member_count" => $member_count,
		];
		self::_doCallSocket("space.userLeaved", $ar_post);
	}

	// изменились данные пользователя в пространстве
	public static function setMember(int $user_id, string|false $badge = false, string|false $description = false, string|false $role = false):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"company_id" => COMPANY_ID,
			"user_id"    => $user_id,
		];
		if ($badge !== false) {
			$ar_post["badge"] = $badge;
		}
		if ($description !== false) {
			$ar_post["description"] = $description;
		}
		if ($role !== false) {
			$ar_post["role"] = $role;
		}
		self::_doCallSocket("space.setMember", $ar_post);
	}

	/**
	 * открыт попап тарифов пространства
	 */
	public static function onTariffShowcaseOpened(int $user_id, string $ip, string $user_agent):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"space_id"   => COMPANY_ID,
			"user_id"    => $user_id,
			"ip"         => $ip,
			"user_agent" => $user_agent,
		];
		self::_doCallSocket("space.onTariffShowcaseOpened", $ar_post);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// выполняем socket запрос
	protected static function _doCallSocket(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketIntercomUrl("intercom");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
