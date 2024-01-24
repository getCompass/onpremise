<?php

namespace Compass\Company;

// здесь определяются все исключения которые используются для логики
// они никак не влюяют на общее поведения системы и используются только
// если с другой стороны ловятся с помощью catch

##########################################################
# region custom system exceptions
##########################################################

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
class cs_PlatformNotFound extends \Exception {

}

/**
 * когда пришла некорректная версия платформы
 */
class cs_PlatformVersionNotFound extends \Exception {

}

/**
 * если куки пусты
 */
class cs_CookieIsEmpty extends \Exception {

}

/**
 * выпадает если ничего не обновили
 */
class cs_RowNotUpdated extends \Exception {

}

/**
 * выпадает если запись уже имеется в базе данных
 */
class cs_RowAlreadyExist extends \Exception {

}

/**
 * неверные данные для приглашения
 */
class cs_WrongInviteData extends \cs_ExceptionWithIndex {

}

/**
 * выпадает если пин код не правилен
 */
class cs_IsNotEqualsPinCode extends \Exception {

}

/**
 * выпадает если user_company_session_token не валиден
 */
class cs_InvalidUserCompanySessionToken extends \Exception {

}

/**
 * Пользователь уже залогинен
 */
class cs_UserAlreadyLoggedIn extends \Exception {

}

/**
 * Пользователь не залогинен
 */
class cs_UserNotLoggedIn extends \Exception {

}

/**
 * есть дубль телефона
 */
class cs_PhoneNumberDuplicate extends \cs_ExceptionWithIndex {

}

/**
 * не найдена запись пушей для пользователя
 */
class cs_UserNotificationNotFound extends \Exception {

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
	 * @param \Throwable|null $previous [optional] The previous throwable used for the exception chaining.
	 *
	 * @mixed
	 */
	public function __construct(int $limit, $message = "", $code = 0, \Throwable | null $previous = null) {

		$this->_limit = $limit;
		parent::__construct($message, $code, $previous);
	}

	public function getLimit():int {

		return $this->_limit;
	}
}

/**
 * заявки на найм не существует
 */
class cs_HireRequestNotExist extends \Exception {

}

/**
 * заявки на увольнение не существует
 */
class cs_DismissalRequestNotExist extends \Exception {

}

/**
 * задачи на увольнение не существует
 */
class cs_DismissalTaskNotExist extends \Exception {

}

/**
 * заявка на найм уже существует
 */
class cs_HiringRequestIsAlreadyExist extends \Exception {

}

/**
 * заявка на увольнение уже существует
 */
class cs_DismissalRequestIsAlreadyExist extends \Exception {

}

/**
 * Пользователь не имеет прав на действие для найма
 */
class cs_UserHasNoRightsToHiring extends \Exception {

}

/**
 * Пользователь не имеет прав на действие для увольнение
 */
class cs_UserHasNotRightsToDismiss extends \Exception {

}

/**
 * список пользователей уволены из компании
 */
class cs_UserKickedList extends \Exception {

	protected array $_kicked_user_id_list;

	public function __construct(array $kicked_user_id_list, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_kicked_user_id_list = $kicked_user_id_list;

		parent::__construct($message, $code, $previous);
	}

	public function getKickedUserIdList():array {

		return $this->_kicked_user_id_list;
	}
}

/**
 * Пользователь уже имеется в компании
 */
class cs_UserAlreadyInCompany extends \cs_ExceptionWithIndex {

	protected int $_user_id;

	public function __construct(int $user_id = 0, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_user_id = $user_id;

		parent::__construct(0, $message, $code, $previous);
	}

	public function getUserId():int {

		return $this->_user_id;
	}
}

/**
 * Пользователь не смог добавить в группу или ошибка при создании сингл диалогов с пользователями
 */
class cs_UsersFromSingleListErrorOrUserCannotAddToGroups extends \Exception {

	protected array $_users_groups_ok_error_list;

	public function __construct(array $users_groups_ok_error_list, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_users_groups_ok_error_list = $users_groups_ok_error_list;

		parent::__construct($message, $code, $previous);
	}

	public function getUsersGroupsOkErrorList():array {

		return $this->_users_groups_ok_error_list;
	}
}

/**
 * Пользователь не имеет прав на отправку инвайта
 */
class cs_UserHasNotRightsToSendInvite extends \Exception {

}

