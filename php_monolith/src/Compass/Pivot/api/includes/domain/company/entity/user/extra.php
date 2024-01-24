<?php

namespace Compass\Pivot;

/**
 * Класс для рбаоты с extra
 */
class Domain_Company_Entity_User_Extra {

	// текущая версия extra
	protected const _EXTRA_COMPANY_USER_VERSION = 2;

	protected const _EXTRA_COMPANY_USER_SCHEMA = [

		1 => [
			"invite_link_id" => 0,
			"user_info"      => [],
		],
		2 => [
			"invite_link_uniq" => "",
			"inviter_user_id"  => 0,
			"user_info"        => [],
		],
	];

	/**
	 * возвращает текущую структуру extra с default значениями
	 *
	 * @param string $invite_link_uniq
	 * @param int    $inviter_user_id
	 *
	 * @return array
	 */
	public static function init(string $invite_link_uniq = "", int $inviter_user_id = 0):array {

		$extra                              = [
			"handler_version" => self::_EXTRA_COMPANY_USER_VERSION,
			"extra"           => self::_EXTRA_COMPANY_USER_SCHEMA[self::_EXTRA_COMPANY_USER_VERSION],
		];
		$extra["extra"]["invite_link_uniq"] = $invite_link_uniq;
		$extra["extra"]["inviter_user_id"]  = $inviter_user_id;
		return $extra;
	}

	/**
	 * Получаем invite_link_uniq
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getInviteLinkUniq(array $extra):int {

		// получаем актуальное extra
		$extra = self::_get($extra);

		return $extra["extra"]["invite_link_uniq"];
	}

	/**
	 * Получаем inviter_user_id
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getInviterUserId(array $extra):int {

		// получаем актуальное extra
		$extra = self::_get($extra);

		return $extra["extra"]["inviter_user_id"];
	}

	/**
	 * Устанавливаем invite_link_uniq
	 *
	 * @param array $extra
	 * @param int   $invite_link_uniq
	 *
	 * @return array
	 */
	public static function setInviteUniqId(array $extra, int $invite_link_uniq):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		$extra["extra"]["invite_link_uniq"] = $invite_link_uniq;
		return $extra;
	}

	/**
	 * Устанавливаем inviter_user_id
	 *
	 * @param array $extra
	 * @param int   $inviter_user_id
	 *
	 * @return array
	 */
	public static function setInviterUserId(array $extra, int $inviter_user_id):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		$extra["extra"]["inviter_user_id"] = $inviter_user_id;
		return $extra;
	}

	/**
	 * Устанавливаем user_info
	 *
	 * @param array            $extra
	 * @param Struct_User_Info $user_info
	 *
	 * @return array
	 */
	public static function setUserInfo(array $extra, Struct_User_Info $user_info):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		$extra["extra"]["user_info"] = [
			"user_id"         => $user_info->user_id,
			"type"            => $user_info->type,
			"is_verified"     => $user_info->is_verified,
			"full_name"       => $user_info->full_name,
			"avatar_file_map" => $user_info->avatar_file_map,
		];
		return $extra;
	}

	/**
	 * получить extra
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	protected static function _get(array $extra):array {

		if (!isset($extra["handler_version"])) {
			return self::init();
		}

		// сравниваем версию пришедшей extra с текущей
		if ($extra["handler_version"] != self::_EXTRA_COMPANY_USER_VERSION) {

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]           = array_merge(self::_EXTRA_COMPANY_USER_SCHEMA[self::_EXTRA_COMPANY_USER_VERSION], $extra["extra"]);
			$extra["handler_version"] = self::_EXTRA_COMPANY_USER_VERSION;
		}

		return $extra;
	}
}
