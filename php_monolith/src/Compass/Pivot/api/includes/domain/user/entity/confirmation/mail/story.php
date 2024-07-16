<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для получения данных об истории 2fa действий
 *
 * Class Domain_User_Entity_Confirmation_TwoFa_Story
 */
class Domain_User_Entity_Confirmation_Mail_Story {

	public const STORY_NAME = "mail_password_confirm_story"; // ключ истории

	public const ERROR_COUNT_LIMIT = 7; // лимит на кол-во ошибок

	// статусы истории подтверждения действий
	public const STATUS_ACTIVE  = 1;
	public const STATUS_SUCCESS = 2;
	public const STATUS_FAILED  = 3;

	// начало подтверждения действия
	public const STAGE_START = 1;

	// дейвствие подтверждено
	public const STAGE_CONFIRMED = 2;

	// время истечения срока попытки
	public const EXPIRE_TIME = 60 * 20;  // через сколько удалить запись

	protected Struct_Db_PivotMail_MailPasswordConfirmStory $mail_password_confirm_story;

	/**
	 * Domain_User_Entity_Confirmation_TwoFa_Story constructor.
	 *
	 */
	public function __construct(Struct_Db_PivotMail_MailPasswordConfirmStory $mail_password_confirm_story) {

		$this->mail_password_confirm_story = $mail_password_confirm_story;
	}

	/**
	 * Получить по мапе
	 *
	 * @param string $confirm_mail_password_story_map
	 *
	 * @return Domain_User_Entity_Confirmation_Mail_Story
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidMailPasswordStoryKey
	 * @throws ParseFatalException
	 */
	public static function getByMap(string $confirm_mail_password_story_map):self {

		try {

			$mail_password_confirm_story = Gateway_Db_PivotMail_MailPasswordConfirmStory::getOne(
				Type_Pack_MailPasswordConfirmStory::getId($confirm_mail_password_story_map));
		} catch (\cs_RowIsEmpty|\cs_UnpackHasFailed) {
			throw new Domain_User_Exception_Confirmation_Mail_InvalidMailPasswordStoryKey("invalid mail password story key");
		}

		return new self($mail_password_confirm_story);
	}

	/**
	 * Инициализировать из записи в базе
	 *
	 * @param Struct_Db_PivotMail_MailPasswordConfirmStory $mail_password_confirm_story
	 *
	 * @return Domain_User_Entity_Confirmation_Mail_Story
	 */
	public static function init(Struct_Db_PivotMail_MailPasswordConfirmStory $mail_password_confirm_story):self {

		return new self($mail_password_confirm_story);
	}

	/**
	 * Создать новую запись истории
	 *
	 * @param int    $user_id
	 * @param string $session_uniq
	 * @param int    $type
	 *
	 * @return static
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function create(int $user_id, string $session_uniq, int $type):self {

		$story = new static(
			new Struct_Db_PivotMail_MailPasswordConfirmStory(
				null,
				$user_id,
				Domain_User_Entity_Confirmation_Mail_Story::STATUS_ACTIVE,
				$type,
				Domain_User_Entity_Confirmation_Mail_Story::STAGE_START,
				0,
				time(),
				0,
				time() + Domain_User_Entity_Confirmation_Mail_Story::EXPIRE_TIME,
				$session_uniq
			)
		);

		$confirm_mail_password_story_id = Gateway_Db_PivotMail_MailPasswordConfirmStory::insert($story->getMailPasswordConfirmInfo());

		$story->mail_password_confirm_story->confirm_mail_password_story_id = $confirm_mail_password_story_id;

		$story->storeInSessionCache(
			$story->mail_password_confirm_story->session_uniq, $story->mail_password_confirm_story->type);

		return $story;
	}

	/**
	 * Обновить объект из существующего
	 *
	 * @param array $set
	 *
	 * @return static
	 * @throws ParseFatalException
	 */
	public function update(array $set):self {

		$this->mail_password_confirm_story->confirm_mail_password_story_id =
			$set["confirm_mail_password_story_id"] ?? $this->mail_password_confirm_story->confirm_mail_password_story_id;
		$this->mail_password_confirm_story->user_id                        = $set["user_id"] ?? $this->mail_password_confirm_story->user_id;
		$this->mail_password_confirm_story->status                         = $set["status"] ?? $this->mail_password_confirm_story->status;
		$this->mail_password_confirm_story->type                           = $set["type"] ?? $this->mail_password_confirm_story->type;
		$this->mail_password_confirm_story->stage                          = $set["stage"] ?? $this->mail_password_confirm_story->stage;
		$this->mail_password_confirm_story->created_at                     = $set["created_at"] ?? $this->mail_password_confirm_story->created_at;
		$this->mail_password_confirm_story->updated_at                     = $set["updated_at"] ?? $this->mail_password_confirm_story->updated_at;
		$this->mail_password_confirm_story->error_count                    = $set["error_count"] ?? $this->mail_password_confirm_story->error_count;
		$this->mail_password_confirm_story->expires_at                     = $set["expires_at"] ?? $this->mail_password_confirm_story->expires_at;
		$this->mail_password_confirm_story->session_uniq                   = $set["session_uniq"] ?? $this->mail_password_confirm_story->session_uniq;

		Gateway_Db_PivotMail_MailPasswordConfirmStory::set($this->mail_password_confirm_story->confirm_mail_password_story_id, $set);

		$this->storeInSessionCache(
			$this->mail_password_confirm_story->session_uniq, $this->mail_password_confirm_story->type);

		return $this;
	}

