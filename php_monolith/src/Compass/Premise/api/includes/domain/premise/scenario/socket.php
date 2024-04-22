<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\Member\Entity\Member;
use cs_RowIsEmpty;
use parseException;

/**
 * Класс обработки сценариев событий premise.
 */
class Domain_Premise_Scenario_Socket {

	/**
	 * Возвращает информацию о сервере.
	 */
	public static function getServerInfo():array {

		// регистрируем, если ранее сервер не был зарегистрирован
		try {
			Domain_Premise_Action_Register::do();
		} catch (\Exception) {
			// ничего не делаем
		}

		$secret_key_config = Domain_Config_Entity_Main::get(Domain_Config_Entity_Main::SECRET_KEY);
		return [PIVOT_DOMAIN, SERVER_UID, $secret_key_config->value["secret_key"] ?? ""];
	}

	/**
	 * Вызывается при регистрации пользователя.
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function userRegistered(int $user_id, int $npc_type, bool $is_root):void {

		// добавляем права админа и бухгалтера, если был добавлен рут-пользователь
		$premise_permissions     = Domain_User_Entity_Permissions::DEFAULT;
		$has_premise_permissions = 0;
		if ($is_root == 1) {

			$premise_permissions     = Domain_User_Entity_Permissions::addPermissionListToMask(
				$premise_permissions, [Domain_User_Entity_Permissions::SERVER_ADMINISTRATOR]
			);
			$has_premise_permissions = 1;
		}

		// добавляем нового пользователя
		Domain_User_Action_UserCreate::do($user_id, $npc_type, $has_premise_permissions, $premise_permissions);
	}
}
