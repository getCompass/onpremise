<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;

// здесь определяются все исключения которые используются для логики
// они никак не влюяют на общее поведения системы и используются только
// если с другой стороны ловятся с помощью catch

##########################################################
# region custom system exceptions
##########################################################

/**
 * переотправка будет доступна позже
 */
class cs_ResendWillBeAvailableLater extends \Exception {

	protected int $_next_attempt;

	public function __construct(int $next_attempt, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_next_attempt = $next_attempt;
		parent::__construct($message, $code, $previous);
	}

	public function getNextAttempt():int {

		return $this->_next_attempt;
	}
}

/**
 * исключение уровня handler для того чтобы вернуть на клиент команду ВМЕСТО ответа
 */
class cs_AnswerCommand extends \Exception {

	protected string $_command_name;
	protected array  $_command_extra;

	public function __construct(string $command_name, array $command_extra, string $message = "", int $code = 0, \Exception $previous = null) {

		$this->_command_name  = $command_name;
		$this->_command_extra = $command_extra;
		parent::__construct($message, $code, $previous);
	}

	public function getCommandName():string {

		return $this->_command_name;
	}

	public function getCommandExtra():array {

		return $this->_command_extra;
	}
}

/**
 * когда пришел некорректный идентификатор платформы
 */
class cs_AnnouncementAuthorizationTokenNotReceived extends \Exception {

}

/**
 * когда пришел некорректный идентификатор платформы
 */
class cs_PlatformNotFound extends \Exception {

}

/**
 * когда пришла некорректная версия платформы
 */
class cs_PlatformVersionNotFound extends \Exception {

}

/**
 * пользователь не найден
 */
class cs_UserNotFound extends \Exception {

}

/**
 * если куки пусты
 */
class cs_CookieIsEmpty extends \Exception {

}

/**
 * выпадает если произошло дублирование при insert записи
 */
class cs_RowDuplication extends \Exception {

}

/**
 * строка не была обновлена
 */
class cs_RowNotUpdated extends \Exception {

}

/**
 * неверный код из смс
 */
class cs_WrongSmsCode extends \Exception {

	private int $available_attempts;
	private int $next_attempt;

	public function __construct(int $available_attempts = 0, int $next_attempt = 0, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->available_attempts = $available_attempts;
		$this->next_attempt       = $next_attempt;
		parent::__construct($message, $code, $previous);
	}

	public function getAvailableAttempts():int {

		return $this->available_attempts;
	}

	public function getNextAttempt():int {

		return $this->next_attempt;
	}
}

/**
 * нет такого номера телефона в базе
 */
class cs_PhoneNumberNotFound extends \Exception {

}

/**
 * неверный ключ аутентификации
 */
class cs_WrongAuthKey extends \Exception {

}

/**
 * аутентификация истекла
 */
class cs_AuthIsExpired extends \Exception {

}

/**
 * аутентификация уже была завершена
 */
class cs_AuthAlreadyFinished extends \Exception {

}

/**
 * пользователь уже залогинен
 */
class cs_UserAlreadyLoggedIn extends \Exception {

}

/**
 * пользователь не залогинен
 */
class cs_UserNotLoggedIn extends \Exception {

}

/**
 * недопустимый IP адрес пользователя
 */
class cs_InvalidIpAddress extends \Exception {

}

/**
 * кэш пустой
 */
class cs_CacheIsEmpty extends \Exception {

}

/**
 * номера телефонов не совпадают
 */
class cs_PhoneNumberIsNotEqual extends \Exception {

}

/**
 * типы не совпадают
 */
class cs_TypeIsNotEqual extends \Exception {

}

/**
 * невалидный код подтверждения
 */
class cs_InvalidConfirmCode extends \Exception {

}

/**
 * ошибка доменной логики
 */
class cs_DamagedActionException extends \Exception {

}

/**
 * превышен лимит ошибок
 */
class cs_ErrorCountLimitExceeded extends \Exception {

	protected int $_next_attempt;

