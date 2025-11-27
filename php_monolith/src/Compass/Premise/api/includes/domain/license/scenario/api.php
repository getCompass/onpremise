<?php

declare(strict_types=1);

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для сценариев api сущности лицензии
 */
class Domain_License_Scenario_Api
{
	/**
	 * Получаем токен аутентификации для получения лицензии
	 *
	 * @throws Domain_Premise_Exception_ServerNotFound
	 * @throws Domain_User_Exception_UserHaveNotPermissions
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getAuthenticationToken(int $user_id): Struct_Premise_AuthToken
	{

		if (ServerProvider::isLocalLicense()) {
			throw new ControllerMethodNotFoundException("method is not allowed");
		}

		// проверяем права пользователя
		$current_user = Gateway_Db_PremiseUser_UserList::getOne($user_id);
		Domain_User_Entity_Permissions::assertIfNotHavePermissions((bool) $current_user->has_premise_permissions);

		// получаем токен аутентификации
		try {
			return Gateway_Premise_License::getAuthenticationToken($user_id);
		} catch (Gateway_Premise_Exception_ServerNotFound) {
			throw new Domain_Premise_Exception_ServerNotFound("server not found");
		}
	}
}