/**
 * пользователи не состоят в компании
 */
class cs_UsersFromSingleListToCreateNotExistInCompany extends \cs_ExceptionWithIndex {

}

/**
 * приглашение уже есть
 */
class cs_InviteAlreadyExists extends \cs_ExceptionWithIndex {

}

/**
 * приглашение уже активно
 */
class cs_InviteLinkNotActive extends \Exception {

}

/**
 * приглашение не существует
 */
class cs_InviteLinkNotExist extends \Exception {

}

/**
 * приглашение не существует
 */
class cs_JoinLinkNotExist extends \Exception {

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
 * лимит для списка пользователей
 */
class cs_CompanyUsersLimit extends \Exception {

}

/**
 * Пользователь не админ по умолчанию
 */
class cs_CompanyUserIsNotForceAdmin extends \Exception {

}

/**
 * Пользователь не программист ботов
 */
class cs_CompanyUserIsNotDeveloper extends \Exception {

}

/**
 * бот не найден
 */
class cs_UserbotNotExist extends \Exception {

}

/**
 * бот не включён
 */
class cs_CompanyUserbotIsNotEnabled extends \Exception {

}

/**
 * бот не выключен
 */
class cs_CompanyUserbotIsNotDisabled extends \Exception {

}

/**
 * некорректный параметр для бота
 */
class cs_IncorrectUserbotParam extends \Exception {

}

/**
 * ошибка парсинга данных события
 */
class cs_InvalidEventArgumentsException extends \Exception {

}

/**
 * отправленный текст слишком длинный
 */
class cs_Text_IsTooLong extends \Exception {

}

/**
 * у сотрудника отсутствуют редакторы
 */
class cs_ThereAreNoEmployeeEditorsException extends \Exception {

}

/**
 * нельзя выполнить действие, потому что вышло время для этого действия
 */
class cs_Action_TimeIsOver extends \Exception {

}

/**
 * Неверное значение конфига
 */
class cs_InvalidConfigValue extends \Exception {

}

/**
 * Некоректный формат пин-кода
 */
class cs_IncorrectPinValue extends \Exception {

}

/**
 * некорректный тип ивента
 */
class cs_RatingIncorrectEvent extends \Exception {

}

/**
 * Требуется подтвердить пинкод
 */
class cs_PinCodeNotConfirmed extends \Exception {

}

/**
 * некорректный тип периода
 */
class cs_RatingIncorrectPeriodType extends \Exception {

}

/**
 * Старый и новый пинкоды совпадают
 */
class cs_OldAndNewPincodesTheSame extends \Exception {

}

/**
 * некорректный параметры даты
 */
class cs_RatingIncorrectDateParams extends \Exception {

}

/**
 * нужно пройти 2fa
 */
class cs_NeedPerformTwoFa extends \Exception {

}

/**
 * некорректный недели
 */
class cs_RatingIncorrectYearOrWeeks extends \Exception {

}

/**
 * Невалидный 2fa токен
 */
class cs_TwoFaIsInvalid extends \Exception {

}

/**
 * Тип действия невалидный
 */
class cs_PinTypeIsInvalid extends \Exception {

}

/**
 * pin токен истек
 */
class cs_PinIsExpired extends \Exception {

}

/**
 * pin действие завершено
 */
class cs_PinIsFinished extends \Exception {

}

/**
 * Текущий пользователь и пользователь которому выдали pin токен не совпадают
 */
class cs_PinInvalidUser extends \Exception {

}

/**
 * Неверный pin токен
 */
class cs_WrongPinKey extends \Exception {

}

/**
 * pin действие потверждено
 */
class cs_PinIsActive extends \Exception {

}

/**
 * pin токен не активен
 */
class cs_PinIsNotActive extends \Exception {

}

/**
 * некорректный период
 */
class cs_RatingIncorrectPeriod extends \Exception {

}

/**
 * Неактивный 2fa токен
 */
class cs_TwoFaIsNotActive extends \Exception {

}

/**
 * некорректный лимит
 */
class cs_RatingIncorrectLimit extends \Exception {

}

/**
 * некорректный оффест
 */
class cs_RatingIncorrectOffset extends \Exception {

}

/**
 * действие для компании заблокировано
 */
class cs_ActionForCompanyBlocked extends \Exception {

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
 * Пользователь владелец компании. Используется при запрете действий над владельцем
 */
class cs_CompanyUserIsOwner extends \Exception {

}

/**
 * Пользователь единственный владелец в компании
 */
class cs_CompanyUserIsOnlyOwner extends \Exception {

}

/**
 * Пользователь руководитель компании. Используется при запрете действий над руководителем
 */
class cs_CompanyUserIsLeader extends \Exception {

}

/**
 * Пользователь не состоит в группе
 */
class cs_UserIsNotInGroup extends \Exception {

}

/**
 * Действие не доступно по какой либо причине (не важно)
 */
class cs_ActionNotAvailable extends \Exception {

}

/**
 * Пользователь сотрудник компании.
 */
class cs_CompanyUserIsEmployee extends \Exception {

}

/**
 * У пользователя ограничен доступ к данным компании.
 */
class cs_UserCompanyAccessLimited extends \Exception {

}

/**
 * У пользователя ограничен доступ к данным компании.
 */
class cs_SearchQueryHasIncorrectLength extends \Exception {

}

/**
 * Пустой список идентификаторов пользователей
 */
class cs_UserIdListEmpty extends \Exception {

}

/**
 * Получили некорректный id пользователя
 */
class cs_IncorrectUserId extends \Exception {

}

/**
 * Получили некорректный country_code
 */
class cs_IncorrectCountryCode extends \Exception {

}

/**
 * Когда микросервис go_sender ответил не ОК
 */
class cs_TalkingBadResponse extends \Exception {

}

/**
 * Невалидное название пресета
 */
class cs_InvalidConversationPresetTitle extends \Exception {

}

/**
 * Превышен лимит числа диалогов в пресете
 */
class cs_InvalidConversationCount extends \Exception {

}

/**
 * Диалог не доступен для добавления в пресет
 */
class cs_ConversationIsNotAvailableForPreset extends \Exception {

