<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии для системных API
 */
class Domain_System_Scenario_Api {

	/**
	 * Сценарий получения служебных данных для клиента, 2 версия
	 *
	 * @throws Domain_System_Exception_EmojiKeywordsNotFound
	 * @throws Domain_System_Exception_VideoOnboardingNotFound
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\LocaleNotFound
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_IncorrectVersion
	 * @throws cs_PlatformNotFound
	 */
	public static function getStartDataV2(int $user_id, array $need_data_list):array {

		return Domain_System_Action_GetStartDataV2::do($user_id, $need_data_list);
	}
}