	/**
	 * Обрабатываем неверный ввод пароля
	 *
	 * @return $this
	 * @throws ParseFatalException
	 */
	public function handleWrongPassword():static {

		$set["error_count"] = ++$this->mail_password_confirm_story->error_count;
		$set["updated_at"]  = time();

		// если достигли лимита - помечаем попытку заваленной
		if ($this->mail_password_confirm_story->error_count >= self::ERROR_COUNT_LIMIT) {
			$set["status"] = self::STATUS_FAILED;
		}

		$this->update($set);

		return $this;
	}

	/**
	 * Обрабатываем успешный ввод пароля
	 *
	 * @return $this
	 * @throws ParseFatalException
	 */
	public function handleSuccessPassword():static {

		$this->update([
			"stage"      => self::STAGE_CONFIRMED,
			"status"     => self::STATUS_SUCCESS,
			"updated_at" => time(),
		]);

		return $this;
	}

	/**
	 * Проверяем, истекло ли время подтверждения
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_NotExpired
	 */
	public function assertIsExpired():self {

		if ($this->mail_password_confirm_story->expires_at > time()) {
			throw new Domain_User_Exception_Confirmation_Mail_NotExpired("confirmation mail is not expired");
		}

		return $this;
	}

	/**
	 * Проверяем, истекло ли время подтверждения
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_IsExpired
	 */
	public function assertNotExpired():self {

		if ($this->mail_password_confirm_story->expires_at < time()) {
			throw new Domain_User_Exception_Confirmation_Mail_IsExpired("confirmation mail is expired");
		}

		return $this;
	}

	/**
	 * Проверяем, истекло ли время подтверждения
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_IsNotConfirmed
	 */
	public function assertIsConfirmed():self {

		if ($this->mail_password_confirm_story->stage !== self::STAGE_CONFIRMED) {
			throw new Domain_User_Exception_Confirmation_Mail_IsNotConfirmed("confirmation mail is not confirmed");
		}

		return $this;
	}

	/**
	 * Проверяем, истекло ли время подтверждения
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_IsConfirmed
	 */
	public function assertIsNotConfirmed():self {

		if ($this->mail_password_confirm_story->stage === self::STAGE_CONFIRMED) {
			throw new Domain_User_Exception_Confirmation_Mail_IsConfirmed("confirmation mail is not confirmed");
		}

		return $this;
	}

	/**
	 * Проверяем, истекло ли время подтверждения
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_NotSuccess
	 */
	public function assertSuccess():self {

		if ($this->mail_password_confirm_story->stage !== self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Confirmation_Mail_NotSuccess("confirmation mail is not finished");
		}

		return $this;
	}