	protected array $_can_send_invite_conversation_key_list;
	protected array $_cannot_send_invite_conversation_key_list;
	protected array $_leaved_member_conversation_key_list;
	protected array $_kicked_member_conversation_key_list;
	protected array $_not_exist_in_company_conversation_key_list;
	protected array $_not_group_conversation_key_list;
	protected array $_ok_list;
	protected array $_kicked_list;

	/**
	 *
	 * @param string         $message  [optional] The Exception message to throw.
	 * @param int            $code     [optional] The Exception code.
	 * @param \Throwable|null $previous [optional] The previous throwable used for the exception chaining.
	 *
	 * @mixed
	 */
	public function __construct(array            $can_send_invite_conversation_key_list,
					    array            $cannot_send_invite_conversation_key_list,
					    array            $leaved_member_conversation_key_list,
					    array            $kicked_member_conversation_key_list,
					    array            $not_exist_in_company_conversation_key_list,
					    array            $not_group_conversation_key_list,
					    array            $_ok_list = [],
					    array            $_kicked_list = [],
								   $message = "",
								   $code = 0,
					    \Throwable | null $previous = null) {

		$this->_can_send_invite_conversation_key_list      = $can_send_invite_conversation_key_list;
		$this->_cannot_send_invite_conversation_key_list   = $cannot_send_invite_conversation_key_list;
		$this->_leaved_member_conversation_key_list        = $leaved_member_conversation_key_list;
		$this->_kicked_member_conversation_key_list        = $kicked_member_conversation_key_list;
		$this->_not_exist_in_company_conversation_key_list = $not_exist_in_company_conversation_key_list;
		$this->_not_group_conversation_key_list            = $not_group_conversation_key_list;
		$this->_ok_list                                    = $_ok_list;
		$this->_kicked_list                                = $_kicked_list;

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Получаем доступные для приглашения список ключей диалогов
	 */
	public function getCanSendInviteConversationKeyList():array {

		return $this->_can_send_invite_conversation_key_list;
	}

	/**
	 * Получаем список диалогов, где пользователь не имеет прав для приглашения
	 */
	public function getCannotSendInviteConversationKeyList():array {

		return $this->_cannot_send_invite_conversation_key_list;
	}

	/**
	 * Получаем список диалогов, которые пользователь покинул
	 */
	public function getLeavedMemberConversationKeyList():array {

		return $this->_leaved_member_conversation_key_list;
	}

	/**
	 * Получаем список диалогов из которых пользователь был кикнут
	 */
	public function getKickedMemberConversationKeyList():array {

		return $this->_kicked_member_conversation_key_list;
	}

	/**
	 * Получаем список диалогов, которые не существуют в текущей компании
	 */
	public function getNotExistInCompanyConversationKeyList():array {

		return $this->_not_exist_in_company_conversation_key_list;
	}

	/**
	 * Получаем список диалогов, которые не являются групповыми
	 */
	public function getNotGroupConversationKeyList():array {

		return $this->_not_group_conversation_key_list;
	}

	/**
	 * Получаем доступные для приглашения список ключей диалогов
	 */
	public function getOkList():array {

		return $this->_ok_list;
	}

	/**
	 * Получаем список диалогов, где пользователь не имеет прав для приглашения
	 */
	public function getKickedList():array {

		return $this->_kicked_list;
	}
}

/**
 * Диалог не доступен для добавления в пресет
 */
class cs_SingleIsNotAvailableForPreset extends \Exception {

