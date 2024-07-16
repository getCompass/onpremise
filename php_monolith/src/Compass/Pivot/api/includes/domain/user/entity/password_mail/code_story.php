<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с паролям связанной с почтой
 */
class Domain_User_Entity_PasswordMail_CodeStory {

	protected Struct_Db_PivotMail_MailPasswordViaCodeStory $password_mail_via_code_story;

	public const STATUS_ACTIVE  = 1; // активный
	public const STATUS_SUCCESS = 2; // успешно завершенный

	public const STAGE_START        = 11; // начало процесса
	public const STAGE_CODE_ADDED   = 12; // процесс завершен успешно
	public const NEXT_ATTEMPT_AFTER = 60; // через сколько доступна переотправка кода
	public const STORY_LIFE_TIME    = 60 * 20; // через сколько истекает история
	public const MAX_RESEND_COUNT   = 7;
	public const STORY_NAME         = "password_mail_via_code_story"; // ключ истории

	/**
	 * Struct_Db_PivotMail_MailPasswordViaCodeStory constructor.
	 *
	 * @param Struct_Db_PivotMail_MailPasswordViaCodeStory $password_mail_via_code_story
	 */
	public function __construct(Struct_Db_PivotMail_MailPasswordViaCodeStory $password_mail_via_code_story) {

		$this->password_mail_via_code_story = $password_mail_via_code_story;
	}

	/**
	 * Получить запись по сессии
	 */
	public static function getFromSessionCache(string $session_uniq, int $type):self {

		// получаем значение из кеша, если есть, иначе дальше начнем процесс
		$key   = self::_getMemcacheStoryKey($session_uniq, $type);
		$cache = Type_Session_Main::getCache($key);

		if (is_array($cache) && $cache === [] || is_bool($cache)) {
			throw new cs_CacheIsEmpty();
		}

		return new static(
			new Struct_Db_PivotMail_MailPasswordViaCodeStory(...array_values($cache))
		);
	}

	/**
	 * Удалить запись по сессии
	 *
	 * @param string $session_uniq
	 * @param int    $type
	 */
	public static function deleteSessionCache(string $session_uniq, int $type):void {

		// получаем значение из кеша, если есть, иначе дальше начнем процесс
		$key = self::_getMemcacheStoryKey($session_uniq, $type);
		Type_Session_Main::clearCache($key);
	}

	/**
	 * Создать данные для новой записи истории
	 */
	public static function createNewStory(int $password_mail_story_id, string $mail, string $confirm_code, string $mail_id):self {

		return new static(
			new Struct_Db_PivotMail_MailPasswordViaCodeStory(
				password_mail_story_id: $password_mail_story_id,
				mail: $mail,
				status: self::STATUS_ACTIVE,
				type: Domain_User_Entity_PasswordMail_Story::TYPE_RESET_PASSWORD,
				stage: self::STAGE_START,
				resend_count: 0,
				error_count: 0,
				created_at: time(),
				updated_at: 0,
				next_resend_at: time() + self::NEXT_ATTEMPT_AFTER,
				message_id: $mail_id,
				code_hash: Type_Hash_Code::makeHash($confirm_code),
			)
		);
	}

	/**
	 * Обновляем свойства сущности, в том числе и в базе
	 *
	 * @throws ParseFatalException
	 */
	public function updateEntity(Struct_Db_PivotMail_MailPasswordViaCodeStory $story, array $update_field_list):static {

		$set = [];
		foreach ($update_field_list as $field => $value) {

			if (!property_exists(Struct_Db_PivotMail_MailPasswordViaCodeStory::class, $field)) {
				throw new ParseFatalException("attempt to set unknown field");
			}

			$this->password_mail_via_code_story->$field = $value;
			$set[$field]                                = $value;
		}

		Gateway_Db_PivotMail_MailPasswordViaCodeStory::setById($story->password_mail_story_id, $set);

		return $this;
	}

	/**
	 * Обрабатываем неверный ввод кода
	 *
	 * @throws ParseFatalException
	 */
	public function handleWrongCode(Struct_Db_PivotMail_MailPasswordViaCodeStory $code_story):static {

		self::updateEntity($code_story, [
			"error_count" => $this->password_mail_via_code_story->error_count + 1,
			"updated_at"  => time(),
		]);

		return $this;
	}

	/**
	 * Обрабатываем успешный ввод кода
	 *
	 * @throws ParseFatalException
	 */
	public function handleSuccessCode(Struct_Db_PivotMail_MailPasswordViaCodeStory $code_story):static {

		self::updateEntity($code_story, [
			"stage"      => self::STAGE_CODE_ADDED,
			"status"     => self::STATUS_SUCCESS,
			"updated_at" => time(),
		]);

		return $this;
	}

