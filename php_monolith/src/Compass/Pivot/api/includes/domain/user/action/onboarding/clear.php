<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Действие сброса онбординга
 *
 * Class Domain_User_Action_Onboarding_Clear
 */
class Domain_User_Action_Onboarding_Clear {

	/**
	 * Обновляем
	 *
	 * @param int   $user_id
	 * @param int   $type
	 * @param array $data
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
	public static function do(int $user_id, int $type):void {

		$user            = Gateway_Bus_PivotCache::getUserInfo($user_id);
		$onboarding_list = Type_User_Main::getOnboardingList($user->extra);
		$onboarding      = Domain_User_Entity_Onboarding::getFromOnboardingList($type, $onboarding_list);

		// если нашли онбординг - то тогда онбординг сбрасываем
		if ($onboarding !== false && $onboarding->status === Domain_User_Entity_Onboarding::STATUS_ACTIVE) {

			$set_onboarding = new Struct_User_Onboarding(
				$onboarding->type,
				Domain_User_Entity_Onboarding::STATUS_UNAVAILABLE,
				$onboarding->data,
				0,
				0,
			);

			Domain_User_Action_Onboarding_Update::do($user, $set_onboarding);
		}
	}
}