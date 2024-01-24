<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Действие обновления онбординга
 * Class Domain_User_Action_Onboarding_Activate
 */
class Domain_User_Action_Onboarding_Activate {

	/**
	 * Обновляем
	 *
	 * @throws BusFatalException
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 * @throws EndpointAccessDeniedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 */
	public static function do(Struct_Db_PivotUser_User $user, int $type, array $data = []):void {

		// создаем онбординг
		$onboarding = new Struct_User_Onboarding(
			$type,
			Domain_User_Entity_Onboarding::STATUS_ACTIVE,
			$data,
			time(),
			0
		);

		Domain_User_Action_Onboarding_Update::do($user, $onboarding);
	}
}