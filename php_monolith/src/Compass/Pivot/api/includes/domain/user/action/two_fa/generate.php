<?php

namespace Compass\Pivot;

/**
 * Проверить и сгенерировать при необходимости токен 2fa
 */
class Domain_User_Action_TwoFa_Generate {

	/**
	 * Проверить и сгенерировать при необходимости токен 2fa
	 *
	 * @param int $user_id
	 * @param int $action_type
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotAuth_TwoFa
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(int $user_id, int $action_type, int $company_id = 0):Struct_Db_PivotAuth_TwoFa {

		Domain_User_Entity_Confirmation_Main::assertTypeIsValid($action_type);

		try {
			// пробуем достать предыдущий запрос
			$two_fa_story = Domain_User_Entity_Confirmation_TwoFa_TwoFa::getLastByUserAndType($user_id, $action_type, $company_id);
			$two_fa_story->assertNotExpired()
				->assertNotFinished()
				->assertNotActive();
		} catch (\cs_RowIsEmpty | cs_TwoFaIsExpired | cs_TwoFaIsFinished | cs_TwoFaIsActive) {

			$two_fa_data  = Domain_User_Action_TwoFa_GenerateToken::do($user_id, $action_type, $company_id);
			$two_fa_story = new Domain_User_Entity_Confirmation_TwoFa_TwoFa($two_fa_data);

			// добавляем в phphooker задачу, чтобы в случае протухания попытки – скинуть лог в аналитику
			Type_Phphooker_Main::onTwoFaStoryExpire($two_fa_data->two_fa_map, $two_fa_data->expires_at);
		}

		return $two_fa_story->getData();
	}
}