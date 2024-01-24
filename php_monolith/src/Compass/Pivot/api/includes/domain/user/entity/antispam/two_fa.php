<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;

/**
 * Class Domain_User_Entity_Antispam_TwoFa
 */
class Domain_User_Entity_Antispam_TwoFa {

	/**
	 * Проверка блокировок перед началом 2fa
	 *
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	public static function checkBeforeSendTwoFaSms(int $user_id, int $company_id, string|false $grecaptcha_response):void {

		try {

			if (Type_Antispam_User::needCheckIsBlocked()) {
				return;
			}

			Type_Antispam_User::assertNotBlock(
				$user_id,
				Type_Antispam_Leveled_User::getBlockRule(Type_Antispam_Leveled_User::TWO_FA),
			);

			$is_need_check_company_block = !Type_List_WhiteList::isCompanyInWhiteList($company_id) || Type_Antispam_Leveled_Main::isWarningLevel();

			if ($is_need_check_company_block) {

				Type_Antispam_Company::checkAndIncrementBlock(
					$company_id,
					Type_Antispam_Leveled_User::getBlockRule(Type_Antispam_Leveled_Company::TWO_FA),
				);
			}

			Type_Antispam_User::throwIfBlocked(
				$user_id,
				Type_Antispam_Leveled_User::getBlockRule(Type_Antispam_Leveled_User::TWO_FA),
			);
		} catch (BlockException) {
			Type_Captcha_Main::assertCaptcha($grecaptcha_response);
		}
	}

	/**
	 * Уменьшаем блокировки при успешном завершении 2fa
	 *
	 */
	public static function successTwoFaConfirm(int $user_id, int $company_id):void {

		Type_Antispam_User::decrement($user_id, Type_Antispam_Leveled_User::getBlockRule(Type_Antispam_Leveled_User::TWO_FA));
		Type_Antispam_Company::decrement($company_id, Type_Antispam_Leveled_Company::getBlockRule(Type_Antispam_Leveled_Company::TWO_FA));
	}
}
