<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы со сменой почты
 */
class Domain_User_Entity_ChangeMail_Story {

	protected Struct_Db_PivotMail_MailChangeStory $change_mail_story;

	public const STATUS_ACTIVE  = 1; // активно
	public const STATUS_SUCCESS = 2; // успешно завершено
	public const STATUS_FAIL    = 3; // завершено с ошибкой

	public const STAGE_FIRST    = 1; // первый этап - старая почта
	public const STAGE_SECOND   = 2; // второй этап - новая почта
	public const STAGE_FINISHED = 3; // третий этап - закончили

	public const EXPIRE_AFTER = 60 * 20; // через сколько истекает

	public const STORY_NAME  = "change_mail_story"; // ключ истории
	public const ACTION_TYPE = "change";            // тип действия

	/**
	 * Struct_Db_PivotMail_MailChangeStory constructor.
	 *
	 * @param Struct_Db_PivotMail_MailChangeStory $change_mail_story
	 */
	public function __construct(Struct_Db_PivotMail_MailChangeStory $change_mail_story) {

		$this->change_mail_story = $change_mail_story;
	}

	/**
	 * Создать данные для новой записи истории
	 */
	public static function createNewStory(int $user_id, string $session_uniq, string $stage):self {

		return new static(
			new Struct_Db_PivotMail_MailChangeStory(
				null,
				$user_id,
				self::STATUS_ACTIVE,
				$stage,
				time(),
				0,
				0,
				time() + self::EXPIRE_AFTER,
				$session_uniq,
			)
		);
	}

	/**
	 * Обновить объект из существующего
	 */
	public static function updateStory(Struct_Db_PivotMail_MailChangeStory $story, array $set):self {

		return new static(
			new Struct_Db_PivotMail_MailChangeStory(
				$set["change_mail_story_id"] ?? $story->change_mail_story_id,
				$set["user_id"] ?? $story->user_id,
				$set["status"] ?? $story->status,
				$set["stage"] ?? $story->stage,
				$set["created_at"] ?? $story->created_at,
				$set["updated_at"] ?? $story->updated_at,
				$set["error_count"] ?? $story->error_count,
				$set["expires_at"] ?? $story->expires_at,
				$set["session_uniq"] ?? $story->session_uniq,
			),
		);
	}

	/**
	 * Обновляем свойства сущности, в том числе и в базе
	 */
	public function updateEntity(Struct_Db_PivotMail_MailChangeStory $story, array $update_field_list):static {

		$set = [];
		foreach ($update_field_list as $field => $value) {

			if (!property_exists(Struct_Db_PivotMail_MailChangeStory::class, $field)) {
				throw new ParseFatalException("attempt to set unknown field");
			}

			$this->change_mail_story->$field = $value;
			$set[$field]                     = $value;
		}

		Gateway_Db_PivotMail_MailChangeStory::setById($story->change_mail_story_id, $set);

		return $this;
	}

	/**
	 * Получить запись по сессии
	 * @throws cs_CacheIsEmpty
	 */
	public static function getFromSessionCache(string $session_uniq):self {

		// получаем значение из кеша, если есть, иначе дальше начнем процесс
		$key   = self::_getMemcacheStoryKey($session_uniq);
		$cache = Type_Session_Main::getCache($key);

		if (is_array($cache) && $cache === [] || is_bool($cache)) {
			throw new cs_CacheIsEmpty();
		}

		return new static(
			new Struct_Db_PivotMail_MailChangeStory(...array_values($cache))
		);
	}

	/**
	 * Сохранить в кэше сессии
	 */
	public function storeInSessionCache(string $session_uniq):void {

		$key = self::_getMemcacheStoryKey($session_uniq);
		Type_Session_Main::setCache($key, (array) $this->change_mail_story, self::EXPIRE_AFTER);
	}

	/**
	 * Удалить запись по сессии
	 */
	public function deleteSessionCache(string $session_uniq):void {

		// получаем значение из кеша, если есть, иначе дальше начнем процесс
		$key = self::_getMemcacheStoryKey($session_uniq);
		Type_Session_Main::clearCache($key);
	}

	/**
	 * Получить запись
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryNotFound
	 * @throws ParseFatalException
	 */
	public static function getByMap(string $change_mail_story_map):self {

		try {
			$story = Gateway_Db_PivotMail_MailChangeStory::getOne($change_mail_story_map);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Security_Mail_Change_StoryNotFound("story not found");
		}

		return new static($story);
	}

