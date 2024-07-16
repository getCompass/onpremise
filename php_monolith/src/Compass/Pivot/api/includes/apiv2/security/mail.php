<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Class Apiv2_Security_Mail
 */
class Apiv2_Security_Mail extends \BaseFrame\Controller\Api {

	public const ECODE_AUTH_MAIL_DISABLED         = 1217001; // отключена на сервере авторизация по почте в конфиге
	public const ECODE_INCORRECT_MAIL             = 1217002; // передан некорректный параметр mail
	public const ECODE_NOT_ALLOWED_MAIL           = 1217003; // передан mail не из списка разрешенных на сервере
	public const ECODE_ALREADY_TAKEN_MAIL         = 1217004; // данная почта уже занята
	public const ECODE_NOT_EXIST_MAIL             = 1217005; // у пользователя не установлена почта
	public const ECODE_SCENARIO_NOT_ALLOWED       = 1217006; // сценарий недоступен на сервере
	public const ECODE_INCORRECT_CONFIRM_CODE     = 1217007; // введен некорректный проверочный код
	public const ECODE_LIMIT_CONFIRM_CODE         = 1217008; // превышено количество ввода попыток проверочного кода
	public const ECODE_RESEND_CODE_NOT_AVAILABLE  = 1217009; // переотправка недоступна по времени
	public const ECODE_INCORRECT_PASSWORD         = 1217010; // введен неверный текущий пароль
	public const ECODE_INCORRECT_PASSWORD_NEW     = 1217011; // введен некорректный новый пароль
	public const ECODE_STORY_NOT_ACTIVE           = 1217012; // время жизни процесса истекло
	public const ECODE_USER_EXIST_MAIL            = 1217013; // у пользователя уже установлена почта
	public const ECODE_NO_ACTIVE_PROCESS          = 1217014; // процесс не активный
	public const ECODE_ERROR_COUNT_EXCEPTION      = 1217015; // превышено число ошибок
	public const ECODE_RESEND_COUNT_EXCEPTION     = 1217016; // превышено количество попыток переотправки
	public const ECODE_INCORRECT_STAGE_PROCESS    = 1217017; // вызов некорректной стадии процесса
	public const ECODE_USER_WAS_REGISTERED_BY_SSO = 1217018; // пользователь был зарегистрирован через SSO, действие запрещено

	public const ALLOW_METHODS = [
		"changePassword",
		"tryResetPassword",
		"confirmResetPassword",
		"finishResetPassword",
		"add",
		"setPasswordOnShortAdd",
		"setPasswordOnFullAdd",
		"confirmCodeOnFullAdd",
		"resendCode",
		"change",
		"setOnShortChange",
		"confirmOldByCodeOnFullChange",
		"setOnFullChange",
		"confirmNewByCodeOnFullChange",
		"confirmMailPasswordStory",
	];

	##########################################################
	# region - смена пароля
	##########################################################

	/**
	 * Меняем пароль
	 *
	 * @return array
	 * @throws BlockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \queryException
	 * @throws cs_blockException|ParseFatalException
	 */
	public function changePassword():array {

		$password     = $this->post(\Formatter::TYPE_STRING, "password");
		$password_new = $this->post(\Formatter::TYPE_STRING, "password_new");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::MAIL_CHANGE_PASSWORD);

		try {
			Domain_User_Scenario_Api_Security_Mail::changePassword($this->user_id, $this->session_uniq, $password, $password_new);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_Password_Mismatch|Domain_User_Exception_Password_Incorrect) {
			throw new CaseException(static::ECODE_INCORRECT_PASSWORD, "incorrect password");
		} catch (Domain_User_Exception_Password_IncorrectNew) {
			throw new CaseException(static::ECODE_INCORRECT_PASSWORD_NEW, "incorrect password new");
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("incorrect authorized user");
		}

