<?php

namespace Compass\Pivot;

/**
 * Класс для получения данных об истории 2fa действий
 *
 * Class Domain_User_Entity_Confirmation_TwoFa_TwoFa
 */
class Domain_User_Entity_Confirmation_TwoFa_TwoFa {

	public const EXPIRE_AT = 60 * 20; // через сколько истекает попытка 2fa действия

	protected Struct_Db_PivotAuth_TwoFa $two_fa;

	/**
	 * Domain_User_Entity_Confirmation_TwoFa_TwoFa constructor.
	 *
	 */
	public function __construct(Struct_Db_PivotAuth_TwoFa $two_fa) {

		$this->two_fa = $two_fa;
	}

	/**
	 * получить по ключу 2fa
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_WrongAuthKey
	 */
	public static function getByMap(string $two_fa_map):self {

		try {
			$two_fa = Gateway_Db_PivotAuth_TwoFaList::getOne($two_fa_map);
		} catch (\cs_RowIsEmpty) {
			throw new cs_WrongAuthKey();
		}

		return new self($two_fa);
	}

	/**
	 * Получить последнее действие по пользователю и типу
	 *
	 * @return static
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getLastByUserAndType(int $user_id, int $action_type, int $company_id):self {

		$two_fa = Gateway_Db_PivotAuth_TwoFaList::getLastByUserAndType($user_id, $action_type, $company_id);

		return new self($two_fa);
	}

	/**
	 * Получить структурные данные
	 *
	 */
	public function getData():Struct_Db_PivotAuth_TwoFa {

		return $this->two_fa;
	}

	/**
	 * Проверить, не истекло ли время 2fa
	 *
	 * @return $this
	 *
	 * @throws cs_TwoFaIsExpired
	 */
	public function assertNotExpired():self {

		if ($this->two_fa->expires_at < time()) {
			throw new cs_TwoFaIsExpired();
		}

		return $this;
	}

	/**
	 * Проверить, не закончено ли 2fa действие
	 *
	 * @return $this
	 *
	 * @throws cs_TwoFaIsFinished
	 */
	public function assertNotFinished():self {

		if ($this->two_fa->is_success) {
			throw new cs_TwoFaIsFinished();
		}

		return $this;
	}

	/**
	 * Проверить, что смс был потвержден
	 *
	 * @return $this
	 *
	 * @throws cs_TwoFaIsActive
	 */
	public function assertNotActive():self {

		if ($this->two_fa->is_active) {
			throw new cs_TwoFaIsActive();
		}

		return $this;
	}

	/**
	 * получаем user_id пользователя, который логинится
	 *
	 */
	public function getUserId():int {

		return $this->two_fa->user_id;
	}

	/**
	 * проверяем, что текущий пользователь и пользователь которому выдали 2fa токен, совпадают
	 *
	 * @return $this
	 *
	 * @throws cs_TwoFaInvalidUser
	 */
	public function assertCorrectUser(int $user_id):self {

		if ($this->two_fa->user_id !== $user_id) {
			throw new cs_TwoFaInvalidUser();
		}

		return $this;
	}

	/**
	 * Обработать 2fa действие
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_AnswerCommand
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
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function handle(int $user_id, int $action_type, string|false $two_fa_key, int $company_id = 0):void {

		if ($two_fa_key) {

			try {

				// пробуем отвалидировать токен
				Domain_User_Action_TwoFa_Validate::do($user_id, $action_type, $two_fa_key, $company_id);

				return;
			} catch (cs_AnswerCommand) {

				// ничего не делаем
			}
		}

		self::_generateToken($user_id, $action_type, $company_id);
	}

	/**
	 * Пометить 2fa токен как неактивный
	 *
	 */
	public static function setTwoFaTokenAsInactive(int $user_id, string $two_fa_key):void {

		Domain_User_Action_TwoFa_SetAsInactive::do($user_id, $two_fa_key);
	}

	/**
	 * Сгенерировать 2fa токен и выбросить команду
	 *
	 * @throws cs_AnswerCommand
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _generateToken(int $user_id, int $action_type, int $company_id = 0):array {

		$two_fa_info = Domain_User_Action_TwoFa_Generate::do($user_id, $action_type, $company_id);

		$two_fa_output = Type_Pack_Main::replaceMapWithKeys([
			"two_fa_map"  => (string) $two_fa_info->two_fa_map,
			"action_type" => (int) $two_fa_info->action_type,
			"expire_at"   => (int) $two_fa_info->expires_at,
		]);

		throw new cs_AnswerCommand("need_confirm_2fa", $two_fa_output);
	}
}
