<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с кодом при смене почты
 */
class Domain_User_Entity_ChangeMail_CodeStory {

	protected Struct_Db_PivotMail_MailChangeViaCodeStory $change_mail_via_code_story;

	public const STATUS_ACTIVE  = 0; // не завершенное подтверждение
	public const STATUS_SUCCESS = 1; // успешное подтверждение

	public const STAGE_START      = 11; // начало процесса
	public const STAGE_CODE_ADDED = 12; // процесс завершен успешно

	public const MAX_ERROR_COUNT   = 7; // максимальное кол-во ошибок
	public const MAX_RESEND_COUNT  = 7; // максимальное кол-во переотправки
	public const NEXT_RESEND_AFTER = 60 * 2; // время, через которое доступна переотправка
	public const EXPIRE_AFTER      = 60 * 20; // время жизни истории

	public const STORY_NAME = "change_mail_via_code_story"; // ключ истории

	/**
	 * Domain_User_Entity_ChangeMail_CodeStory constructor.
	 *
	 * @param Struct_Db_PivotMail_MailChangeViaCodeStory $change_mail_via_code_story
	 */
	public function __construct(Struct_Db_PivotMail_MailChangeViaCodeStory $change_mail_via_code_story) {

		$this->change_mail_via_code_story = $change_mail_via_code_story;
	}