	/**
	 * Проверяем, истекло ли время подтверждения
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_IsSuccess
	 */
	public function assertNotSuccess():self {

		if ($this->mail_password_confirm_story->stage === self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Confirmation_Mail_IsSuccess("confirmation mail is not finished");
		}

		return $this;
	}

	/**
	 * Проверяем, что попытка ещё активна
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_IsNotActive
	 */
	public function assertActive():self {

		if ($this->mail_password_confirm_story->status !== self::STATUS_ACTIVE) {
			throw new Domain_User_Exception_Confirmation_Mail_IsNotActive("mail confirmation is not active");
		}

		return $this;
	}

	/**
	 * Проверяем, что попытка неактивна
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_IsActive
	 */
	public function assertNotActive():self {

		if ($this->mail_password_confirm_story->status === self::STATUS_ACTIVE) {
			throw new Domain_User_Exception_Confirmation_Mail_IsActive("mail confirmation is active");
		}

		return $this;
	}

	/**
	 * Проверяем, что текущий пользователь и пользователь которому выдали 2fa токен, совпадают
	 *
	 * @param int $user_id
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidUser
	 */
	public function assertCorrectUser(int $user_id):self {

		if ($this->mail_password_confirm_story->user_id !== $user_id) {
			throw new Domain_User_Exception_Confirmation_Mail_InvalidUser("confirmation mail invalid user");
		}

		return $this;
	}

	/**
	 * Проверяем, что лимит ошибок не превышен
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_ErrorCountExceeded
	 */
	public function assertErrorCountLimitNotExceeded():self {

		if ($this->mail_password_confirm_story->error_count >= self::ERROR_COUNT_LIMIT) {
			throw new Domain_User_Exception_Confirmation_Mail_ErrorCountExceeded($this->getMailPasswordConfirmInfo()->expires_at);
		}

		return $this;
	}

	/**
	 * Проверяем, тип действия совпадает
	 *
	 * @param int $type
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_IsInvalidType
	 */
	public function assertTypeIsValid(int $type):self {

		if ($this->mail_password_confirm_story->type !== $type) {
			throw new Domain_User_Exception_Confirmation_Mail_IsInvalidType("invalid confirmation type");
		}

		return $this;
	}

	/**
	 * Получить данные об 2fa
	 *
	 */
	public function getMailPasswordConfirmInfo():Struct_Db_PivotMail_MailPasswordConfirmStory {

		return $this->mail_password_confirm_story;
	}

	/**
	 * Получаем доступное кол-во попыток
	 *
	 */
	public function getAvailableAttempts():int {

		return self::ERROR_COUNT_LIMIT - $this->mail_password_confirm_story->error_count;
	}

	/**
	 * Получить запись по сессии
	 *
	 * @param string $session_uniq
	 * @param int    $type
	 *
	 * @return Domain_User_Entity_Confirmation_Mail_Story
	 *
	 * @throws Domain_User_Exception_CacheIsEmpty
	 */
	public static function getFromSessionCache(string $session_uniq, int $type):self {

		// получаем значение из кеша, если есть, иначе дальше начнем процесс
		$key   = self::_getMemcacheStoryKey($session_uniq, $type);
		$cache = Type_Session_Main::getCache($key);

		if ($cache === [] || is_bool($cache)) {
			throw new Domain_User_Exception_CacheIsEmpty("cache is empty");
		}

		return new static(
			Struct_Db_PivotMail_MailPasswordConfirmStory::fromArray($cache)
		);
	}

	/**
	 * Сохранить в кэше сессии
	 *
	 */
	public function storeInSessionCache(string $session_uniq, int $type):void {

		$key = self::_getMemcacheStoryKey($session_uniq, $type);
		Type_Session_Main::setCache($key, (array) $this->mail_password_confirm_story, self::EXPIRE_TIME);
	}

	##########################################################
	# region PROTECTED
	##########################################################

	/**
	 * Генерируем ключ для сохранения в кеш
	 *
	 * @param string $session_uniq
	 * @param int    $action_type
	 *
	 * @return string
	 */
	protected static function _getMemcacheStoryKey(string $session_uniq, int $action_type):string {

		return $session_uniq . "_" . $action_type . "_" . self::STORY_NAME;
	}
}
