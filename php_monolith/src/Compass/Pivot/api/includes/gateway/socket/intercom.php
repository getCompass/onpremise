<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс-интерфейс для работы с модулем php_intercom
 */
class Gateway_Socket_Intercom extends Gateway_Socket_Default {

	// помечаем профиль удаленным
	public static function userProfileDeleted(int $user_id):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"deleted_user_id" => $user_id,
		];
		self::_doCallSocket("user.profileDeleted", $ar_post);
	}

	// пользователь сменил номер телефона
	public static function userPhoneNumberChanged(int $user_id, string $new_phone_number):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"user_id"          => $user_id,
			"new_phone_number" => $new_phone_number,
		];
		self::_doCallSocket("user.phoneNumberChanged", $ar_post);
	}

	// пользователь изменил профиль
	public static function userSetProfile(int $user_id, string|false $name, string|false $avatar_file_map):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"user_id" => $user_id,
		];

		if ($avatar_file_map !== false) {
			$ar_post["avatar_file_key"] = Type_Pack_File::doEncrypt($avatar_file_map);
		}

		if ($name !== false) {
			$ar_post["name"] = $name;
		}

		self::_doCallSocket("user.setProfile", $ar_post);
	}

	// пользователь удалил аватарку
	public static function userDoClearAvatar(int $user_id):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"user_id" => $user_id,
		];
		self::_doCallSocket("user.doClearAvatar", $ar_post);
	}

	// пространство было удалено
	public static function spaceDeleted(int $company_id):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"company_id" => $company_id,
		];
		self::_doCallSocket("space.spaceDeleted", $ar_post);
	}

	// первая оплата в пространстве
	public static function spaceFirstPay(int $company_id):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"company_id" => $company_id,
		];
		self::_doCallSocket("space.firstPay", $ar_post);
	}

	/**
	 * действие тарифа скоро закончится
	 */
	public static function onSpaceTariffExpiration(int $user_id, int $company_id):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"user_id"  => $user_id,
			"space_id" => $company_id,
		];
		self::_doCallSocket("space.onSpaceTariffExpiration", $ar_post);
	}

	/**
	 * действие тарифа закончилось - скоро пространство будет заблокировано
	 */
	public static function onSpaceTariffExpired(int $user_id, int $company_id):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"user_id"  => $user_id,
			"space_id" => $company_id,
		];
		self::_doCallSocket("space.onSpaceTariffExpired", $ar_post);
	}

	/**
	 * пространство заблокировано
	 */
	public static function onSpaceTariffBlocked(int $user_id, int $company_id):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"user_id"  => $user_id,
			"space_id" => $company_id,
		];
		self::_doCallSocket("space.onSpaceTariffBlocked", $ar_post);
	}

	// выполняем socket запрос
	protected static function _doCallSocket(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketIntercomUrl();
		return self::_doCall($url, $method, $params, $user_id);
	}
}
