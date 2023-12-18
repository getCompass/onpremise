<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Действие добавления онбординга, если ранее тот отсутствовал
 */
class Domain_User_Action_Onboarding_ActivateIfNotExist {

	/**
	 * Добавляем
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
	public static function do(int $user_id, int $type, array $data = []):void {

		$user            = Gateway_Bus_PivotCache::getUserInfo($user_id);
		$onboarding_list = Type_User_Main::getOnboardingList($user->extra);
		$onboarding      = Domain_User_Entity_Onboarding::getFromOnboardingList($type, $onboarding_list);

		// если нашли онбординг - то ничего не делаем
		if ($onboarding !== false) {
			return;
		}

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