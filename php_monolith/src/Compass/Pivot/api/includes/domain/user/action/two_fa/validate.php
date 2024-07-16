<?php

namespace Compass\Pivot;

/**
 * Валидируем 2fa токен
 */
class Domain_User_Action_TwoFa_Validate {

	/**
	 * Валидируем 2fa токен
	 *
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_TwoFaInvalidCompany
	 * @throws cs_TwoFaInvalidUser
	 * @throws cs_TwoFaIsExpired
	 * @throws cs_TwoFaIsFinished
	 * @throws cs_TwoFaIsNotActive
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws cs_UnknownKeyType
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_WrongTwoFaKey
	 * @throws cs_blockException
	 */
	public static function do(int $user_id, int $action_type, string $two_fa_key, int $company_id = 0):void {

		// проверяем не заблокирована ли проверка
		Type_Antispam_User::check($user_id, Type_Antispam_User::WRONG_TWO_FA_TOKEN);

		try {

			$two_fa_map = Type_Pack_Main::replaceKeyWithMap("two_fa_key", $two_fa_key);
			$story      = Domain_User_Entity_Confirmation_TwoFa_Story::getByMap($two_fa_map);

			$story->assertCorrectUser($user_id)
				->assertCorrectCompanyId($company_id)
				->assertTypeIsValid($action_type)
				->assertNotExpired()
				->assertNotFinished()
				->assertActive();
		} catch (\Exception $e) {

			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::WRONG_TWO_FA_TOKEN);
			throw $e;
		}
	}
}