	/**
	 * Получить запись про код
	 *
	 * @throws ParseFatalException
	 * @throws Domain_User_Exception_Password_NotFound
	 */
	public static function getActive(string $password_mail_story_map):self {

		try {

			$code_story = Gateway_Db_PivotMail_MailPasswordViaCodeStory::getOne($password_mail_story_map);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Password_NotFound("code not found");
		}

		return new static($code_story);
	}

	/**
	 * Сохранить в кэше сессии
	 */
	public function storeInSessionCache(string $session_uniq, int $type):void {

		$key = self::_getMemcacheStoryKey($session_uniq, $type);
		Type_Session_Main::setCache($key, (array) $this->password_mail_via_code_story, self::STORY_LIFE_TIME);
	}

	/**
	 * Получить данные записи
	 */
	public function getStoryData():Struct_Db_PivotMail_MailPasswordViaCodeStory {

		return $this->password_mail_via_code_story;
	}

	/**
	 * Ожидаем, что процесс активен
	 */
	public function assertActive():self {

		if ($this->password_mail_via_code_story->status !== self::STATUS_ACTIVE) {
			throw new Domain_User_Exception_Password_StoryIsNotActive("story is not active");
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс не завершен успешно
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Password_StoryIsSuccess
	 */
	public function assertNotSuccess():self {

		if ($this->password_mail_via_code_story->status === self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Password_StoryIsSuccess("story is success");
		}

		return $this;
	}

	/**
	 * Получить этап
	 */
	public function getStage():int {

		return $this->password_mail_via_code_story->stage;
	}

	/**
	 * Получить время следующей переотправки
	 */
	public function getNextResend():int {

		return $this->password_mail_via_code_story->next_resend_at;
	}

	/**
	 * Получить доступное кол-во попыток
	 */
	public function getAvailableAttempts():int {

		return self::MAX_RESEND_COUNT - $this->password_mail_via_code_story->error_count;
	}

	/**
	 * Проверяем, достигнут ли лимит ввода неверного кода
	 *
	 * @throws Domain_User_Exception_Password_ErrorCountLimitExceeded
	 */
	public function assertCodeErrorCountLimitNotExceeded():self {

		if ($this->password_mail_via_code_story->error_count >= self::MAX_RESEND_COUNT) {
			throw new Domain_User_Exception_Password_ErrorCountLimitExceeded(
				$this->password_mail_via_code_story->next_resend_at,
				"code error count limit exceeded");
		}

		return $this;
	}

	/**
	 * проверяем, что передан корректный проверочный код
	 */
	public function assertEqualCode(string $confirm_code):self {

		if (!Type_Hash_Code::compareHash($this->password_mail_via_code_story->code_hash, $confirm_code)) {

			throw new cs_WrongCode(
				$this->getAvailableAttempts() - 1,
				$this->password_mail_via_code_story->next_resend_at
			);
		}

		return $this;
	}

	/**
	 * проверяем, был ли ранее успешно введен код
	 *
	 * @throws Domain_User_Exception_Password_StageNotAllowed
	 */
	public function assertHasCode():self {

		if ($this->password_mail_via_code_story->status !== self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Password_StageNotAllowed("stage not allowed");
		}

		return $this;
	}

	/**
	 * Получить доступное кол-во попыток
	 */
	public function getAvailableResends():int {

		return self::MAX_RESEND_COUNT - $this->password_mail_via_code_story->resend_count;
	}

	/**
	 * Проверяем, превышено ли кол-во переотправок
	 *
	 * @throws Domain_User_Exception_Mail_CodeResendCountExceeded
	 */
	public function assertResendCountNotExceeded(int $next_attempt):self {

		if ($this->password_mail_via_code_story->resend_count >= self::MAX_RESEND_COUNT) {
			throw new Domain_User_Exception_Mail_CodeResendCountExceeded($next_attempt);
		}

		return $this;
	}

	/**
	 * Проверяем время переотправки
	 *
	 */
	public function assertResendIsAvailable(int $next_attempt):self {

		// игнорируем проверку в тестах, если нужно
		if (ServerProvider::isTest() && Type_System_Testing::isIgnoreMailResendException()) {
			return $this;
		}

		if (time() < self::getNextResend()) {
			throw new Domain_User_Exception_Mail_CodeResendNotAvailable($next_attempt);
		}

		return $this;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * ключ memcache для story
	 *
	 * @param string $session_uniq
	 * @param int    $type
	 *
	 * @return string
	 */
	protected static function _getMemcacheStoryKey(string $session_uniq, int $type):string {

		return $session_uniq . "_" . self::STORY_NAME . "_" . $type;
	}

}