<?php

namespace Compass\Company;

/**
 * Базовый класс для добавления пользовательского бота в компанию как !!!ПОЛЬЗОВАТЕЛЯ-УЧАСТНИКА
 */
class Domain_User_Action_AddUserbotToMember {

	protected const _ROLE           = \CompassApp\Domain\Member\Entity\Member::ROLE_USERBOT;
	protected const _PERMISSIONS    = \CompassApp\Domain\Member\Entity\Permission::DEFAULT;
	protected const _BADGE_COLOR_ID = 4;
	protected const _BADGE_CONTENT  = "Bot";

	/**
	 * выполняем действие добавления пользовательского бота в компанию
	 */
	public static function do(int $user_id, int $npc_type, string $full_name, string $avatar_file_key, string $short_description):void {

		// инициализируем extra
		$extra = \CompassApp\Domain\Member\Entity\Extra::initExtra();
		$extra = \CompassApp\Domain\Member\Entity\Extra::setBadgeInExtra($extra, self::_BADGE_COLOR_ID, self::_BADGE_CONTENT);

		Gateway_Db_CompanyData_MemberList::insertOrUpdate(
			$user_id,
			self::_ROLE,
			$npc_type,
			self::_PERMISSIONS,
			"",
			$full_name,
			$short_description,
			$avatar_file_key,
			"",
			$extra,
		);
	}

	/**
	 * Получаем роль и права которые будут установлены для userbot
	 *
	 * @return array
	 */
	public static function getUserbotPresetRolePermissions():array {

		return [self::_ROLE, self::_PERMISSIONS];
	}

}
