<?php declare(strict_types = 1);

namespace Compass\Premise;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для сценариев api домена premise
 */
class Domain_Premise_Scenario_Api {

	/**
	 * Отдаем стартовые данные по premise
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function start(int $user_id):array {

		// получаем версию онпремайз
		$premise_config  = Domain_Config_Entity_Main::get(Domain_Config_Entity_Main::ONPREMISE_APP_VERSION);
		$premise_version = $premise_config->value["version"] ?? ONPREMISE_VERSION;


		// получаем права пользователя
		$user = Gateway_Db_PremiseUser_UserList::getOne($user_id);

		$permission_list = Domain_User_Entity_Permissions::getPermissionList($user->premise_permissions);

		return [$premise_version, $permission_list];
	}

	/**
	 * Вызываем метод проверки активации сервера
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getServerActivationStatus():array {

		return Domain_Premise_Action_GetServerActivationStatus::do();
	}
}