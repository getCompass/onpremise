<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Сбрасываем все онбординги
 *
 * Class Domain_User_Action_Onboarding_ClearAll
 */
class Domain_User_Action_Onboarding_ClearAll {

	/**
	 * Сбрасываем
	 *
	 * @param int $user_id
	 *
	 * @throws BusFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 */
	public static function do(int $user_id):void {

		// на паблике такая магия запрещена
		assertNotPublicServer();

		$user            = Gateway_Bus_PivotCache::getUserInfo($user_id);
		$onboarding_list = Type_User_Main::getOnboardingList($user->extra);

		// если онбордингов нет - то и писать ничего не надо в базу
		if ($onboarding_list === []) {
			return;
		}

		// просто обнуляем массив онбордингов
		$extra = Type_User_Main::setOnboardingList($user->extra, []);

		Gateway_Db_PivotUser_UserList::set($user_id, [
			"extra"      => $extra,
			"updated_at" => time(),
		]);

		// сбрасываем кэш пользователя
		Gateway_Bus_PivotCache::clearUserCacheInfo($user->user_id);
	}
}