	public function __construct(int $next_attempt, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_next_attempt = $next_attempt;
		parent::__construct($message, $code, $previous);
	}

	public function getNextAttempt():int {

		return $this->_next_attempt;
	}
}

/**
 * превышен лимит переотправок смс
 */
class cs_ResendSmsCountLimitExceeded extends \Exception {

}

/**
 * недопустимый код страны
 */
class cs_InvalidCountryCode extends \Exception {

}

/**
 * неизвестный тип ключа
 */
class cs_UnknownKeyType extends \Exception {

}

/**
 * требуется рекапча
 */
class cs_RecaptchaIsRequired extends \Exception {

}

/**
 * неверная рекапча
 */
class cs_WrongRecaptcha extends \Exception {

}

/**
 * ошибка парсинга данных события
 */
class cs_InvalidEventArgumentsException extends \Exception {

}

/**
 * фича не найден
 */
class cs_FeatureNotFound extends \Exception {

}

/**
 * правило для фичи не найдено
 */
class cs_RuleNotFound extends \Exception {

}

/**
 * правило с таким именем уже существует
 */
class cs_RuleAlreadyExists extends \Exception {

}

/**
 * невозможно сделать из именнованного правило безимянное
 */
class cs_RuleConvertingToUnnamed extends \Exception {

}

/**
 * невалидная аватарка
 */
class cs_InvalidAvatarFileMap extends \Exception {

}

/**
 * аватарка передана не изображением
 */
class cs_FileIsNotImage extends \Exception {

}

/**
 * невалидный тип личности для профиля
 */
class cs_InvalidMBTIType extends \Exception {

}

/**
 * превышено допустимое кол-во выделений
 */
class cs_ExceededColorSelectionList extends \Exception {

}

##########################################################
# компании
##########################################################

/**
 * компания по статусу не свободна
 */
class cs_NoFreeCompanyFound extends \Exception {

}

/**
 * компания по статусу не свободна
 */
class cs_FreeCompanySlotNotFound extends \Exception {

}

/**
 * не создали домино таблицу
 */
class cs_NotCreatedDominoTable extends \Exception {

}

/**
 * некоректный avatar_color_id
 */
class cs_CompanyIncorrectAvatarColorId extends \Exception {

}

/**
 * неккоректное имя компании
 */
class cs_CompanyIncorrectName extends \Exception {

}

/**
 * неккоректное имя компании
 */
class cs_CompanyIncorrectClientCompanyId extends \Exception {

}

/**
 * неккоректный device_id
 */
class cs_CompanyIncorrectDeviceId extends \Exception {

}

/**
 * некоректный company_id
 */
class cs_CompanyIncorrectCompanyId extends \Exception {

}

/**
 * некоректный limit
 */
class cs_CompanyIncorrectLimit extends \Exception {

}

/**
 * некоректный offset
 */
class cs_CompanyIncorrectMinOrder extends \Exception {

}

/**
 * выпадает если не нашли row в базе данных user_company_list
 */
class cs_CompanyUserIsNotFound extends \Exception {

}

/**
 * выпадает если не приглашенный
 */
class cs_CompanyUserIsNotInvited extends \Exception {

}

/**
 * выпадает если не покинул или не отозван
 */
class cs_CompanyUserIsNotLefted extends \Exception {

}

/**
 * компания не существует
 */
class cs_CompanyNotExist extends \Exception {

}

/**
 * достигнут лимит создания компаний
 */
class cs_CompanyCreateExceededLimit extends \Exception {

}

# endregion
##########################################################

/**
 * пользователь не найден в компании
 */
class cs_UserNotInCompany extends \Exception {

}

/**
 * пользователь имеет активные компании
 */
class cs_UserHaveActiveCompanies extends \Exception {

}

/**
 * пользователь уже заблочен
 */
class cs_UserAlreadyBlocked extends \Exception {

}

/**
 * пользователь уже в компании
 */
class cs_UserAlreadyInCompany extends \cs_ExceptionWithIndex {