	protected array $_ok_list;
	protected array $_kicked_list;
	protected array $_can_send_invite_conversation_key_list;
	protected array $_cannot_send_invite_conversation_key_list;
	protected array $_leaved_member_conversation_key_list;
	protected array $_kicked_member_conversation_key_list;
	protected array $_not_exist_in_company_conversation_key_list;
	protected array $_not_group_conversation_key_list;

	/**
	 *
	 * @param string         $message  [optional] The Exception message to throw.
	 * @param int            $code     [optional] The Exception code.
	 * @param \Throwable|null $previous [optional] The previous throwable used for the exception chaining.
	 *
	 * @mixed
	 */
	public function __construct(array            $ok_list,
					    array            $kicked_list,
					    array            $can_send_invite_conversation_key_list = [],
					    array            $cannot_send_invite_conversation_key_list = [],
					    array            $leaved_member_conversation_key_list = [],
					    array            $kicked_member_conversation_key_list = [],
					    array            $not_exist_in_company_conversation_key_list = [],
					    array            $not_group_conversation_key_list = [],
								   $message = "",
								   $code = 0,
					    \Throwable | null $previous = null) {

		$this->_ok_list                                    = $ok_list;
		$this->_kicked_list                                = $kicked_list;
		$this->_can_send_invite_conversation_key_list      = $can_send_invite_conversation_key_list;
		$this->_cannot_send_invite_conversation_key_list   = $cannot_send_invite_conversation_key_list;
		$this->_leaved_member_conversation_key_list        = $leaved_member_conversation_key_list;
		$this->_kicked_member_conversation_key_list        = $kicked_member_conversation_key_list;
		$this->_not_exist_in_company_conversation_key_list = $not_exist_in_company_conversation_key_list;
		$this->_not_group_conversation_key_list            = $not_group_conversation_key_list;

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Получаем доступные для приглашения список ключей диалогов
	 */
	public function getOkList():array {

		return $this->_ok_list;
	}

	/**
	 * Получаем список диалогов, где пользователь не имеет прав для приглашения
	 */
	public function getKickedList():array {

		return $this->_kicked_list;
	}

	/**
	 * Получаем доступные для приглашения список ключей диалогов
	 */
	public function getCanSendInviteConversationKeyList():array {

		return $this->_can_send_invite_conversation_key_list;
	}

	/**
	 * Получаем список диалогов, где пользователь не имеет прав для приглашения
	 */
	public function getCannotSendInviteConversationKeyList():array {

		return $this->_cannot_send_invite_conversation_key_list;
	}

	/**
	 * Получаем список диалогов, которые пользователь покинул
	 */
	public function getLeavedMemberConversationKeyList():array {

		return $this->_leaved_member_conversation_key_list;
	}

	/**
	 * Получаем список диалогов из которых пользователь был кикнут
	 */
	public function getKickedMemberConversationKeyList():array {

		return $this->_kicked_member_conversation_key_list;
	}

	/**
	 * Получаем список диалогов, которые не существуют в текущей компании
	 */
	public function getNotExistInCompanyConversationKeyList():array {

		return $this->_not_exist_in_company_conversation_key_list;
	}

	/**
	 * Получаем список диалогов, которые не являются групповыми
	 */
	public function getNotGroupConversationKeyList():array {

		return $this->_not_group_conversation_key_list;
	}
}

/**
 * Превышен лимит кол-ва пресетов
 */
class cs_ExceededPresetCountLimit extends \Exception {

}

/**
 * Превышен лимит кол-ва запрашиваемых id
 */
class cs_ExceededCountOfRequestedId extends \Exception {

}

/**
 * Массив имеет дубликаты
 */
class cs_ArrayHasDuplicates extends \Exception {

}

/**
 * Активный пресет не найден
 */
class cs_ActiveConversationPresetNotFound extends \Exception {

}

/**
 * Получили некорректный id заявки на найм
 */
class cs_IncorrectHiringRequestId extends \Exception {

}

/**
 * Получили некорректный массив id заявок на найм
 */
class cs_IncorrectHiringRequestIdList extends \Exception {

}

/**
 * Получили некорректный массив ключей диалогов
 */
class cs_IncorrectConversationKeyListToJoin extends \Exception {

}

/**
 * Получили некорректный массив id пользователей для создания сингл диалогов
 */
class cs_IncorrectSingleListToCreate extends \Exception {

}

/**
 * Получили некорректный id заявки на увольнение
 */
class cs_IncorrectDismissalRequestId extends \Exception {

}

/**
 * Получили некорректный массив id заявок на увольнение
 */
class cs_IncorrectDismissalRequestIdList extends \Exception {

}

/**
 * Заявка на найем уже одобрена
 */
class cs_HiringRequestAlreadyApproved extends \Exception {

}

/**
 * Заявка на наем уже принята
 */
class cs_HiringRequestAlreadyConfirmed extends \Exception {

}

/**
 * Заявка на наем не на постмодерации
 */
class cs_HiringRequestNotPostmoderation extends \Exception {

}

/**
 * Заявка на наем не принята
 */
class cs_HiringRequestNotConfirmed extends \Exception {

}

/**
 * Заявка на найм уже отклонена
 */
class cs_HiringRequestAlreadyRejected extends \Exception {

}

/**
 * Получен отрицательный интервал времени при разнице дат
 */
class cs_DatesWrongOrder extends \Exception {

}

/**
 * Заявка на увольнение уже одобрена
 */
class cs_DismissalRequestAlreadyApproved extends \Exception {

}

/**
 * Заявка на увольнение уже отклонена
 */
class cs_DismissalRequestAlreadyRejected extends \Exception {

}

/**
 * Заявка на увольнение уже отклонена
 */
class cs_UserHasNotRoleToDismiss extends \Exception {

}

/**
 * У пользователя недостаточно прав для доступа к функионалу истории
 */
class cs_UserHasNotRightToGetHiringHistory extends \Exception {

}

/**
 * Несуществующий статус для заявки
 */
class cs_IncorrectRequestStatus extends \Exception {

}

/**
 * Несущетсвующий статус для пользователя
 */
class cs_IncorrectMemberStatus extends \Exception {

}

/**
 * Не можем уволить пользователя
 */
class cs_UserCantLeftCompany extends \Exception {

