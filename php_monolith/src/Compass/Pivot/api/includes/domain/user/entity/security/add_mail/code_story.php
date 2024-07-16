<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с смс при добавлении почты
 */
class Domain_User_Entity_Security_AddMail_CodeStory {

	protected Struct_Db_PivotMail_MailAddViaCodeStory $add_mail_via_code_story;
	protected string                                  $mail;

	public const STATUS_ACTIVE  = 1; // активно
	public const STATUS_SUCCESS = 2; // успешно завершено

	public const STAGE_WRONG_CODE = 3; // ввод проверочного кода

	public const MAX_ERROR_COUNT  = 7; // максимальное кол-во ошибок
	public const MAX_RESEND_COUNT = 7; // максимальное кол-во переотправки

	public const NEXT_RESEND_AFTER = 60; // время, через которое доступна переотправка
	public const EXPIRE_AFTER      = 60 * 20; // время жизни истории

	/**
	 * Domain_User_Entity_Security_AddMail_CodeStory constructor.
	 *
	 * @param Struct_Db_PivotMail_MailAddViaCodeStory $add_mail_via_code_story
	 * @param string                                  $mail
	 */
	public function __construct(Struct_Db_PivotMail_MailAddViaCodeStory $add_mail_via_code_story, string $mail) {

		$this->add_mail_via_code_story = $add_mail_via_code_story;
		$this->mail                    = $mail;
	}

	/**
	 * Создать данные для новой смс при добавлении почты
	 */
	public static function createNewCodeStory(
		string $add_mail_story_map,
		string $mail,
		int    $stage,
		string $message_id,
		string $code
	):self {

		try {
			$add_mail_story_id = Type_Pack_AddMailStory::getId($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		try {
			$code_hash = Type_Hash_Code::makeHash($code);
		} catch (cs_IncorrectSaltVersion) {
			throw new ParseFatalException("fatal error make hash");
		}

		return new static(
			new Struct_Db_PivotMail_MailAddViaCodeStory(
				$add_mail_story_id,
				$mail,
				self::STATUS_ACTIVE,
				$stage,
				0,
				0,
				time(),
				0,
				time() + self::NEXT_RESEND_AFTER,
				$message_id,
				$code_hash,
			),
			$mail
		);
	}

	/**
	 * Получить запись по сессии
	 * @throws cs_CacheIsEmpty
	 */
	public static function getFromSessionCache(string $mail):self {

		$cached_story = Type_Session_Main::getCache($mail . self::class);

		if ($cached_story === []) {
			throw new cs_CacheIsEmpty();
		}

		return new static(
			new Struct_Db_PivotMail_MailAddViaCodeStory(...array_values($cached_story)),
			$mail
		);
	}

	/**
	 * Сохранить в кэше сессии
	 */
	public function storeInSessionCache(string $mail):void {

		Type_Session_Main::setCache($mail . self::class, (array) $this->add_mail_via_code_story, self::EXPIRE_AFTER);
	}

	/**
	 * Создать новый объект из существующего с обновлением
	 */
	public static function updateStoryData(string $mail, Struct_Db_PivotMail_MailAddViaCodeStory $code_story, array $set):self {

		return new static(
			new Struct_Db_PivotMail_MailAddViaCodeStory(
				$set["add_mail_story_id"] ?? $code_story->add_mail_story_id,
				$set["mail"] ?? $code_story->mail,
				$set["status"] ?? $code_story->status,
				$set["stage"] ?? $code_story->stage,
				$set["resend_count"] ?? $code_story->resend_count,
				$set["error_count"] ?? $code_story->error_count,
				$set["created_at"] ?? $code_story->created_at,
				$set["updated_at"] ?? $code_story->updated_at,
				$set["next_resend_at"] ?? $code_story->next_resend_at,
				$set["message_id"] ?? $code_story->message_id,
				$set["code_hash"] ?? $code_story->code_hash,
			),
			$mail
		);
	}

	/**
	 * Получить данные записи о добавлении почты телефона
	 */
	public function getCodeStoryData():Struct_Db_PivotMail_MailAddViaCodeStory {

		return $this->add_mail_via_code_story;
	}

	/**
	 * Ожидаем, что процесс не завершен успешно
	 *
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 */
	public function assertNotSuccess():self {

		if ($this->add_mail_via_code_story->status === self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Mail_StoryIsSuccess("already success");
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс активный
	 *
	 * @throws Domain_User_Exception_Mail_StoryIsNotActive
	 */
	public function assertActive():self {

		if ($this->add_mail_via_code_story->status !== self::STATUS_ACTIVE) {
			throw new Domain_User_Exception_Mail_StoryIsNotActive("status not active");
		}

		return $this;
	}

	/**
	 * Проверяем что стадия ввода кода
	 *
	 * @throws Domain_User_Exception_Mail_StoryNotEqualStage
	 */
	public function assertEnteringCodeStage():self {

		if ($this->add_mail_via_code_story->stage !== self::STAGE_WRONG_CODE) {
			throw new Domain_User_Exception_Mail_StoryNotEqualStage("already success");
		}

		return $this;
	}

	/**
	 * Проверяем, превышено ли кол-во ошибок
	 *
	 * @throws Domain_User_Exception_Mail_CodeErrorCountExceeded
	 */
	public function assertErrorCountNotExceeded():self {

		if ($this->add_mail_via_code_story->error_count >= self::MAX_ERROR_COUNT) {
			throw new Domain_User_Exception_Mail_CodeErrorCountExceeded("resend count not exceeded");
		}

		return $this;
	}

	/**
	 * Проверяем, превышено ли кол-во переотправок
	 *
	 * @throws Domain_User_Exception_Mail_CodeResendCountExceeded
	 */
	public function assertResendCountNotExceeded(int $next_attempt):self {

		if ($this->add_mail_via_code_story->resend_count >= self::MAX_RESEND_COUNT) {
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

	/**
	 * Получить время следующей переотправки
	 */
	public function getNextResend():int {

		return $this->add_mail_via_code_story->next_resend_at;
	}

	/**
	 * Получить доступное кол-во попыток
	 */
	public function getAvailableAttempts():int {

		return self::MAX_ERROR_COUNT - $this->add_mail_via_code_story->error_count;
	}

	/**
	 * Получить доступное кол-во попыток
	 */
	public function getAvailableResends():int {

		return self::MAX_RESEND_COUNT - $this->add_mail_via_code_story->resend_count;
	}

	/**
	 * Получить message_id
	 */
	public function getMessageId():string {

		return $this->add_mail_via_code_story->message_id;
	}

	/**
	 * Получить номер телефона
	 */
	public function getMailNumber():string {

		return $this->add_mail_via_code_story->mail;
	}

	/**
	 * Проверяем код из почты
	 *
	 * @throws cs_WrongCode
	 */
	public function assertEqualCode(string $code):self {

		try {

			if (!Type_Hash_Code::compareHash($this->add_mail_via_code_story->code_hash, $code)) {
				throw new cs_WrongCode(
					$this->getAvailableAttempts() - 1,
					$this->add_mail_via_code_story->next_resend_at
				);
			}
		} catch (cs_IncorrectSaltVersion|cs_InvalidHashStruct) {
			throw new ParseFatalException("fatal error parse hash");
		}

		return $this;
	}

	/**
	 * Получить запись
	 */
	public static function get(string $add_mail_story_map, string $mail):self {

		try {
			$story = Gateway_Db_PivotMail_MailAddViaCodeStory::getOne($add_mail_story_map, $mail);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Mail_StoryNotFound("story code not found");
		}

		return new static($story, $mail);
	}
}