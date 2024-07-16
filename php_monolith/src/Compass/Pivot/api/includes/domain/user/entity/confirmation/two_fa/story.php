<?php

namespace Compass\Pivot;

/**
 * Класс для получения данных об истории 2fa действий
 *
 * Class Domain_User_Entity_Confirmation_TwoFa_Story
 */
class Domain_User_Entity_Confirmation_TwoFa_Story {

	public const NEXT_ATTEMPT_AFTER = 60 * 1;  // через сколько доступна пересылка смски

	public const RESEND_COUNT_LIMIT = 3; // лимит переотправки смс
	public const ERROR_COUNT_LIMIT  = 3; // лимит на кол-во ошибок

	protected Struct_Db_PivotAuth_TwoFa      $two_fa;
	protected Struct_Db_PivotAuth_TwoFaPhone $two_fa_phone;

	/**
	 * Domain_User_Entity_Confirmation_TwoFa_Story constructor.
	 *
	 */
	public function __construct(Struct_Db_PivotAuth_TwoFa $two_fa, Struct_Db_PivotAuth_TwoFaPhone $two_fa_phone) {

		$this->two_fa       = $two_fa;
		$this->two_fa_phone = $two_fa_phone;
	}

	/**
	 * получить по ключу 2fa
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_WrongTwoFaKey
	 */
	public static function getByMap(string $two_fa_map):self {

		try {

			$two_fa       = Gateway_Db_PivotAuth_TwoFaList::getOne($two_fa_map);
			$two_fa_phone = Gateway_Db_PivotAuth_TwoFaPhoneList::getOne($two_fa_map);
		} catch (\cs_RowIsEmpty) {
			throw new cs_WrongTwoFaKey();
		}

		return new self($two_fa, $two_fa_phone);
	}

	/**
	 * проверяем, истекла ли 2fa
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
	 * проверяем, не закончено ли действие
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
	 * проверяем, что 2fa ещё активна
	 *
	 * @return $this
	 *
	 * @throws cs_TwoFaIsNotActive
	 */
	public function assertActive():self {

		if (!$this->two_fa->is_active) {
			throw new cs_TwoFaIsNotActive();
		}

		return $this;
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
	 * проверяем, что переданная компания и компания в которую выдали 2fa токен, совпадают
	 *
	 * @return $this
	 *
	 * @throws cs_TwoFaInvalidCompany
	 */
	public function assertCorrectCompanyId(int $company_id):self {

		if ($this->two_fa->company_id !== $company_id) {
			throw new cs_TwoFaInvalidCompany();
		}

		return $this;
	}

	/**
	 * проверяем, что лимит ошибок не превышен
	 *
	 * @return $this
	 *
	 * @throws cs_ErrorCountLimitExceeded
	 */
	public function assertErrorCountLimitNotExceeded():self {

		if ($this->two_fa_phone->error_count >= self::ERROR_COUNT_LIMIT) {
			throw new cs_ErrorCountLimitExceeded($this->getTwoFaInfo()->expires_at);
		}

		return $this;
	}

	/**
	 * проверяем, подтверждён ли номер телефона
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneIsNotConfirmed
	 */
	public function assertPhoneConfirmed():self {

		if (!$this->two_fa_phone->is_success) {
			throw new cs_PhoneIsNotConfirmed();
		}

		return $this;
	}

	/**
	 * проверяем, что код совпадает
	 *
	 * @return $this
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 * @throws cs_WrongCode
	 */
	public function assertEqualCode(string $code):self {

		if (!Type_Hash_Code::compareHash($this->two_fa_phone->sms_code_hash, $code)) {

			throw new cs_WrongCode(
				$this->getAvailableAttempts() - 1,
				$this->getTwoFaInfo()->expires_at,
			);
		}

		return $this;
	}

	/**
	 * проверяем, что число переотправок не превышено
	 *
	 * @return $this
	 *
	 * @throws cs_ResendCodeCountLimitExceeded
	 */
	public function assertResendCountLimitNotExceeded():self {

		if ($this->two_fa_phone->resend_count >= self::RESEND_COUNT_LIMIT) {
			throw new cs_ResendCodeCountLimitExceeded();
		}

		return $this;
	}

	/**
	 * проверяем, что переотправка доступна
	 *
	 * @return $this
	 *
	 * @throws cs_ResendWillBeAvailableLater
	 */
	public function assertResendIsAvailable():self {

		if ($this->two_fa_phone->next_resend_at > time()) {
			throw new cs_ResendWillBeAvailableLater($this->getNextAttempt());
		}

		return $this;
	}

	/**
	 * проверяем, тип действия совпадает
	 *
	 * @return $this
	 *
	 * @throws cs_TwoFaTypeIsInvalid
	 */
	public function assertTypeIsValid(int $action_type):self {

		if ($this->two_fa->action_type > $action_type) {
			throw new cs_TwoFaTypeIsInvalid();
		}

		return $this;
	}

	/**
	 * получить данные об 2fa записи о телефоне
	 *
	 */
	public function getPhoneInfo():Struct_Db_PivotAuth_TwoFaPhone {

		return $this->two_fa_phone;
	}

	/**
	 * получить данные об 2fa
	 *
	 */
	public function getTwoFaInfo():Struct_Db_PivotAuth_TwoFa {

		return $this->two_fa;
	}

	/**
	 * получаем доступное кол-во попыток
	 *
	 */
	public function getAvailableAttempts():int {

		return self::ERROR_COUNT_LIMIT - $this->two_fa_phone->error_count;
	}

	/**
	 * получаем время следующей попытки
	 *
	 */
	public function getNextAttempt():int {

		if ($this->two_fa_phone->resend_count >= self::RESEND_COUNT_LIMIT) {
			return 0;
		}

		return $this->two_fa_phone->next_resend_at;
	}
}