	protected int    $_user_id;
	protected int    $_company_id;
	protected int    $_inviter_user_id;
	protected string $_inviter_full_name;
	protected int    $_is_post_moderation;
	protected int    $_entry_option;
	protected int    $_was_member;

	public function __construct(int $user_id = 0, int $company_id = 0, int $inviter_user_id = 0, string $inviter_full_name = "", int $is_post_moderation = 0, int $entry_option = 0, int $was_member = 0, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_user_id            = $user_id;
		$this->_company_id         = $company_id;
		$this->_inviter_user_id    = $inviter_user_id;
		$this->_inviter_full_name  = $inviter_full_name;
		$this->_is_post_moderation = $is_post_moderation;
		$this->_entry_option       = $entry_option;
		$this->_was_member         = $was_member;
		parent::__construct(0, $message, $code, $previous);
	}

	/**
	 * пишем id пользователя
	 */
	public function setUserId(int $user_id):void {

		$this->_user_id = $user_id;
	}

	/**
	 * получаем id пользователя
	 */
	public function getUserId():int {

		if ($this->_company_id < 1) {
			throw new ParseFatalException("exception does not contain user_id field");
		}

		return $this->_user_id;
	}

	/**
	 * пишем id компании
	 */
	public function setCompanyId(int $company_id):void {

		$this->_company_id = $company_id;
	}

	/**
	 * получаем id компании
	 */
	public function getCompanyId():int {

		if ($this->_company_id < 1) {
			throw new ParseFatalException("exception does not contain company_id field");
		}

		return $this->_company_id;
	}

	/**
	 * пишем user_id пользователя от которого приглашение
	 */
	public function setInviterUserId(int $inviter_user_id):void {

		$this->_inviter_user_id = $inviter_user_id;
	}

	/**
	 * получаем user_id пользователя от которого приглашение
	 */
	public function getInviterUserId():int {

		if ($this->_inviter_user_id < 1) {
			throw new ParseFatalException("exception does not contain inviter_user_id field");
		}

		return $this->_inviter_user_id;
	}

	/**
	 * получаем bvz пользователя от которого приглашение
	 */
	public function getInviterFullName():string {

		return $this->_inviter_full_name;
	}

	/**
	 * получаем флаг было ли приглашения на постмодерации
	 */
	public function getFlagPostModeration():int {

		return $this->_is_post_moderation;
	}

	/**
	 * получаем entry_option
	 */
	public function getEntryOption():int {

		return $this->_entry_option;
	}

	/**
	 * получаем флаг состоял ли пользователь в компании
	 */
	public function getFlagWasMember():int {

		return $this->_was_member;
	}
}

/**
 * пользователь уже находится на постмодерации
 */
class cs_UserAlreadyInPostModeration extends \Exception {

}

/**
 * приглашение уже есть
 */
class cs_InviteAlreadyExists extends \cs_ExceptionWithIndex {

}

/**
 * приглашение не найдено
 */
class cs_InviteNotFound extends \Exception {

}

/**
 * пользователь не имеет прав на это действие
 */
class cs_UserIsNotInvitee extends \Exception {

}

/**
 * приглашение было отозвано
 */
class cs_InviteIsRevoked extends \Exception {

}

/**
 * приглашение было отклонено
 */
class cs_InviteIsDeclined extends \Exception {

}

/**
 * приглашение было принято
 */
class cs_InviteIsAccepted extends \Exception {

}

/**
 * прислан невалидный user_company_session_token
 */
class cs_InvalidUserCompanySessionToken extends \Exception {

}

/**
 * нет доступных для обслуживания смс-провайдеров
 */
class cs_SmsNoAvailableProviders extends \Exception {

}

/**
 * неверная подпись
 */
class cs_WrongSignature extends \Exception {

}

/**
 * запрос к провайдеру провалился
 */
class cs_SmsFailedRequestToProvider extends \Exception {

	protected Struct_Gateway_Sms_Provider_Response $_response;

