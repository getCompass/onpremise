<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-хендлер для работы с сущностью аутентификации через почту
 */
class Domain_User_Entity_AuthStory_MethodHandler_Mail extends Domain_User_Entity_AuthStory_MethodHandler_Default {

	/** лимит на кол-во ошибок при вводе пароля на on-premise окружении */
	public const ON_PREMISE_PASSWORD_ERROR_COUNT = 7;

	/** лимит на кол-во ошибок при вводе кода на on-premise окружении */
	public const ON_PREMISE_CODE_ERROR_COUNT = 7;

	/** лимит на кол-во переотправок проверочного кода на on-premise окружении */
	public const ON_PREMISE_CODE_RESEND_COUNT = 7;

	/** через сколько доступна переотправка кода */
	public const NEXT_ATTEMPT_AFTER = 60;

	/** через сколько истекает попытка аутентификации */
	public const STORY_LIFE_TIME = 60 * 20;

	public const STAGE_ENTERING_PASSWORD = "entering_password";
	public const STAGE_ENTERING_CODE     = "entering_code";
	public const STAGE_FINISHED          = "finished";

	protected Struct_Db_PivotAuth_AuthDefault|Struct_Db_PivotAuth_AuthMail $_auth_entity;

	public function __construct(
		Struct_Db_PivotAuth_AuthMail $auth_mail,
	) {

		$this->_auth_entity = $auth_mail;
	}

	/**
	 * проверяем, достигнут ли лимит ввода неверного кода
	 *
	 * @return $this
	 * @throws Domain_User_Exception_AuthStory_ErrorCountLimitExceeded
	 */
	public function assertCodeErrorCountLimitNotExceeded():self {

		if ($this->_auth_entity->code_error_count >= self::ON_PREMISE_CODE_ERROR_COUNT) {
			throw new Domain_User_Exception_AuthStory_ErrorCountLimitExceeded("code error count limit exceeded");
		}

		return $this;
	}

	/**
	 * проверяем, что передан корректный проверочный код
	 */
	public function assertEqualCode(string $confirm_code):void {

		if (!Type_Hash_Code::compareHash($this->_auth_entity->code_hash, $confirm_code)) {

			throw new cs_WrongCode(
				$this->getAvailableCodeEnteringAttempts() - 1,
				$this->_auth_entity->next_resend_at
			);
		}
	}

	/**
	 * проверяем, что число переотправок не превышено
	 */
	public function assertResendCountLimitNotExceeded():self {

		if ($this->_auth_entity->resend_count >= self::ON_PREMISE_CODE_RESEND_COUNT) {
			throw new cs_ResendCodeCountLimitExceeded();
		}

		return $this;
	}

	/**
	 * доступна ли переотправка кода
	 *
	 * @return bool
	 */
	public function resendIsAvailable():bool {

		return $this->_auth_entity->next_resend_at <= time() && $this->_auth_entity->resend_count < self::ON_PREMISE_CODE_RESEND_COUNT;
	}

	/**
	 * проверяем, что переотправка доступна
	 *
	 * @return static
	 */
	public function assertResendIsAvailable():void {

		if (!$this->resendIsAvailable()) {
			throw new cs_ResendWillBeAvailableLater($this->getNextResendAt());
		}
	}

	/**
	 * проверяем, имеется ли доступ к этапу ввода проверочного кода
	 *
	 * @throws Domain_User_Exception_AuthStory_StageNotAllowed
	 */
	public function assertAccessEnteringCodeStage():self {

		if ($this->_auth_entity->has_password !== 1 || $this->_auth_entity->has_code === 1) {
			throw new Domain_User_Exception_AuthStory_StageNotAllowed();
		}

		return $this;
	}

	/**
	 * проверяем, был ли ранее успешно введен код
	 *
	 * @throws Domain_User_Exception_AuthStory_StageNotAllowed
	 */
	public function assertHasCode():self {

		if ($this->_auth_entity->has_code !== 1) {
			throw new Domain_User_Exception_AuthStory_StageNotAllowed();
		}

		return $this;
	}

	/**
	 * получаем доступное кол-во попыток ввода кода
	 */
	public function getAvailableCodeEnteringAttempts():int {

		return static::ON_PREMISE_CODE_ERROR_COUNT - $this->_auth_entity->code_error_count;
	}

	/**
	 * получаем доступное кол-во попыток ввода пароля
	 */
	public function getAvailablePasswordEnteringAttempts():int {

		return static::ON_PREMISE_PASSWORD_ERROR_COUNT - $this->_auth_entity->password_error_count;
	}

	/**
	 * получаем доступное кол-во переотправки кода
	 */
	public function getAvailableResendCount():int {

		return static::ON_PREMISE_CODE_RESEND_COUNT - $this->_auth_entity->resend_count;
	}

	/**
	 * получаем кол-во ошибок при ввода пароля
	 *
	 * @return int
	 */
	public function getPasswordErrorCount():int {

		return $this->_auth_entity->password_error_count;
	}

