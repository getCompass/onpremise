<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Действие обновления онбординга
 *
 * Class Domain_User_Action_Onboarding_Finish
 */
class Domain_User_Action_Onboarding_Finish {

	/**
	 * Обновляем
	 *
	 * @param int $user_id
	 * @param int $type
	 *
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatusStep
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 */
	public static function do(int $user_id, int $type):void {

		$user            = Gateway_Bus_PivotCache::getUserInfo($user_id);
		$onboarding_list = Type_User_Main::getOnboardingList($user->extra);
		$onboarding      = Domain_User_Entity_Onboarding::getFromOnboardingList($type, $onboarding_list);
		$status          = Domain_User_Entity_Onboarding::STATUS_FINISHED;

		// если не нашли онбординг - то тогда выкидываем ошибку
		if ($onboarding === false || $onboarding->status !== Domain_User_Entity_Onboarding::STATUS_ACTIVE) {
			throw new Domain_User_Exception_Onboarding_NotAllowedStatusStep("cant finish onboarding");
		}

		$set_onboarding = new Struct_User_Onboarding(
			$onboarding->type,
			$status,
			$onboarding->data,
			$onboarding->activated_at,
			time()
		);

		Domain_User_Action_Onboarding_Update::do($user, $set_onboarding);
	}
}