	public function __construct(Struct_Gateway_Sms_Provider_Response $response, string $message = "", int $code = 0, \Exception $previous = null) {

		debug($response);
		$this->_response = $response;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * возвращает сам ответ
	 *
	 */
	public function getResponse():Struct_Gateway_Sms_Provider_Response {

		return $this->_response;
	}
}

/**
 * mocked-данные не найдены
 */
class cs_MockedDataIsNotFound extends \Exception {

}

/**
 * передан хэш с неверной структурой
 */
class cs_InvalidHashStruct extends \Exception {

}

/**
 * неверная версия соли
 */
class cs_IncorrectSaltVersion extends \Exception {

}

/**
 * Faq заметка не найдена
 */
class cs_FaqNotFound extends \Exception {

}

/**
 * Class cs_PhoneNumberIsBlocked
 */
class cs_PhoneNumberIsBlocked extends BlockException {

	/** @var int время для следующей попытки */
	private int $next_attempt;

	/**
	 * cs_PhoneNumberIsBlocked constructor.
	 *
	 */
	public function __construct(int $next_attempt, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->next_attempt = $next_attempt;
		parent::__construct($message, $next_attempt, $code, $previous);
	}

	public function getNextAttempt():int {

		return $this->next_attempt;
	}
}

/**
 * Дублируется order поле в массиве компаний
 */
class cs_DuplicateOrder extends \Exception {

}

/**
 * Дублируется company_id поле в массиве компаний
 */
class cs_DuplicateCompanyId extends \Exception {

}

/**
 * Найдено лишнее значение
 */
class cs_FoundExtraValue extends \Exception {

}

/**
 * Отсутствует нужное значение
 */
class cs_MissedValue extends \Exception {

}

/**
 * Неверное значение
 */
class cs_WrongValue extends \Exception {

}

/**
 * 2fa токен истек
 */
class cs_TwoFaIsExpired extends \Exception {

}

/**
 * 2fa действие завершено
 */
class cs_TwoFaIsFinished extends \Exception {

}

/**
 * 2fa действие потверждено
 */
class cs_TwoFaIsActive extends \Exception {

}

/**
 * Неверный 2fa токен
 */
class cs_WrongTwoFaKey extends \Exception {

}

/**
 * Текущий пользователь и пользователь которому выдали 2fa токен не совпадают
 */
class cs_TwoFaInvalidUser extends \Exception {

}

/**
 * Переданная компания и компания в которую выдали 2fa токен, не совпадают
 */
class cs_TwoFaInvalidCompany extends \Exception {

}

/**
 * Номер телефона не подтверждён
 */
class cs_PhoneIsNotConfirmed extends \Exception {

}

/**
 * Тип действия невалидный
 */
class cs_TwoFaTypeIsInvalid extends \Exception {

}

/**
 * 2fa токен не активен
 */
class cs_TwoFaIsNotActive extends \Exception {

}

/**
 * Публичный документ не найден
 */
class cs_PublicDocumentNotFound extends \Exception {

}

/**
 * неверные данные для приглашения
 */
class cs_WrongInviteData extends \cs_ExceptionWithIndex {

}

/**
 * данные телефона пользователя не найдены
 */
class cs_UserPhoneSecurityNotFound extends \Exception {

}

/**
 * есть дубль телефона
 */
class cs_PhoneNumberDuplicate extends \cs_ExceptionWithIndex {

}

/**
 * превышение по лимиту кол-ва приглашений
 */
class cs_InvitesCountLimit extends \Exception {

	protected int $_limit;

	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 *
	 * @link https://php.net/manual/en/exception.construct.php
	 *
	 * @param string         $message  [optional] The Exception message to throw.
	 * @param int            $code     [optional] The Exception code.
	 * @param \Throwable|null $previous [optional] The previous \Throwable used for the exception chaining.
	 */
	public function __construct(int $limit, string $message = "", int $code = 0, \Throwable|null $previous = null) {

		$this->_limit = $limit;
		parent::__construct($message, $code, $previous);
	}

