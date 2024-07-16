<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;

/**
 * Валидируем 2fa токен
 */
class Domain_User_Action_Confirmation_Mail_Validate {

	/**
	 * Валидируем 2fa токен
	 *
	 * @param int    $user_id
	 * @param int    $action_type
	 * @param string $confirmation_key
	 *
	 * @throws BlockException
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidMailPasswordStoryKey
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidUser
	 * @throws Domain_User_Exception_Confirmation_Mail_IsExpired
	 * @throws Domain_User_Exception_Confirmation_Mail_IsInvalidType
	 * @throws Domain_User_Exception_Confirmation_Mail_IsNotConfirmed
	 * @throws Domain_User_Exception_Confirmation_Mail_NotSuccess
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_UnknownKeyType
	 * @throws cs_blockException
	 */
	public static function do(int $user_id, int $action_type, string $confirmation_key):void {

		// проверяем не заблокирована ли проверка
		Type_Antispam_User::check($user_id, Type_Antispam_User::WRONG_MAIL_PASSWORD_TOKEN);

		try {

			$confirmation_map = Type_Pack_Main::replaceKeyWithMap("confirm_mail_password_story_key", $confirmation_key);
			$story            = Domain_User_Entity_Confirmation_Mail_Story::getByMap($confirmation_map);

			$story->assertCorrectUser($user_id)->assertTypeIsValid($action_type)->assertNotExpired()->assertIsConfirmed()->assertSuccess();
		} catch (\Exception $e) {

			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::WRONG_MAIL_PASSWORD_TOKEN);
			throw $e;
		}
	}
}