	/**
	 * Создать данные для новой смс при добавлении почты
	 */
	public static function createNewCodeStory(
		string $change_mail_story_map,
		string $mail,
		string $new_mail,
		int    $stage,
		int    $status,
		string $message_id,
		string $code
	):self {

		try {
			$change_mail_story_id = Type_Pack_ChangeMailStory::getId($change_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		try {
			$code_hash = Type_Hash_Code::makeHash($code);
		} catch (cs_IncorrectSaltVersion) {
			throw new ParseFatalException("fatal error make hash");
		}

		return new static(
			new Struct_Db_PivotMail_MailChangeViaCodeStory(
				$change_mail_story_id,
				$mail,
				$new_mail,
				$status,
				$stage,
				0,
				0,
				time(),
				0,
				time() + self::NEXT_RESEND_AFTER,
				$message_id,
				$code_hash,
			)
		);
	}

	/**
	 * Получить запись по сессии
	 * @throws cs_CacheIsEmpty
	 */
	public static function getFromSessionCache(string $session_uniq, int $change_story_stage):self {

		// получаем значение из кеша, если есть, иначе дальше начнем процесс
		$key   = self::_getMemcacheStoryKey($session_uniq, $change_story_stage);
		$cache = Type_Session_Main::getCache($key);

		if (is_array($cache) && $cache === [] || is_bool($cache)) {
			throw new cs_CacheIsEmpty();
		}

		return new static(
			new Struct_Db_PivotMail_MailChangeViaCodeStory(...array_values($cache))
		);
	}

	/**
	 * Сохранить в кэше сессии
	 */
	public function storeInSessionCache(string $session_uniq, int $change_story_stage):void {

		$key = self::_getMemcacheStoryKey($session_uniq, $change_story_stage);
		Type_Session_Main::setCache($key, (array) $this->change_mail_via_code_story, self::EXPIRE_AFTER);
	}

	/**
	 * Удалить запись по сессии
	 */
	public function deleteSessionCache(string $session_uniq, int $change_story_stage):void {

		// получаем значение из кеша, если есть, иначе дальше начнем процесс
		$key = self::_getMemcacheStoryKey($session_uniq, $change_story_stage);
		Type_Session_Main::clearCache($key);
	}

	/**
	 * Получить запись про код
	 *
	 * @throws ParseFatalException
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
	 */
	public static function getActive(string $change_mail_story_map, string $mail):self {

		try {

			$code_story = Gateway_Db_PivotMail_MailChangeViaCodeStory::getOne($change_mail_story_map, $mail);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound("code story not found");
		}

		return new static($code_story);
	}

	/**
	 * Создать новый объект из существующего с обновлением
	 */
	public static function updateStoryData(Struct_Db_PivotMail_MailChangeViaCodeStory $code_story, array $set):self {

		return new static(
			new Struct_Db_PivotMail_MailChangeViaCodeStory(
				$set["change_mail_story_id"] ?? $code_story->change_mail_story_id,
				$set["mail"] ?? $code_story->mail,
				$set["mail_new"] ?? $code_story->mail_new,
				$set["status"] ?? $code_story->status,
				$set["stage"] ?? $code_story->stage,
				$set["resend_count"] ?? $code_story->resend_count,
				$set["error_count"] ?? $code_story->error_count,
				$set["created_at"] ?? $code_story->created_at,
				$set["updated_at"] ?? $code_story->updated_at,
				$set["next_resend_at"] ?? $code_story->next_resend_at,
				$set["message_id"] ?? $code_story->message_id,
				$set["code_hash"] ?? $code_story->code_hash,
			)
		);
	}

	/**
	 * Обновляем свойства сущности, в том числе и в базе
	 */
	public function updateEntity(Struct_Db_PivotMail_MailChangeViaCodeStory $code_story, array $update_field_list):static {

		$set = [];
		foreach ($update_field_list as $field => $value) {

			if (!property_exists(Struct_Db_PivotMail_MailChangeViaCodeStory::class, $field)) {
				throw new ParseFatalException("attempt to set unknown field");
			}

			$this->change_mail_via_code_story->$field = $value;
			$set[$field]                              = $value;
		}

		Gateway_Db_PivotMail_MailChangeViaCodeStory::setById($code_story->change_mail_story_id, $code_story->mail, $set);

		return $this;
	}

	/**
	 * Получить данные записи о добавлении почты телефона
	 */
	public function getCodeStoryData():Struct_Db_PivotMail_MailChangeViaCodeStory {

		return $this->change_mail_via_code_story;
	}

	/**
	 * Ожидаем, что процесс не завершен успешно
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryIsSuccess
	 */
	public function assertNotSuccess():self {

		if ($this->change_mail_via_code_story->status === self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Security_Mail_Change_CodeStoryIsSuccess("already success");
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс не завершен успешно
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryIsNotConfirmStage
	 */
	public function assertConfirmStage():self {

		if ($this->change_mail_via_code_story->status !== self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Security_Mail_Change_CodeStoryIsNotConfirmStage("not success");
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс активный
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryIsNotActive
	 */
	public function assertActive():self {

		if ($this->change_mail_via_code_story->status !== self::STATUS_ACTIVE) {
			throw new Domain_User_Exception_Security_Mail_Change_CodeStoryIsNotActive("status not active");
		}

		return $this;
	}

	/**
	 * Проверяем, превышено ли кол-во переотправок
	 *
	 * @throws Domain_User_Exception_Mail_CodeResendCountExceeded
	 */
	public function assertResendCountNotExceeded(int $next_attempt):self {

		if ($this->change_mail_via_code_story->resend_count >= self::MAX_RESEND_COUNT) {
			throw new Domain_User_Exception_Mail_CodeResendCountExceeded($next_attempt);
		}

		return $this;
	}

	/**
	 * Проверяем, достигнут ли лимит ввода неверного кода
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded
	 */
	public function assertCodeErrorCountLimitNotExceeded():self {

		if ($this->change_mail_via_code_story->error_count >= self::MAX_ERROR_COUNT) {
			throw new Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded(
				$this->change_mail_via_code_story->next_resend_at,
				"code error count limit exceeded");
		}

		return $this;
	}

	/**
	 * Проверяем, что передан корректный проверочный код
	 */
	public function assertEqualCode(string $confirm_code):self {

		if (!Type_Hash_Code::compareHash($this->change_mail_via_code_story->code_hash, $confirm_code)) {

			throw new cs_WrongCode(
				$this->getAvailableAttempts() - 1,
				$this->change_mail_via_code_story->next_resend_at
			);
		}

		return $this;
	}

	/**
	 * Ожидаем, что почта будет отличаться
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_SameMail
	 */
	public function assertNotEqualMail(string $mail):self {

		if ($this->change_mail_via_code_story->mail_new === $mail) {
			throw new Domain_User_Exception_Security_Mail_Change_SameMail("same mail");
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

	/**
	 * Получить время следующей переотправки
	 */
	public function getNextResend():int {

		return $this->change_mail_via_code_story->next_resend_at;
	}

	/**
	 * Получить доступное кол-во попыток
	 */
	public function getAvailableAttempts():int {

		return self::MAX_ERROR_COUNT - $this->change_mail_via_code_story->error_count;
	}

	/**
	 * Получить доступное кол-во попыток
	 */
	public function getAvailableResends():int {

		return self::MAX_RESEND_COUNT - $this->change_mail_via_code_story->resend_count;
	}

	/**
	 * Получить message_id
	 */
	public function getMessageId():string {

		return $this->change_mail_via_code_story->message_id;
	}

	/**
	 * Получаем почту
	 */
	public function getMail():string {

		return $this->change_mail_via_code_story->mail;
	}

	/**
	 * Обрабатываем неверный ввод кода
	 *
	 * @throws ParseFatalException
	 */
	public function handleWrongCode(Struct_Db_PivotMail_MailChangeViaCodeStory $code_story):static {

		self::updateEntity($code_story, [
			"error_count" => $this->change_mail_via_code_story->error_count + 1,
			"updated_at"  => time(),
		]);

		return $this;
	}

	/**
	 * обрабатываем успех
	 *
	 * @throws ParseFatalException
	 */
	public function handleSuccessCode(Struct_Db_PivotMail_MailChangeViaCodeStory $code_story):static {

		self::updateEntity($code_story, [
			"status"     => self::STATUS_SUCCESS,
			"stage"      => self::STAGE_CODE_ADDED,
			"updated_at" => time(),
		]);

		return $this;
	}

	/**
	 * Получить данные записи
	 */
	public function getStoryData():Struct_Db_PivotMail_MailChangeViaCodeStory {

		return $this->change_mail_via_code_story;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Ключ memcache для story
	 */
	protected static function _getMemcacheStoryKey(string $session_uniq, int $change_story_stage):string {

		return $session_uniq . "_" . self::STORY_NAME . $change_story_stage;
	}
}