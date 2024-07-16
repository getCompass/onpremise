<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы с добавлением почты
 */
class Domain_User_Entity_Security_AddMail_Story {

	protected Struct_Db_PivotMail_MailAddStory $add_mail_story;
	protected string                           $mail;

	public const STATUS_ACTIVE  = 1; // активно
	public const STATUS_SUCCESS = 2; // успешно завершено
	public const STATUS_FAIL    = 3; // завершено с ошибкой

	public const STAGE_BEGIN        = 1; // старт процесса
	public const STAGE_SET_PASSWORD = 2; // установка пароля
	public const STAGE_WRONG_CODE   = 3; // ввод проверочного кода

	public const EXPIRE_AFTER = 60 * 20; // через сколько истекает

	public const ACTION_TYPE = "add"; // тип действия

	/**
	 * Domain_User_Entity_Security_AddMail_Story constructor.
	 */
	public function __construct(Struct_Db_PivotMail_MailAddStory $add_mail_story, string $mail) {

		$this->add_mail_story = $add_mail_story;
		$this->mail           = $mail;
	}

	/**
	 * Создать данные для добавления номера телефона
	 */
	public static function createNewStory(int $user_id, string $session_uniq, string $mail):self {

		return new static(
			new Struct_Db_PivotMail_MailAddStory(
				null,
				$user_id,
				self::STATUS_ACTIVE,
				self::STAGE_BEGIN,
				time(),
				0,
				0,
				time() + self::EXPIRE_AFTER,
				$session_uniq,
			),
			$mail
		);
	}

	/**
	 * Создать новый объект из существующего с обновлением
	 */
	public static function updateStoryData(string $mail, Struct_Db_PivotMail_MailAddStory $story, array $set):self {

		return new static(
			new Struct_Db_PivotMail_MailAddStory(
				$set["add_mail_story_id"] ?? $story->add_mail_story_id,
				$set["user_id"] ?? $story->user_id,
				$set["status"] ?? $story->status,
				$set["stage"] ?? $story->stage,
				$set["created_at"] ?? $story->created_at,
				$set["updated_at"] ?? $story->updated_at,
				$set["error_count"] ?? $story->error_count,
				$set["expires_at"] ?? $story->expires_at,
				$set["session_uniq"] ?? $story->session_uniq,
			),
			$mail,
		);
	}

	/**
	 * Сохранить в кэше сессии
	 */
	public function storeInSessionCache(string $mail):void {

		Type_Session_Main::setCache($mail . self::class, (array) $this->add_mail_story, self::EXPIRE_AFTER);
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
			new Struct_Db_PivotMail_MailAddStory(...array_values($cached_story)),
			$mail
		);
	}

	/**
	 * Получить данные записи о добавлении номера телефона
	 *
	 * @return Struct_Db_PivotMail_MailAddStory
	 */
	public function getStoryData():Struct_Db_PivotMail_MailAddStory {

		return $this->add_mail_story;
	}

	/**
	 * Ожидаем, что процесс не истек
	 *
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 */
	public function assertNotExpire():self {

		if ($this->add_mail_story->expires_at < time()) {
			throw new Domain_User_Exception_Mail_StoryIsExpired("is expired");
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс не завершен успешно
	 *
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 */
	public function assertNotSuccess():self {

		if ($this->add_mail_story->status === self::STATUS_SUCCESS) {
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

		if ($this->add_mail_story->status !== self::STATUS_ACTIVE) {
			throw new Domain_User_Exception_Mail_StoryIsNotActive("status not active");
		}

		return $this;
	}

	/**
	 * Получить map записи
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public function getStoryMap():string {

		try {

			return Type_Pack_AddMailStory::doPack(
				$this->add_mail_story->add_mail_story_id,
				Type_Pack_AddMailStory::getShardIdByTime($this->add_mail_story->created_at),
				$this->mail,
				$this->add_mail_story->created_at,
			);
		} catch (cs_IncorrectSaltVersion|\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}
	}

	/**
	 * Получаем временную метку истекания попытки
	 */
	public function getExpiresAt():int {

		return $this->add_mail_story->expires_at;
	}

	/**
	 * Проверяем, что начатое добавление номера принадлежит пользователю
	 *
	 * @throws Domain_User_Exception_UserNotAuthorized
	 */
	public function assertUserAuthorized(int $user_id):self {

		if ($this->add_mail_story->user_id !== $user_id) {
			throw new Domain_User_Exception_UserNotAuthorized();
		}

		return $this;
	}

	/**
	 * Удалить запись по сессии
	 *
	 * @param string $mail
	 */
	public static function deleteSessionCache(string $mail):void {

		// получаем значение из кеша, если есть, иначе дальше начнем процесс
		Type_Session_Main::clearCache($mail . self::class);
	}

	/**
	 * Получить запись
	 *
	 * @param string $add_mail_story_map
	 * @param string $mail
	 *
	 * @return Domain_User_Entity_Security_AddMail_Story
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 */
	public static function get(string $add_mail_story_map, string $mail):self {

		try {
			$story = Gateway_Db_PivotMail_MailAddStory::getOne($add_mail_story_map);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Mail_StoryNotFound("story not found");
		}

		return new static($story, $mail);
	}

	/**
	 * Получить запись
	 *
	 * @param string $add_mail_story_map
	 * @param string $mail
	 *
	 * @return Domain_User_Entity_Security_AddMail_Story
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 */
	public static function getForUpdate(string $add_mail_story_map, string $mail):self {

		try {
			$story = Gateway_Db_PivotMail_MailAddStory::getForUpdate($add_mail_story_map);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Mail_StoryNotFound("story not found");
		}

		return new static($story, $mail);
	}
}