<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы с паролям связанной с почтой
 */
class Domain_User_Entity_PasswordMail_Story {

	/** @var Struct_Db_PivotMail_MailPasswordStory история о изменении пароля */
	protected Struct_Db_PivotMail_MailPasswordStory $password_mail_story;

	public const STATUS_ACTIVE  = 1; // активный
	public const STATUS_SUCCESS = 2; // успешно завершенный

	public const STAGE_START    = 1; // начало процесса
	public const STAGE_FINISHED = 2; // процесс завершен успешно

	public const TYPE_CHANGE_PASSWORD = 1; // тип смена пароля
	public const TYPE_RESET_PASSWORD  = 2; // тип сброс пароля

	public const EXPIRE_AFTER = 60 * 20; // время жизни истории

	public const NEXT_RESEND_AFTER = 60 * 2; // время, через которое доступна переотправка

	public const STORY_NAME = "password_mail_story"; // ключ истории

	public const ACTION_TYPE_RESET_PASSWORD = "reset_password"; // тип действия

	/**
	 * Struct_Db_PivotMail_MailPasswordStory constructor.
	 *
	 * @param Struct_Db_PivotMail_MailPasswordStory $password_mail_story
	 */
	public function __construct(Struct_Db_PivotMail_MailPasswordStory $password_mail_story) {

		$this->password_mail_story = $password_mail_story;
	}

	/**
	 * Получить запись по сессии
	 * @throws cs_CacheIsEmpty
	 */
	public static function getFromSessionCache(string $session_uniq, int $type):self {

		// получаем значение из кеша, если есть, иначе дальше начнем процесс
		$key   = self::_getMemcacheStoryKey($session_uniq, $type);
		$cache = Type_Session_Main::getCache($key);

		if (is_array($cache) && $cache === [] || is_bool($cache)) {
			throw new cs_CacheIsEmpty();
		}

		return new static(
			new Struct_Db_PivotMail_MailPasswordStory(...array_values($cache))
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
	 *
	 * @param int    $user_id
	 * @param string $session_uniq
	 * @param int    $type
	 *
	 * @return static
	 */
	public static function createNewStory(int $user_id, string $session_uniq, int $type):self {

		return new static(
			new Struct_Db_PivotMail_MailPasswordStory(
				null,
				$user_id,
				self::STATUS_ACTIVE,
				$type,
				self::STAGE_START,
				time(),
				0,
				0,
				time() + self::EXPIRE_AFTER,
				$session_uniq,
			)
		);
	}

	/**
	 * оновить объект из существующего
	 *
	 * @param Struct_Db_PivotMail_MailPasswordStory $story
	 * @param array                                 $set
	 *
	 * @return static
	 */
	public static function updateStory(Struct_Db_PivotMail_MailPasswordStory $story, array $set):self {

		return new static(
			new Struct_Db_PivotMail_MailPasswordStory(
				$set["password_mail_story_id"] ?? $story->password_mail_story_id,
				$set["user_id"] ?? $story->user_id,
				$set["status"] ?? $story->status,
				$set["type"] ?? $story->type,
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
	 *
	 * @throws ParseFatalException
	 */
	public function updateEntity(Struct_Db_PivotMail_MailPasswordStory $story, array $update_field_list):static {

		$set = [];
		foreach ($update_field_list as $field => $value) {

			if (!property_exists(Struct_Db_PivotMail_MailPasswordStory::class, $field)) {
				throw new ParseFatalException("attempt to set unknown field");
			}

			$this->password_mail_story->$field = $value;
			$set[$field]                       = $value;
		}

		Gateway_Db_PivotMail_MailPasswordStory::setById($story->password_mail_story_id, $set);

		return $this;
	}

	/**
	 * Обрабатываем неверный ввод кода
	 *
	 * @throws ParseFatalException
	 */
	public function handleWrongCode(Struct_Db_PivotMail_MailPasswordStory $story):static {

		self::updateEntity($story, [
			"error_count" => $this->password_mail_story->error_count + 1,
			"updated_at"  => time(),
		]);

		return $this;
	}

	/**
	 * обрабатываем успех
	 *
	 * @throws ParseFatalException
	 */
	public function handleSuccess(Struct_Db_PivotMail_MailPasswordStory $story):static {

		self::updateEntity($story, [
			"status"     => self::STATUS_SUCCESS,
			"stage"      => self::STAGE_FINISHED,
			"updated_at" => time(),
		]);

		return $this;
	}

	/**
	 * сохранить в кэше сессии
	 *
	 */
	public function storeInSessionCache(string $session_uniq, int $type):void {

		$key = self::_getMemcacheStoryKey($session_uniq, $type);
		Type_Session_Main::setCache($key, (array) $this->password_mail_story, self::EXPIRE_AFTER);
	}

	/**
	 * Получить запись по map
	 *
	 * @throws Domain_User_Exception_Password_WrongMap
	 */
	public static function getByMap(string $password_mail_story_map):self {

		try {
			$story_data = Gateway_Db_PivotMail_MailPasswordStory::getOne($password_mail_story_map);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Password_WrongMap("wrong map");
		}

		return new static($story_data);
	}

	/**
	 * Получить объект кода
	 *
	 * @throws Domain_User_Exception_Password_NotFound
	 * @throws \parseException
	 */
	public function getActiveCodeStoryEntity():Domain_User_Entity_PasswordMail_CodeStory {

		return Domain_User_Entity_PasswordMail_CodeStory::getActive(
			$this->getStoryMap(),
		);
	}

	/**
	 * Получить мапу записи
	 *
	 * @throws \parseException
	 */
	public function getStoryMap():string {

		try {

			return Type_Pack_PasswordMailStory::doPack(
				$this->password_mail_story->password_mail_story_id,
				Type_Pack_PasswordMailStory::getShardIdByTime($this->password_mail_story->created_at),
				self::TYPE_RESET_PASSWORD,
				$this->password_mail_story->created_at,
			);
		} catch (cs_IncorrectSaltVersion|\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}
	}

	/**
	 * Получить данные записи
	 *
	 */
	public function getStoryData():Struct_Db_PivotMail_MailPasswordStory {

		return $this->password_mail_story;
	}

	/**
	 * Ожидаем, что процесс не истек
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Password_StoryIsExpired
	 */
	public function assertNotExpired():self {

		if ($this->password_mail_story->expires_at < time()) {
			throw new Domain_User_Exception_Password_StoryIsExpired("story is expired");
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс активен
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_Password_StoryIsNotActive
	 */
	public function assertActive():self {

		if ($this->password_mail_story->status !== self::STATUS_ACTIVE) {
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

		if ($this->password_mail_story->status === self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Password_StoryIsSuccess("story is success");
		}

		return $this;
	}

	/**
	 * Получить этап
	 *
	 */
	public function getStage():int {

		return $this->password_mail_story->stage;
	}

	/**
	 * Проверяем, что начатая история принадлежит авторизованному пользователю
	 *
	 * @param int $user_id
	 *
	 * @return $this
	 */
	public function assertUserAuthorized(int $user_id):self {

		if ($this->password_mail_story->user_id !== $user_id) {
			throw new Domain_User_Exception_UserNotAuthorized();
		}

		return $this;
	}

	/**
	 * получаем временную метку протухания попытки
	 *
	 * @return int
	 */
	public function getExpiresAt():int {

		return $this->password_mail_story->expires_at;
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