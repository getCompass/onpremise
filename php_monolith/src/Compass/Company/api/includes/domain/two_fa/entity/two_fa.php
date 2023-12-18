<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с 2fa
 *
 * Class Domain_TwoFa_Entity_TwoFa
 */
class Domain_TwoFa_Entity_TwoFa {

	public const TWO_FA_CHANGE_PIN_TYPE     = 1; // тип изменения пинкода
	public const TWO_FA_SELF_DISMISSAL_TYPE = 2; // покидание компании (самоувольнение)
	public const TWO_FA_DELETE_COMPANY      = 3; // удаление компании

	/**
	 * Обработать 2fa действие
	 *
	 * @throws cs_ActionForCompanyBlocked
	 * @throws cs_AnswerCommand
	 * @throws cs_TwoFaIsInvalid
	 * @throws cs_TwoFaIsNotActive
	 * @throws \parseException|\returnException|\blockException
	 */
	public static function handle(int $user_id, int $action_type, string $two_fa_key):void {

		if ($two_fa_key) {

			try {

				// пробуем отвалидировать токен
				Gateway_Socket_Pivot::tryValidateTwoFaToken($user_id, $action_type, $two_fa_key);

				return;
			} catch (cs_AnswerCommand) {
				// если пришла команда, значит токен нужно пересоздать (он просрочен или действие уже закончено)
			}
		}
		// генерируем новый токен и выбрасываем команду
		self::_generateToken($user_id, $action_type);
	}

	/**
	 * Пометить 2fa токен как неактивный
	 *
	 * @throws \parseException|\returnException
	 */
	public static function setTwoFaTokenAsInactive(int $user_id, string $two_fa_key):void {

		Gateway_Socket_Pivot::setTwoFaTokenAsInactive($user_id, $two_fa_key);
	}

	/**
	 * Сгенерировать 2fa токен и выбросить команду
	 *
	 * @throws cs_AnswerCommand
	 * @throws \parseException|\returnException|\blockException
	 */
	protected static function _generateToken(int $user_id, int $action_type):array {

		$two_fa_info = Gateway_Socket_Pivot::doGenerateTwoFaToken($user_id, $action_type);

		throw new cs_AnswerCommand("need_confirm_2fa", $two_fa_info);
	}
}
