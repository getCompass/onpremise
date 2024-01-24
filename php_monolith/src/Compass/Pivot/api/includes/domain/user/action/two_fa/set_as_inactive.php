<?php

namespace Compass\Pivot;

/**
 * Установить токен, как неактивный
 */
class Domain_User_Action_TwoFa_SetAsInactive {

	/**
	 * Установить токен как неактивный
	 *
	 * @param int    $user_id
	 * @param string $two_fa_map
	 *
	 * @throws cs_TwoFaInvalidUser
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function do(int $user_id, string $two_fa_map):void {

		try {

			$story = Domain_User_Entity_TwoFa_Story::getByMap($two_fa_map);
			$story->assertCorrectUser($user_id);

			Domain_User_Action_TwoFa_InvalidateToken::do($story);
		} catch (cs_WrongTwoFaKey) {
			// подавляем exception, инвалидация не должна выбрасывать ошибки
		}
	}
}