	protected int $_company_id;
	protected int $_user_id;

	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 *
	 * @link https://php.net/manual/en/exception.construct.php
	 *
	 * @param string         $message  [optional] The Exception message to throw.
	 * @param int            $code     [optional] The Exception code.
	 * @param \Throwable|null $previous [optional] The previous throwable used for the exception chaining.
	 *
	 * @mixed
	 */
	public function __construct(int $company_id, int $user_id, $message = "", int $code = 0, \Throwable | null $previous = null) {

		$this->_user_id    = $user_id;
		$this->_company_id = $company_id;
		parent::__construct($message, $code, $previous);
	}

	public function getUserId():int {

		return $this->_user_id;
	}

	public function getCompanyId():int {

		return $this->_company_id;
	}
}

/**
 * некорректный лимит
 */
class cs_IncorrectLimit extends \Exception {

}

/**
 * некорректный оффест
 */
class cs_IncorrectOffset extends \Exception {

}

/**
 * некорректный invite_link_uniq
 */
class cs_IncorrectInviteLinkUniq extends \Exception {

}

/**
 * некорректный join_link_uniq
 */
class cs_IncorrectJoinLinkUniq extends \Exception {

}

/**
 * invite_link истек
 */
class cs_InviteLinkIdExpired extends \Exception {

}

/**
 * invite_link более не может быть использован
 */
class cs_InviteLinkAlreadyUsed extends \Exception {

}

/**
 * некорректный type
 */
class cs_IncorrectType extends \Exception {

}

/**
 * некорректный lives_day_count
 */
class cs_IncorrectLivesDayCount extends \Exception {

}

/**
 * некорректный lives_hour_count
 */
class cs_IncorrectLivesHourCount extends \Exception {

}

/**
 * некорректный can_use_count
 */
class cs_IncorrectCanUseCount extends \Exception {

}

/**
 * превышен лимит активных инвайтов
 */
class cs_ExceededCountActiveInvite extends \Exception {

}

/**
 * ссылка не может быть отредактирована
 */
class cs_InvalidStatusForEditInvite extends \Exception {

}

/**
 * ссылка удалена
 */
class cs_InviteLinkDeleted extends \Exception {

}

/**
 * ссылка удалена
 */
class cs_JoinLinkDeleted extends \Exception {

}

/**
 * переданы неправильные параметры
 */
class cs_InvalidParamForEditInvite extends \Exception {

}

/**
 * нет владельца в компании
 */
class cs_NoOwnerException extends \cs_RowIsEmpty {

}

/**
 * Превышен лимит активных приглашения при одобрении заявки
 */
class cs_ActiveCompanyInviteLimitExceeded extends \Exception {

}

/**
 * Превышен лимит числа отправленных приглашений за день
 */
class cs_DailyCompanyInviteLimitExceeded extends \Exception {

}

/**
 * Превышен лимит числа отклоненных приглашений за день
 */
class cs_DeclinedCompanyInviteLimitExceeded extends \Exception {

}

/**
 * Превышен лимит числа отклоненных приглашений за день
 */
class cs_RecaptchaFailedOnSendInvite extends \Exception {

}

/**
 * пользователи не являются участниками компании
 */
class cs_NotCompanyMembers extends \Exception {