	public function getLimit():int {

		return $this->_limit;
	}
}

/**
 * действие для компании заблокировано
 */
class cs_ActionForCompanyBlocked extends \Exception {

}

/**
 * пользователь не является создателем компании
 */
class cs_UserIsNotCreatorOfCompany extends \Exception {

}

/**
 * приглашения уже были отправлены после создания компании
 */
class cs_InvitationsAlreadyWereSentOnCreation extends \Exception {

}

/**
 * при срабатывании блокировки, но с данными о следующей попытке
 */
class cs_blockException extends BlockException {

	/** @var int время для следующей попытки */
	private int $next_attempt;

	/**
	 * cs_PhoneNumberIsBlocked constructor.
	 *
	 */
	public function __construct(int $next_attempt, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->next_attempt = $next_attempt;
		parent::__construct($message, $next_attempt, $code, $previous);
	}

	public function getNextAttempt():int {

		return $this->next_attempt;
	}
}

/**
 * Неподдерживаемый тип токена
 */
class cs_NotificationsUnsupportedTokenType extends \Exception {

}

/**
 * Невалидный токен
 */
class cs_NotificationsInvalidToken extends \Exception {

}

/**
 * Интервал меньше 1
 */
class cs_NotificationsIntervalLessThenMinute extends \Exception {

}

/**
 * Достиги лимит
 */
class cs_NotificationsShutdownLimitExceeded extends BlockException {

	/** @var int время для следующей попытки */
	private int $max_time_limit;

	/**
	 * cs_NotificationsShutdownLimitExceeded constructor.
	 *
	 */
	public function __construct(int $max_time_limit, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->max_time_limit = $max_time_limit;
		parent::__construct($message, $max_time_limit, $code, $previous);
	}

	public function getMaxTimeLimit():int {

		return $this->max_time_limit;
	}
}

/**
 * ошибка при некорретных данных переключения режима событий
 */
class cs_IncorrectNotificationToggleData extends \Exception {

}

/**
 * Некорректный токен пользователя
 */
class cs_UserNotHaveToken extends \Exception {

}

/**
 * ошибка при некорретных данных переключения режима событий
 */
class cs_NotificationUnsupportedSoundType extends \Exception {

}

/**
 * Неверное значение конфига
 */
class cs_InvalidConfigValue extends \Exception {

}

/**
 * Процесс смены номера телефона истек
 */
class cs_PhoneChangeIsExpired extends \Exception {

}

/**
 * Процесс смены номера неактивен
 */
class cs_PhoneChangeIsNotActive extends \Exception {

}

/**
 * Процесс смены номера завершен
 */
class cs_PhoneChangeIsSuccess extends \Exception {

}

/**
 * Ожидали другой этап смены номера
 */
class cs_PhoneChangeWrongStage extends \Exception {

}

/**
 * Смс для смены номера уже подтверждена
 */
class cs_PhoneChangeSmsAlreadyConfirmed extends \Exception {

}

/**
 * Смс для смены номера отклонена
 */
class cs_PhoneChangeSmsDeclined extends \Exception {

}

/**
 * Смс для смены номера не найдена
 */
class cs_PhoneChangeSmsNotFound extends \Exception {

}

/**
 * Превышено кол-во попыток подтвердить смс для смены номера
 */
class cs_PhoneChangeSmsErrorCountExceeded extends \Exception {

	/** @var int время для следующей попытки */
	private int $next_attempt;

	/**
	 * cs_PhoneChangeSmsErrorCountExceeded constructor.
	 *
	 */
	public function __construct(int $next_attempt = 0, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->next_attempt = $next_attempt;
		parent::__construct($message, $code, $previous);
	}

	public function setNextAttempt(int $next_attempt):void {

		$this->next_attempt = $next_attempt;
	}