		return $this->ok();
	}

	##########################################################
	# region - сброс пароля
	##########################################################

	/**
	 * Метод для начала процесса сброса пароля
	 *
	 * @throws BlockException
	 * @throws CaseException
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_blockException
	 */
	public function tryResetPassword():array {

		// проверяем блокировку по user_id
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::MAIL_TRY_RESET_PASSWORD);

		try {
			[$story, $code_story] = Domain_User_Scenario_Api_Security_Mail::tryResetPassword($this->user_id, $this->session_uniq);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_Password_ErrorCountLimitExceeded $e) {

			throw new CaseException(self::ECODE_ERROR_COUNT_EXCEPTION, "reset code error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		}

		return $this->ok(Apiv2_Format::mailResetPassword($story, $code_story));
	}

	/**
	 * Метод подтверждения сброса пароля кодом подтверждения с почты
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public function confirmResetPassword():array {

		$code                    = $this->post(\Formatter::TYPE_STRING, "code");
		$password_mail_story_key = $this->post(\Formatter::TYPE_STRING, "password_mail_story_key");

		try {
			$password_mail_story_map = Type_Pack_Main::replaceKeyWithMap("password_mail_story_key", $password_mail_story_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {
			Domain_User_Scenario_Api_Security_Mail::confirmResetPassword($this->user_id, $this->session_uniq, $code, $password_mail_story_map);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Password_WrongMap|Domain_User_Exception_Password_StoryIsSuccess|
		Domain_User_Exception_Password_NotFound|Domain_User_Exception_Password_StoryIsNotActive) {
			throw new CaseException(static::ECODE_NO_ACTIVE_PROCESS, "user don't have active reset process");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_Password_StoryIsExpired) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "time to confirm code expired");
		} catch (cs_WrongCode $e) {

			throw new CaseException(static::ECODE_INCORRECT_CONFIRM_CODE, "incorrect code", [
				"code_available_attempts" => $e->getAvailableAttempts(),
				"next_attempt"            => $e->getNextAttempt(),
			]);
		} catch (Domain_User_Exception_Password_ErrorCountLimitExceeded $e) {

			throw new CaseException(self::ECODE_ERROR_COUNT_EXCEPTION, "reset code error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_InvalidConfirmCode) {
			throw new ParamException("incorrect code validation");
		}

		return $this->ok();
	}

	/**
	 * Метод задания нового пароля для завершения процесса сброса пароля
	 *
	 * @throws CaseException
	 * @throws ParamException
	 */
	public function finishResetPassword():array {

		$password                = $this->post(\Formatter::TYPE_STRING, "password");
		$password_mail_story_key = $this->post(\Formatter::TYPE_STRING, "password_mail_story_key");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::MAIL_FINISH_RESET_PASSWORD);

		try {
			$password_mail_story_map = Type_Pack_Main::replaceKeyWithMap("password_mail_story_key", $password_mail_story_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {
			Domain_User_Scenario_Api_Security_Mail::finishResetPassword($this->user_id, $this->session_uniq, $password, $password_mail_story_map);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_Password_StoryIsExpired) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "story expired");
		} catch (Domain_User_Exception_Password_WrongMap|Domain_User_Exception_Password_StoryIsSuccess|
		Domain_User_Exception_Password_NotFound|Domain_User_Exception_Password_StoryIsNotActive|Domain_User_Exception_Password_StageNotAllowed) {
			throw new CaseException(static::ECODE_NO_ACTIVE_PROCESS, "user don't have active reset process");
		} catch (Domain_User_Exception_Password_Incorrect) {
			throw new CaseException(static::ECODE_INCORRECT_PASSWORD_NEW, "incorrect password");
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("incorrect password validation");
		}

		return $this->ok();
	}

	/**
	 * Подтвердить действие паролем от почты
	 *
	 * @return array
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @long
	 */
	public function confirmMailPasswordStory():array {

		$password                        = $this->post(\Formatter::TYPE_STRING, "password");
		$confirm_mail_password_story_key = $this->post(\Formatter::TYPE_STRING, "confirm_mail_password_story_key");

		try {
			$confirm_mail_password_story_map = Type_Pack_Main::replaceKeyWithMap("confirm_mail_password_story_key", $confirm_mail_password_story_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {
			Domain_User_Scenario_Api_Security_Mail::confirmMailPasswordStory($this->user_id, $password, $confirm_mail_password_story_map);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_Confirmation_Mail_IsExpired|Domain_User_Exception_Confirmation_Mail_IsNotActive|
		Domain_User_Exception_Confirmation_Mail_IsConfirmed) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "story expired");
		} catch (Domain_User_Exception_Confirmation_Mail_WrongPassword) {
			throw new CaseException(static::ECODE_INCORRECT_PASSWORD_NEW, "incorrect password");
		} catch (Domain_User_Exception_Confirmation_Mail_InvalidMailPasswordStoryKey|\cs_RowIsEmpty) {
			throw new ParamException("incorrect story key");
		} catch (Domain_User_Exception_Confirmation_Mail_ErrorCountExceeded $e) {

			throw new CaseException(self::ECODE_ERROR_COUNT_EXCEPTION, "confirm code error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		}

		return $this->ok();
	}

	##########################################################
	# region - смена почты
	##########################################################

	/**
	 * Метод старта процесса смены почты
	 *
	 * @throws BlockException
	 * @throws CaseException
	 * @throws \queryException
	 * @throws cs_blockException
	 */
	public function change():array {

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::MAIL_CHANGE);

		try {
			[$story, $code_story, $scenario] = Domain_User_Scenario_Api_Security_Mail::change($this->user_id, $this->session_uniq);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_Security_UserWasRegisteredBySso) {
			throw new CaseException(static::ECODE_USER_WAS_REGISTERED_BY_SSO, "user was registered by sso, action is not allowed");
		}

		return $this->ok(Apiv2_Format::changeMail($story, $code_story, $scenario));
	}

	/**
	 * Метод добавление нового адреса почты в процессе смены почты по короткому сценарию
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @long большой try-catch
	 */
	public function setOnShortChange():array {

		$change_mail_story_key = $this->post(\Formatter::TYPE_STRING, "change_mail_story_key");
		$mail                  = $this->post(\Formatter::TYPE_STRING, "mail");

		try {
			$change_mail_story_map = Type_Pack_Main::replaceKeyWithMap("change_mail_story_key", $change_mail_story_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {
			$mail = Domain_User_Scenario_Api_Security_Mail::setOnShortChange($this->user_id, $this->session_uniq, $change_mail_story_map, $mail);
		} catch (Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound|Domain_User_Exception_Security_Mail_Change_StoryIsSuccess
		|Domain_User_Exception_Security_Mail_Change_StoryNotFound) {
			throw new CaseException(static::ECODE_NO_ACTIVE_PROCESS, "user don't have active reset process");
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Security_Mail_Change_WrongStage) {
			throw new CaseException(static::ECODE_INCORRECT_STAGE_PROCESS, "incorrect stage process");
		} catch (Domain_User_Exception_Mail_ScenarioNotAllowed) {
			throw new CaseException(static::ECODE_SCENARIO_NOT_ALLOWED, "scenario not allowed");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_AuthStory_Mail_DomainNotAllowed $e) {
			throw new CaseException(static::ECODE_NOT_ALLOWED_MAIL, "mail not allowed", ["mail_domain_list" => $e->getAllowedDomainList()]);
		} catch (Domain_User_Exception_Security_Mail_Change_StoryIsExpired) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "time to confirm code expired");
		} catch (InvalidMail) {
			throw new CaseException(static::ECODE_INCORRECT_MAIL, "incorrect mail");
		} catch (Domain_User_Exception_Mail_IsTaken|Domain_User_Exception_Mail_Binding) {
			throw new CaseException(static::ECODE_ALREADY_TAKEN_MAIL, "mail is busy");
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("incorrect authorized user");
		}

		return $this->ok([
			"mail_mask" => (string) Domain_User_Entity_Mail::getMailMask($mail),
		]);
	}

	/**
	 * Метод подтверждение смены почты кодом на прошлую почту
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @long большой try-catch
	 */
	public function confirmOldByCodeOnFullChange():array {

		$change_mail_story_key = $this->post(\Formatter::TYPE_STRING, "change_mail_story_key");
		$code                  = $this->post(\Formatter::TYPE_STRING, "code");

		try {
			$change_mail_story_map = Type_Pack_Main::replaceKeyWithMap("change_mail_story_key", $change_mail_story_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {
			Domain_User_Scenario_Api_Security_Mail::confirmOldByCodeOnFullChange($this->user_id, $this->session_uniq, $change_mail_story_map, $code);
		} catch (Domain_User_Exception_Security_Mail_Change_StoryNotFound|Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
		|Domain_User_Exception_Security_Mail_Change_StoryIsSuccess) {
			throw new CaseException(static::ECODE_NO_ACTIVE_PROCESS, "user don't have active reset process");
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Security_Mail_Change_WrongStage) {
			throw new CaseException(static::ECODE_INCORRECT_STAGE_PROCESS, "incorrect stage process");
		} catch (Domain_User_Exception_Mail_ScenarioNotAllowed) {
			throw new CaseException(static::ECODE_SCENARIO_NOT_ALLOWED, "scenario not allowed");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_Security_Mail_Change_StoryIsExpired) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "time to confirm code expired");
		} catch (Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded $e) {

			throw new CaseException(self::ECODE_ERROR_COUNT_EXCEPTION, "reset code error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_WrongCode $e) {

			throw new CaseException(static::ECODE_INCORRECT_CONFIRM_CODE, "incorrect code", [
				"code_available_attempts" => $e->getAvailableAttempts(),
				"next_attempt"            => $e->getNextAttempt(),
			]);
		}

		return $this->ok();
	}

	/**
	 * Метод добавление нового адреса почты в процессе смены почты
	 *
	 * @throws CaseException
	 * @throws \queryException
	 * @long большой try-catch
	 */
	public function setOnFullChange():array {

		$change_mail_story_key = $this->post(\Formatter::TYPE_STRING, "change_mail_story_key");
		$mail                  = $this->post(\Formatter::TYPE_STRING, "mail");

		try {
			$change_mail_story_map = Type_Pack_Main::replaceKeyWithMap("change_mail_story_key", $change_mail_story_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {

			[$story, $code_story, $scenario] = Domain_User_Scenario_Api_Security_Mail::setOnFullChange(
				$this->user_id, $this->session_uniq, $change_mail_story_map, $mail
			);
		} catch (Domain_User_Exception_Security_Mail_Change_StoryNotFound|Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
		|Domain_User_Exception_Security_Mail_Change_StoryIsSuccess) {
			throw new CaseException(static::ECODE_NO_ACTIVE_PROCESS, "user don't have active reset process");
		} catch (Domain_User_Exception_Security_Mail_Change_WrongStage|Domain_User_Exception_Security_Mail_Change_CodeStoryIsNotConfirmStage) {
			throw new CaseException(static::ECODE_INCORRECT_STAGE_PROCESS, "incorrect stage process");
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_ScenarioNotAllowed) {
			throw new CaseException(static::ECODE_SCENARIO_NOT_ALLOWED, "scenario not allowed");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_AuthStory_Mail_DomainNotAllowed $e) {
			throw new CaseException(static::ECODE_NOT_ALLOWED_MAIL, "mail not allowed", ["mail_domain_list" => $e->getAllowedDomainList()]);
		} catch (Domain_User_Exception_Security_Mail_Change_StoryIsExpired) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "time to confirm code expired");
		} catch (InvalidMail) {
			throw new CaseException(static::ECODE_INCORRECT_MAIL, "incorrect mail");
		} catch (Domain_User_Exception_Mail_IsTaken) {
			throw new CaseException(static::ECODE_ALREADY_TAKEN_MAIL, "mail is busy");
		}

		return $this->ok(Apiv2_Format::changeMail($story, $code_story, $scenario));
	}

	/**
	 * Метод добавление нового адреса почты в процессе смены почты
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @long большой try-catch
	 */
	public function confirmNewByCodeOnFullChange():array {

		$change_mail_story_key = $this->post(\Formatter::TYPE_STRING, "change_mail_story_key");
		$code                  = $this->post(\Formatter::TYPE_STRING, "code");

		try {
			$change_mail_story_map = Type_Pack_Main::replaceKeyWithMap("change_mail_story_key", $change_mail_story_key);
		} catch (\cs_DecryptHasFailed|cs_UnknownKeyType) {
			throw new ParamException("incorrect key");
		}

		try {
			$mail = Domain_User_Scenario_Api_Security_Mail::confirmNewByCodeOnFullChange($this->user_id, $this->session_uniq, $change_mail_story_map, $code);
		} catch (Domain_User_Exception_Security_Mail_Change_StoryIsSuccess|Domain_User_Exception_Security_Mail_Change_StoryNotFound|cs_CacheIsEmpty) {
			throw new CaseException(static::ECODE_NO_ACTIVE_PROCESS, "user don't have active reset process");
		} catch (Domain_User_Exception_Security_Mail_Change_WrongStage) {
			throw new CaseException(static::ECODE_INCORRECT_STAGE_PROCESS, "incorrect stage process");
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_ScenarioNotAllowed) {
			throw new CaseException(static::ECODE_SCENARIO_NOT_ALLOWED, "scenario not allowed");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user don't have mail");
		} catch (Domain_User_Exception_Security_Mail_Change_StoryIsExpired) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "time to confirm code expired");
		} catch (Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded $e) {

			throw new CaseException(self::ECODE_ERROR_COUNT_EXCEPTION, "reset code error count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (cs_WrongCode $e) {

			throw new CaseException(static::ECODE_INCORRECT_CONFIRM_CODE, "incorrect code", [
				"code_available_attempts" => $e->getAvailableAttempts(),
				"next_attempt"            => $e->getNextAttempt(),
			]);
		} catch (Domain_User_Exception_Mail_IsTaken|Domain_User_Exception_Mail_Binding) {
			throw new CaseException(static::ECODE_ALREADY_TAKEN_MAIL, "mail is busy");
		} catch (InvalidMail) {
			throw new CaseException(static::ECODE_INCORRECT_MAIL, "incorrect mail");
		}

		return $this->ok([
			"mail_mask" => (string) Domain_User_Entity_Mail::getMailMask($mail),
		]);
	}

	##########################################################
	# region - добавление почты
	##########################################################

	/**
	 * добавляем почту (начало процесса)
	 *
	 * @return array
	 * @throws BlockException
	 * @throws CaseException
	 * @throws \queryException
	 * @throws cs_blockException
	 */
	public function add():array {

		$mail = $this->post(\Formatter::TYPE_STRING, "mail");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::MAIL_ADD);

		try {
			[$story, $code_story, $scenario, $stage] = Domain_User_Scenario_Api_Security_Mail::add($this->user_id, $this->session_uniq, $mail);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (InvalidMail) {
			throw new CaseException(static::ECODE_INCORRECT_MAIL, "incorrect mail");
		} catch (Domain_User_Exception_AuthStory_Mail_DomainNotAllowed $e) {
			throw new CaseException(static::ECODE_NOT_ALLOWED_MAIL, "mail not allowed", ["mail_domain_list" => $e->getAllowedDomainList()]);
		} catch (Domain_User_Exception_Mail_IsTaken) {
			throw new CaseException(static::ECODE_ALREADY_TAKEN_MAIL, "mail is busy");
		} catch (Domain_User_Exception_Mail_AlreadyExist) {
			throw new CaseException(static::ECODE_USER_EXIST_MAIL, "mail is exist in user");
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("incorrect authorized user");
		} catch (Domain_User_Exception_Security_UserWasRegisteredBySso) {
			throw new CaseException(static::ECODE_USER_WAS_REGISTERED_BY_SSO, "user was registered by sso, action is not allowed");
		}

		return $this->ok(Apiv2_Format::addMail($story, $code_story, $scenario, $stage));
	}

	/**
	 * устанавливаем пароль при добавлении почты (короткий сценарий - завершение процесса добавления почты)
	 *
	 * @return array
	 * @throws BlockException
	 * @throws CaseException
	 * @throws cs_blockException
	 *
	 * @long большой try-catch
	 */
	public function setPasswordOnShortAdd():array {

		$add_mail_story_key = $this->post(\Formatter::TYPE_STRING, "add_mail_story_key");
		$password           = $this->post(\Formatter::TYPE_STRING, "password");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::MAIL_ADD_ON_SET_PASSWORD);

		try {
			$mail = Domain_User_Scenario_Api_Security_Mail::setPasswordOnShortAdd($this->user_id, $add_mail_story_key, $password);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_ScenarioNotAllowed) {
			throw new CaseException(static::ECODE_SCENARIO_NOT_ALLOWED, "scenario not allowed");
		} catch (Domain_User_Exception_AuthStory_Mail_DomainNotAllowed $e) {
			throw new CaseException(static::ECODE_NOT_ALLOWED_MAIL, "mail not allowed", ["mail_domain_list" => $e->getAllowedDomainList()]);
		} catch (Domain_User_Exception_Mail_IsTaken) {
			throw new CaseException(static::ECODE_ALREADY_TAKEN_MAIL, "mail is busy");
		} catch (Domain_User_Exception_Mail_AlreadyExist) {
			throw new CaseException(static::ECODE_USER_EXIST_MAIL, "mail is exist in user");
		} catch (Domain_User_Exception_Password_Incorrect) {
			throw new CaseException(static::ECODE_INCORRECT_PASSWORD_NEW, "incorrect password");
		} catch (Domain_User_Exception_Mail_StoryIsNotActive|Domain_User_Exception_Mail_StoryIsExpired|Domain_User_Exception_Mail_StoryIsNotActive
		|Domain_User_Exception_Mail_StoryIsSuccess|Domain_User_Exception_Mail_StoryNotFound) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "story not active");
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("incorrect authorized user");
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("incorrect add_mail_story_key");
		}

		return $this->ok([
			"mail_mask" => (string) Domain_User_Entity_Mail::getMailMask($mail),
		]);
	}

	/**
	 * устанавливаем пароль при добавлении почты (полный сценарий)
	 *
	 * @return array
	 * @throws BlockException
	 * @throws CaseException
	 * @throws \queryException
	 * @throws cs_blockException
	 *
	 * @long большой try-catch
	 */
	public function setPasswordOnFullAdd():array {

		$add_mail_story_key = $this->post(\Formatter::TYPE_STRING, "add_mail_story_key");
		$password           = $this->post(\Formatter::TYPE_STRING, "password");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::MAIL_ADD_ON_SET_PASSWORD);

		try {
			[$story, $code_story, $scenario, $stage] = Domain_User_Scenario_Api_Security_Mail::setPasswordOnFullAdd($this->user_id, $add_mail_story_key, $password);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_ScenarioNotAllowed) {
			throw new CaseException(static::ECODE_SCENARIO_NOT_ALLOWED, "scenario not allowed");
		} catch (Domain_User_Exception_AuthStory_Mail_DomainNotAllowed $e) {

			throw new CaseException(static::ECODE_NOT_ALLOWED_MAIL, "mail not allowed", [
					"mail_domain_list" => (int) $e->getAllowedDomainList(),
				]
			);
		} catch (Domain_User_Exception_Mail_IsTaken) {
			throw new CaseException(static::ECODE_ALREADY_TAKEN_MAIL, "mail is busy");
		} catch (Domain_User_Exception_Mail_AlreadyExist) {
			throw new CaseException(static::ECODE_USER_EXIST_MAIL, "mail is exist in user");
		} catch (Domain_User_Exception_Password_Incorrect) {
			throw new CaseException(static::ECODE_INCORRECT_PASSWORD_NEW, "incorrect password");
		} catch (Domain_User_Exception_Mail_StoryIsNotActive|Domain_User_Exception_Mail_StoryIsExpired|Domain_User_Exception_Mail_StoryIsNotActive
		|Domain_User_Exception_Mail_StoryIsSuccess|Domain_User_Exception_Mail_StoryNotFound) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "story not active");
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("incorrect authorized user");
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("incorrect add_mail_story_key");
		}

		return $this->ok(Apiv2_Format::addMail($story, $code_story, $scenario, $stage));
	}

	/**
	 * подтверждаем проверочным кодом добавлении почты (полный сценарий - завершение процесса добавления почты)
	 *
	 * @return array
	 * @throws BlockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws cs_blockException
	 *
	 * @long большой try-catch
	 */
	public function confirmCodeOnFullAdd():array {

		$add_mail_story_key = $this->post(\Formatter::TYPE_STRING, "add_mail_story_key");
		$code               = $this->post(\Formatter::TYPE_STRING, "code");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::MAIL_ADD_ON_CONFIRM_CODE);

		try {
			$mail = Domain_User_Scenario_Api_Security_Mail::confirmCodeOnFullAdd($this->user_id, $add_mail_story_key, $code);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_ScenarioNotAllowed) {
			throw new CaseException(static::ECODE_SCENARIO_NOT_ALLOWED, "scenario not allowed");
		} catch (Domain_User_Exception_AuthStory_Mail_DomainNotAllowed $e) {
			throw new CaseException(static::ECODE_NOT_ALLOWED_MAIL, "mail not allowed", ["mail_domain_list" => $e->getAllowedDomainList()]);
		} catch (Domain_User_Exception_Mail_IsTaken) {
			throw new CaseException(static::ECODE_ALREADY_TAKEN_MAIL, "mail is busy");
		} catch (Domain_User_Exception_Mail_AlreadyExist) {
			throw new CaseException(static::ECODE_USER_EXIST_MAIL, "mail is exist in user");
		} catch (cs_WrongCode $e) {

			throw new CaseException(static::ECODE_INCORRECT_CONFIRM_CODE, "incorrect confirm code", [
				"code_available_attempts" => (int) $e->getAvailableAttempts(),
				"next_attempt"            => (int) $e->getNextAttempt(),
			]);
		} catch (Domain_User_Exception_Mail_CodeErrorCountExceeded) {
			throw new CaseException(static::ECODE_LIMIT_CONFIRM_CODE, "limit confirm code");
		} catch (Domain_User_Exception_Mail_StoryNotFound|cs_CacheIsEmpty|Domain_User_Exception_Mail_StoryIsNotActive|Domain_User_Exception_Mail_StoryIsExpired
		|Domain_User_Exception_Mail_StoryIsNotActive|Domain_User_Exception_Mail_StoryIsSuccess) {
			throw new CaseException(static::ECODE_STORY_NOT_ACTIVE, "story not active");
		} catch (Domain_User_Exception_Mail_StoryNotEqualStage) {
			throw new ParamException("incorrect stage process");
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("incorrect authorized user");
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("incorrect add_mail_story_key");
		}

		return $this->ok([
			"mail_mask" => (string) Domain_User_Entity_Mail::getMailMask($mail),
		]);
	}

	##########################################################
	# region - переотправка проверочного кода на почту
	##########################################################

	/**
	 * Метод переотправки проверочного кода по почте для авторизованного пользователя
	 *
	 * @return array
	 * @throws CaseException
	 * @throws \queryException
	 *
	 * @long большой try-catch
	 */
	public function resendCode():array {

		$mail_story_key  = $this->post(\Formatter::TYPE_STRING, "mail_story_key");
		$mail_story_type = $this->post(\Formatter::TYPE_STRING, "mail_story_type");

		try {
			[$story, $story_code, $stage] = Domain_User_Scenario_Api_Security_Mail::resendCode($this->user_id, $mail_story_key, $mail_story_type);
		} catch (Domain_User_Exception_AuthMethodDisabled) {
			throw new CaseException(static::ECODE_AUTH_MAIL_DISABLED, "disabled authentication mail");
		} catch (Domain_User_Exception_Mail_NotFound) {
			throw new CaseException(static::ECODE_NOT_EXIST_MAIL, "user not mail");
		} catch (Domain_User_Exception_Mail_AlreadyExist) {
			throw new CaseException(static::ECODE_USER_EXIST_MAIL, "mail is exist in user");
		} catch (Domain_User_Exception_UserNotAuthorized) {
			throw new ParamException("not found code");
		} catch (Domain_User_Exception_Mail_ScenarioNotAllowed) {
			throw new CaseException(static::ECODE_SCENARIO_NOT_ALLOWED, "scenario not allowed");
		} catch (cs_CacheIsEmpty|Domain_User_Exception_Mail_StoryIsExpired|Domain_User_Exception_Mail_StoryIsNotActive
		|Domain_User_Exception_Mail_StoryIsSuccess|Domain_User_Exception_Password_NotFound|Domain_User_Exception_Mail_StoryNotFound
		|Domain_User_Exception_Password_StoryIsExpired|Domain_User_Exception_Password_StoryIsSuccess|Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
		|Domain_User_Exception_Security_Mail_Change_StoryIsExpired|Domain_User_Exception_Security_Mail_Change_StoryIsSuccess|Domain_User_Exception_Security_Mail_Change_StoryNotFound) {
			throw new CaseException(self::ECODE_STORY_NOT_ACTIVE, "active process not found");
		} catch (Domain_User_Exception_Mail_CodeResendNotAvailable $e) {

			throw new CaseException(self::ECODE_RESEND_CODE_NOT_AVAILABLE, "resend is not available", [
				"next_resend" => $e->getNextAttempt(),
			]);
		} catch (Domain_User_Exception_Mail_CodeResendCountExceeded $e) {

			throw new CaseException(self::ECODE_RESEND_COUNT_EXCEPTION, "resend count exceeded", [
				"next_attempt" => $e->getNextAttempt(),
			]);
		} catch (\cs_DecryptHasFailed|Domain_User_Exception_Password_WrongMap) {
			throw new ParamException("incorrect mail_story_key");
		}

		return $this->ok(Apiv2_Format::resendCode($story, $story_code, $mail_story_type, $stage));
	}
}