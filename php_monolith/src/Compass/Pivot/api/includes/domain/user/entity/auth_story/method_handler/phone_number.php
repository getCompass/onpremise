<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-хендлер для работы с сущностью аутентификации через номер телефона
 */
class Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber extends Domain_User_Entity_AuthStory_MethodHandler_Default {

	/** лимит на кол-во ошибок при вводе секрета на saas окружении */
	public const SAAS_ERROR_COUNT_LIMIT = 3;

	/** лимит на кол-во ошибок при вводе секрета на on-premise окружении */
	public const ON_PREMISE_ERROR_COUNT_LIMIT = 3;

	/** лимит переотправки секрета */
	public const RESEND_COUNT_LIMIT = 3;

	/** через сколько доступна переотправка секрета */
	public const NEXT_ATTEMPT_AFTER = 60;

	/** через сколько истекает попытка аутентификации */
	public const STORY_LIFE_TIME = 60 * 20;

	protected Struct_Db_PivotAuth_AuthDefault|Struct_Db_PivotAuth_AuthPhone $_auth_entity;

	public function __construct(
		Struct_Db_PivotAuth_AuthPhone $auth_phone,
	) {

		$this->_auth_entity = $auth_phone;
	}

	public function assertErrorCountLimitNotExceeded(int $error_count_limit):self {

		if ($this->_auth_entity->error_count >= $error_count_limit) {
			throw new Domain_User_Exception_AuthStory_ErrorCountLimitExceeded("error count limit exceeded");
		}

		return $this;
	}

	/**
	 * проверяем, что проверочный код
	 */
	public function assertEqualCode(string $confirm_codee, int $error_count_limit):void {

		if (!Type_Hash_Code::compareHash($this->_auth_entity->sms_code_hash, $confirm_codee)) {

			throw new cs_WrongCode(
				$this->getAvailableAttempts($error_count_limit) - 1,
				$this->_auth_entity->next_resend_at
			);
		}
	}

	/**
	 * проверяем, что число переотправок не превышено
	 */
	public function assertResendCountLimitNotExceeded():self {

		if ($this->_auth_entity->resend_count >= self::RESEND_COUNT_LIMIT) {
			throw new cs_ResendCodeCountLimitExceeded();
		}

		return $this;
	}

	/**
	 * проверяем, что переотправка доступна
	 *
	 * @return static
	 */
	public function assertResendIsAvailable():void {

		if ($this->_auth_entity->next_resend_at > time()) {
			throw new cs_ResendWillBeAvailableLater($this->_auth_entity->next_resend_at);
		}
	}

	/**
	 * получаем доступное кол-во попыток ввода секрета
	 */
	public function getAvailableAttempts(int $error_count_limit):int {

		return $error_count_limit - $this->_auth_entity->error_count;
	}

	/**
	 * получаем доступное кол-во попыток ввода секрета
	 */
	public function getAvailableResendCount():int {

		return static::RESEND_COUNT_LIMIT - $this->_auth_entity->resend_count;
	}

	/**
	 * получаем номер телефона аутентификации
	 *
	 * @return string
	 */
	public function getPhoneNumber():string {

		return $this->_auth_entity->phone_number;
	}

	/**
	 * получаем количество переотправок кода
	 *
	 * @return int
	 */
	public function getResendCount():int {

		return $this->_auth_entity->resend_count;
	}

	/**
	 * получаем количество неверного ввода кода
	 *
	 * @return int
	 */
	public function getErrorCount():int {

		return $this->_auth_entity->error_count;
	}

	/**
	 * получаем время создания попытки
	 *
	 * @return int
	 */
	public function getCreatedAt():int {

		return $this->_auth_entity->created_at;
	}

	/**
	 * Конвертируем сущность способа аутентификации в ассоц. массив
	 *
	 * @return array
	 */
	public function authEntityToArray():array {

		return (array) $this->_auth_entity;
	}

	/**
	 * получаем временную метку следующей переотправки секрета
	 * @return int
	 */
	public function getNextResendAt():int {

		if ($this->_auth_entity->resend_count >= static::RESEND_COUNT_LIMIT) {
			return 0;
		}

		return $this->_auth_entity->next_resend_at;
	}

	/**
	 * подготавливаем черновик auth_method_data
	 *
	 * @return array
	 */
	public static function prepareAuthMethodDataDraft(string $phone_number, string $sms_code_hash, string $sms_id):array {

		return (array) new Struct_Db_PivotAuth_AuthPhone(
			"", 0, 0, 0, time(), 0, time() + self::NEXT_ATTEMPT_AFTER, $sms_id, $sms_code_hash, $phone_number
		);
	}

	/**
	 * создаем сущность
	 */
	public function create():void {

		if (!Gateway_Db_PivotAuth_AuthPhoneList::inTransaction(Type_Pack_Auth::getShardId($this->_auth_entity->auth_map))) {
			throw new ParseFatalException("active transaction required");
		}

		Gateway_Db_PivotAuth_AuthPhoneList::insert($this->_auth_entity);
	}

	/**
	 * Обрабатываем успешное завершение аутентификации
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function handleSuccess(int $user_id, array $additional_update_field_list):static {

		// сюда запишем список всех полей, которые будем обновлять
		$update_field_list               = $additional_update_field_list;
		$update_field_list["is_success"] = 1;
		$update_field_list["updated_at"] = time();
		$this->_updateEntity($update_field_list);

		return $this;
	}

	/**
	 * Обрабатываем переотправку секрета
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws cs_IncorrectSaltVersion
	 */
	public function handleResend(string $cofirm_code, array $additional_update_field_list):static {

		$time = time();

		// сюда запишем список всех полей, которые будем обновлять
		$update_field_list                   = $additional_update_field_list;
		$update_field_list["resend_count"]   = $this->_auth_entity->resend_count + 1;
		$update_field_list["next_resend_at"] = $time + self::NEXT_ATTEMPT_AFTER;
		$update_field_list["sms_code_hash"]  = Type_Hash_Code::makeHash($cofirm_code);
		$update_field_list["updated_at"]     = $time;

		$this->_updateEntity($update_field_list);

		return $this;
	}

	/**
	 * Обрабатываем неверный ввод секрета
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public function handleWrongCode():static {

		$this->_updateEntity([
			"error_count" => $this->_auth_entity->error_count + 1,
			"updated_at"  => time(),
		]);

		return $this;
	}

	/**
	 * Обновляем свойства сущности, в том числе и в базе
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	protected function _updateEntity(array $update_field_list):static {

		$set = [];
		foreach ($update_field_list as $field => $value) {

			if (!property_exists(Struct_Db_PivotAuth_AuthPhone::class, $field)) {
				throw new ParseFatalException("attempt to set unknown field");
			}

			$this->_auth_entity->$field = $value;
			$set[$field]                = $value;
		}

		Gateway_Db_PivotAuth_AuthPhoneList::set($this->_auth_entity->auth_map, $set);

		return $this;
	}

	public function getAuthParameter():string {

		return $this->getPhoneNumber();
	}

	public function getAuthMap():string {

		return $this->_auth_entity->auth_map;
	}

	public function getSmsID():string {

		return $this->_auth_entity->sms_id;
	}
}