	public function getNextAttempt():int {

		return $this->next_attempt;
	}
}

/**
 * Превышено кол-во попыток переотправить смс для смены номера
 */
class cs_PhoneChangeSmsResendCountExceeded extends \Exception {

}

/**
 * Не наступило время для переотправки
 */
class cs_PhoneChangeSmsResendNotAvailable extends cs_blockException {

}

/**
 * Неверный ключ к смене номера телефона
 */
class cs_PhoneChangeStoryWrongMap extends \Exception {

}

/**
 * Такой номер телефона уже зарегистрирован
 */
class cs_PhoneAlreadyRegistered extends \Exception {

}

/**
 * Этот номер уже принадлежит этому пользователю
 */
class cs_PhoneAlreadyAssignedToCurrentUser extends \Exception {

}

/**
 * Смс на этот номер уже была отправлена
 */
class cs_PhoneChangeSmsStoryAlreadyExist extends \Exception {

}

/**
 * Превышен лимит числа отправленных приглашений за день
 */
class cs_DailyCompanyInviteLimitExceeded extends BlockException {

}

/**
 * Превышен лимит числа одновременно активных приглашений
 */
class cs_ActiveCompanyInviteLimitExceeded extends BlockException {

}

/**
 * Превышен лимит числа отклоненных приглашений в компанию
 */
class cs_DeclinedCompanyInviteLimitExceeded extends BlockException {

}

/**
 * Компания не активна (свободна или отключена)
 */
class cs_CompanyIsNotActive extends \Exception {

}

/**
 * Компания не в лобби
 */
class cs_CompanyIsNotLobby extends \Exception {

}

/**
 * Ссылка-инвайт не найдена
 */
class cs_JoinLinkNotFound extends \Exception {

}

/**
 * Некоректная ссылка
 */
class cs_IncorrectJoinLink extends \Exception {

}

/**
 * Некорректный тип страницы ссылки-приглашения
 */
class cs_IncorrectJoinLinkPageType extends \Exception {

}

/**
 * Инвайт-ссылка не действительна
 */
class cs_JoinLinkIsNotActive extends \Exception {

}

/**
 * Не найден таск на выход из компании
 */
class cs_ExitTaskNotExist extends \Exception {

}

/**
 * Таска на выход из компании в статусе "in progress"
 */
class cs_ExitTaskInProgress extends \Exception {

}

/**
 * Инвайт-ссылка уже использована
 */
class cs_JoinLinkIsUsed extends \Exception {

}

/**
 * Пригласительная ссылка протухла
 */
class cs_JoinLinkIsExpired extends \Exception {

}

/**
 * Отправленный текст слишком длинный
 */
class cs_Text_IsTooLong extends \Exception {

}

/**
 * Заявка на наем не на постмодерации
 */
class cs_HiringRequestNotPostmoderation extends \Exception {

}

/**
 * Невалидный 2fa токен
 */
class cs_TwoFaIsInvalid extends \Exception {

}

/**
 * Некорретная версия чего либо
 */
class cs_IncorrectVersion extends \Exception {

}

/**
 * Некорретная локаль
 */
class cs_IncorrectLang extends \Exception {

}

/**
 * некорректный timestamp
 */
class cs_IncorrectTimestamp extends \Exception {

}

/**
 * неккоректный массив id компаний
 */
class cs_CompanyIncorrectCompanyIdList extends \Exception {

}

/**
 * Действие не доступно
 */
class cs_ActionNotAvailable extends \Exception {

}

/**
 * Компания в гибернации
 */
class cs_CompanyIsHibernate extends \Exception {

}

/**
 * у одного из пользователей имеется активный звонок
 */
class cs_OneOfUsersHaveActiveCall extends \Exception {

	// список пользователей, у кого занята телефонная линия
	protected array $_user_list_with_busy_line = [];

	public function __construct(array $user_list_with_busy_line, $message = "", $code = 0, \Throwable $previous = null) {

		$this->_user_list_with_busy_line = $user_list_with_busy_line;

		parent::__construct($message, $code, $previous);
	}

	/**
	 * получаем список пользователей с занятой телефонной линией
	 *
	 * @return array
	 */
	public function getUserListWithBusyLine():array {

		return $this->_user_list_with_busy_line;
	}
}