	protected array $_user_id_list;

	/**
	 *
	 * @param string         $message  [optional] The Exception message to throw.
	 * @param int            $code     [optional] The Exception code.
	 *
	 * @mixed
	 */
	public function __construct(array $user_id_list, $message = "", $code = 0, \Throwable | null $previous = null) {

		$this->_user_id_list = $user_id_list;
		parent::__construct($message, $code, $previous);
	}

	public function getUserIdList():array {

		return $this->_user_id_list;
	}
}

/**
 * Достигли максимального времени для отключения уведомлений
 */
class cs_NotificationsSnoozeTimeLimitExceeded extends \Exception {

	/** @var int время для следующей попытки */
	private int $max_time_limit;

	/**
	 * cs_NotificationsShutdownLimitExceeded constructor.
	 */
	public function __construct(int $max_time_limit, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->max_time_limit = $max_time_limit;
		parent::__construct($message, $code, $previous);
	}

	public function getMaxTimeLimit():int {

		return $this->max_time_limit;
	}
}

/**
 * Некорректное время для отключения уведомлений
 */
class cs_SnoozeTimeIntervalLessThenMinute extends \Exception {

}

/**
 * ошибка при некорретных данных переключения режима событий
 */
class cs_IncorrectNotificationToggleData extends \Exception {

}

/**
 * невалидное status для профиля
 */
class cs_InvalidProfileStatus extends \Exception {

}

/**
 * невалидное badge для профиля
 */
class cs_InvalidProfileBadge extends \Exception {

}

/**
 * невалидное mbti для профиля
 */
class cs_InvalidProfileMbti extends \Exception {

}

/**
 * невалидное join time для профиля
 */
class cs_InvalidProfileJoinTime extends \Exception {

}

/**
 * неверная подпись
 */
class cs_WrongSignature extends \Exception {

}

/**
 * задача на удаление участника из компании в статусе "in progress"
 */
class cs_MemberExitTaskInProgress extends \Exception {

}

/**
 * Компания удалена
 */
class cs_CompanyIsDeleted extends \Exception {

}

/**
 * Компании не существует
 */
class cs_CompanyIsNotExist extends \Exception {

}

/**
 * Пытаемся убрать редактора администратора
 */
class cs_AdministrationNotIsDeletingEditor extends \Exception {

}

/**
 * Таблица не пуста
 */
class cs_TableIsNotEmpty extends \Exception {

}

/**
 * Все пользователя из массива кикнутые
 */
class cs_AllUserKicked extends \Exception {

}

/**
 * mocked-данные не найдены
 */
class cs_MockedDataIsNotFound extends \Exception {

}