	/**
	 * получаем почту аутентификации
	 *
	 * @return string
	 */
	public function getMail():string {

		return $this->_auth_entity->mail;
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
	 * получаем временную метку следующей переотправки кода
	 * @return int
	 */
	public function getNextResendAt():int {

		if ($this->_auth_entity->resend_count >= static::ON_PREMISE_CODE_RESEND_COUNT) {
			return 0;
		}

		return $this->_auth_entity->next_resend_at;
	}

	/**
	 * получаем кол-во переотправок кода
	 * @return int
	 */
	public function getResendCount():int {

		return $this->_auth_entity->resend_count;
	}

	/**
	 * подготавливаем черновик Struct_Db_PivotAuth_AuthMail
	 *
	 * @return array
	 */
	public static function prepareAuthMailDataDraft(string $mail):array {

		return (array) new Struct_Db_PivotAuth_AuthMail(
			auth_map: "",
			is_success: 0,
			has_password: 0,
			has_code: 0,
			resend_count: 0,
			password_error_count: 0,
			code_error_count: 0,
			created_at: time(),
			updated_at: 0,
			next_resend_at: time() + self::NEXT_ATTEMPT_AFTER,
			message_id: "",
			code_hash: "",
			mail: $mail,
		);
	}

	/**
	 * подготавливаем черновик Struct_Db_PivotAuth_AuthMail с проверочным кодом
	 *
	 * @return array
	 */
	public static function prepareAuthMailDataWithConfirmCodeDraft(string $mail, string $confirm_code, string $mail_id):array {

		return (array) new Struct_Db_PivotAuth_AuthMail(
			auth_map: "",
			is_success: 0,
			has_password: 0,
			has_code: 0,
			resend_count: 0,
			password_error_count: 0,
			code_error_count: 0,
			created_at: time(),
			updated_at: 0,
			next_resend_at: time() + self::NEXT_ATTEMPT_AFTER,
			message_id: $mail_id,
			code_hash: Type_Hash_Code::makeHash($confirm_code),
			mail: $mail,
		);
	}

	/**
	 * создаем сущность
	 */
	public function create():void {

		if (!Gateway_Db_PivotAuth_AuthMailList::inTransaction(Type_Pack_Auth::getShardId($this->_auth_entity->auth_map))) {
			throw new ParseFatalException("active transaction required");
		}

		Gateway_Db_PivotAuth_AuthMailList::insert($this->_auth_entity);
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
	 * обрабатываем успешный ввод пароля
	 *
	 * @return $this
	 */
	public function handleSuccessPassword(array $additional_update_field_list):static {

		// сюда запишем список всех полей, которые будем обновлять
		$update_field_list                 = $additional_update_field_list;
		$update_field_list["has_password"] = 1;
		$update_field_list["updated_at"]   = time();

		$this->_updateEntity($update_field_list);

		return $this;
	}

	/**
	 * обрабатываем успешный ввод кода
	 *
	 * @return $this
	 */
	public function handleSuccessCode():static {

		$this->_updateEntity([
			"has_code"   => 1,
			"updated_at" => time(),
		]);

		return $this;
	}

	/**
	 * Обрабатываем неверный ввод пароля
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public function handleWrongPassword():static {

		$this->_updateEntity([
			"password_error_count" => $this->_auth_entity->password_error_count + 1,
			"updated_at"           => time(),
		]);

		return $this;
	}

	/**
	 * Обрабатываем неверный ввод кода
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public function handleWrongCode():static {

		$this->_updateEntity([
			"code_error_count" => $this->_auth_entity->code_error_count + 1,
			"updated_at"       => time(),
		]);

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
	public function handleResend(string $confirm_code, string $mail_id):static {

		$time = time();
		$this->_updateEntity([
			"resend_count"   => $this->_auth_entity->resend_count + 1,
			"next_resend_at" => $time + self::NEXT_ATTEMPT_AFTER,
			"message_id"     => $mail_id,
			"code_hash"      => Type_Hash_Code::makeHash($confirm_code),
			"updated_at"     => $time,
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

			if (!property_exists(Struct_Db_PivotAuth_AuthMail::class, $field)) {
				throw new ParseFatalException("attempt to set unknown field");
			}

			$this->_auth_entity->$field = $value;
			$set[$field]                = $value;
		}

		Gateway_Db_PivotAuth_AuthMailList::set($this->_auth_entity->auth_map, $set);

		return $this;
	}

	/**
	 * получаем параметр аутентификации с помощью которого была начата попытка
	 *
	 * для аутентификации через телефон – это номер
	 * для аутентификации через почту – это адрес почты
	 *
	 * @return string
	 */
	public function getAuthParameter():string {

		return $this->getMail();
	}

	/**
	 * получить map идентификатор аутентификации
	 *
	 * @return string
	 */
	public function getAuthMap():string {

		return $this->_auth_entity->auth_map;
	}

	/**
	 * был ли отправлен проверочный код
	 *
	 * @return bool
	 */
	public function wasConfirmCodeSent():bool {

		return mb_strlen($this->_auth_entity->code_hash) > 0;
	}

	/**
	 * определяем этап аутентификации
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public function resolveStage(int $auth_type):string {

		// если это регистрация/логин по почте
		if (in_array($auth_type, [Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL])) {

			if ($this->_auth_entity->has_password === 0) {
				return self::STAGE_ENTERING_PASSWORD;
			}

			if ($this->_auth_entity->is_success === 0 && $this->_auth_entity->has_code === 0) {
				return self::STAGE_ENTERING_CODE;
			}

			return self::STAGE_FINISHED;
		}

		if ($auth_type === Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL) {

			if ($this->_auth_entity->has_code === 0) {
				return self::STAGE_ENTERING_CODE;
			}

			if ($this->_auth_entity->has_password === 0) {
				return self::STAGE_ENTERING_PASSWORD;
			}

			return self::STAGE_FINISHED;
		}

		throw new ParseFatalException("unexpected auth type [$auth_type]");
	}
}