	/**
	 * Получить объект кода
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
	 * @throws ParseFatalException
	 */
	public function getActiveCodeStoryEntity(string $mail):Domain_User_Entity_ChangeMail_CodeStory {

		return Domain_User_Entity_ChangeMail_CodeStory::getActive(
			$this->getStoryMap(), $mail
		);
	}

	/**
	 * Получить map записи
	 *
	 * @throws ParseFatalException
	 */
	public function getStoryMap():string {

		try {

			return Type_Pack_ChangeMailStory::doPack(
				$this->change_mail_story->change_mail_story_id,
				$this->change_mail_story->created_at,
			);
		} catch (cs_IncorrectSaltVersion|\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}
	}

	/**
	 * Получить данные записи
	 */
	public function getStoryData():Struct_Db_PivotMail_MailChangeStory {

		return $this->change_mail_story;
	}

	/**
	 * Получаем временную метку истекания попытки
	 */
	public function getExpiresAt():int {

		return $this->change_mail_story->expires_at;
	}

	/**
	 * Получить этап
	 */
	public function getStage():int {

		return $this->change_mail_story->stage;
	}

	/**
	 * Проверяем, что начатая история принадлежит авторизованному пользователю
	 *
	 * @throws Domain_User_Exception_UserNotAuthorized
	 */
	public function assertUserAuthorized(int $user_id):self {

		if ($this->change_mail_story->user_id !== $user_id) {
			throw new Domain_User_Exception_UserNotAuthorized();
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс не истек
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsExpired
	 */
	public function assertNotExpired():self {

		if ($this->change_mail_story->expires_at < time()) {
			throw new Domain_User_Exception_Security_Mail_Change_StoryIsExpired("story is expired");
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс активен
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsNotActive
	 */
	public function assertActive():self {

		if ($this->change_mail_story->status !== self::STATUS_ACTIVE) {
			throw new Domain_User_Exception_Security_Mail_Change_StoryIsNotActive("story is not active");
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс не завершен успешно
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsSuccess
	 */
	public function assertNotSuccess():self {

		if ($this->change_mail_story->status === self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Security_Mail_Change_StoryIsSuccess("story is success");
		}

		return $this;
	}

	/**
	 * Проверяем что находимся на верном шаге
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_WrongStage
	 */
	public function assertFirstStage():self {

		if ($this->change_mail_story->stage !== self::STAGE_FIRST) {
			throw new Domain_User_Exception_Security_Mail_Change_WrongStage("wrong stage");
		}

		return $this;
	}

	/**
	 * Проверяем что находимся на втором шаге
	 *
	 * @throws Domain_User_Exception_Security_Mail_Change_WrongStage
	 */
	public function assertSecondStage():self {

		if ($this->change_mail_story->stage !== self::STAGE_SECOND) {
			throw new Domain_User_Exception_Security_Mail_Change_WrongStage("wrong stage");
		}

		return $this;
	}

	/**
	 * Обрабатываем неверный ввод кода
	 *
	 * @throws ParseFatalException
	 */
	public function handleWrongCode(Struct_Db_PivotMail_MailChangeStory $story):static {

		self::updateEntity($story, [
			"error_count" => $this->change_mail_story->error_count + 1,
			"updated_at"  => time(),
		]);

		return $this;
	}

	/**
	 * обрабатываем успех
	 *
	 * @throws ParseFatalException
	 */
	public function handleSuccess(Struct_Db_PivotMail_MailChangeStory $story):static {

		self::updateEntity($story, [
			"status"     => self::STATUS_SUCCESS,
			"updated_at" => time(),
		]);

		return $this;
	}

	/**
	 * обрабатываем успех на первом шаге
	 *
	 * @throws ParseFatalException
	 */
	public function handleFirstStage(Struct_Db_PivotMail_MailChangeStory $story):static {

		self::updateEntity($story, [
			"stage"      => self::STAGE_SECOND,
			"updated_at" => time(),
		]);

		return $this;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Ключ memcache для story
	 */
	protected static function _getMemcacheStoryKey(string $session_uniq):string {

		return $session_uniq . "_" . self::STORY_NAME;
	}
}