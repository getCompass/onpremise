<?php

namespace Compass\Pivot;

/**
 * Класс для работы с добавлением номера телефона
 */
class Domain_User_Entity_Security_AddPhone_Story {

	protected Struct_Db_PivotPhone_PhoneAddStory $add_phone_story;

	public const STATUS_ACTIVE  = 1; // активно
	public const STATUS_SUCCESS = 2; // успешно завершено
	public const STATUS_FAIL    = 3; // завершено с ошибкой

	public const STAGE_FIRST  = 1; // первый этап
	public const STAGE_SECOND = 2; // второй этап

	public const EXPIRE_AFTER = 60 * 20; // через сколько истекает
	public const ACTION_TYPE  = "add"; // тип действия

	/**
	 * Domain_User_Entity_Security_AddPhone_Story constructor.
	 */
	public function __construct(Struct_Db_PivotPhone_PhoneAddStory $add_phone_story) {

		$this->add_phone_story = $add_phone_story;
	}

	/**
	 * Создать данные для добавления номера телефона
	 */
	public static function createNewStory(int $user_id, string $session_uniq):self {

		return new static(
			new Struct_Db_PivotPhone_PhoneAddStory(
				null,
				$user_id,
				self::STATUS_ACTIVE,
				self::STAGE_FIRST,
				time(),
				0,
				time() + self::EXPIRE_AFTER,
				$session_uniq,
			)
		);
	}

	/**
	 * Создать новый объект из существующего с обновлением
	 */
	public static function createFromAnotherStoryData(Struct_Db_PivotPhone_PhoneAddStory $another_story, array $set):self {

		return new static(
			new Struct_Db_PivotPhone_PhoneAddStory(
				$set["add_phone_story_id"] ?? $another_story->add_phone_story_id,
				$set["user_id"] ?? $another_story->user_id,
				$set["status"] ?? $another_story->status,
				$set["stage"] ?? $another_story->stage,
				$set["created_at"] ?? $another_story->created_at,
				$set["updated_at"] ?? $another_story->updated_at,
				$set["expires_at"] ?? $another_story->expires_at,
				$set["session_uniq"] ?? $another_story->session_uniq,
			),
		);
	}

	/**
	 * Сохранить в кэше сессии
	 */
	public function storeInSessionCache($phone_number):void {

		Type_Session_Main::setCache($phone_number . self::class, (array) $this->add_phone_story, self::EXPIRE_AFTER + 3 * 60);
	}

	/**
	 * Получить запись по сессии
	 * @throws cs_CacheIsEmpty
	 */
	public static function getFromSessionCache(string $phone_number):self {

		$cached_story = Type_Session_Main::getCache($phone_number . self::class);

		if ($cached_story === []) {
			throw new cs_CacheIsEmpty();
		}

		return new static(
			new Struct_Db_PivotPhone_PhoneAddStory(...array_values($cached_story))
		);
	}

	/**
	 * Получить запись по map
	 *
	 * @throws Domain_User_Exception_Security_Phone_StoryWrongMap
	 */
	public static function getByMap(int $user_id, string $add_phone_story_map):self {

		try {
			$story_data = Gateway_Db_PivotPhone_PhoneAddStory::getOneForUser($user_id, $add_phone_story_map);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Security_Phone_StoryWrongMap("wrong map");
		}

		return new static($story_data);
	}

	/**
	 * Получить данные записи о добавлении номера телефона
	 */
	public function getStoryData():Struct_Db_PivotPhone_PhoneAddStory {

		return $this->add_phone_story;
	}

	/**
	 * Ожидаем, что процесс не истек
	 *
	 * @throws Domain_User_Exception_Security_Phone_IsExpired
	 */
	public function assertNotExpire():self {

		if ($this->add_phone_story->expires_at < time()) {
			throw new Domain_User_Exception_Security_Phone_IsExpired("is expired");
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс не завершен успешно
	 *
	 * @throws Domain_User_Exception_Security_Phone_IsSuccess
	 */
	public function assertNotSuccess():self {

		if ($this->add_phone_story->status === self::STATUS_SUCCESS) {
			throw new Domain_User_Exception_Security_Phone_IsSuccess("already success");
		}

		return $this;
	}

	/**
	 * Получить объект смс
	 *
	 * @throws \parseException
	 * @throws Domain_User_Exception_Security_Phone_SmsNotFound
	 */
	public function getActiveSmsStoryEntity():Domain_User_Entity_Security_AddPhone_SmsStory {

		return Domain_User_Entity_Security_AddPhone_SmsStory::getActive(
			$this->getStoryMap(),
		);
	}

	/**
	 * Получить map записи
	 *
	 * @throws \parseException
	 */
	public function getStoryMap():string {

		return Type_Pack_AddPhoneStory::doPack(
			$this->add_phone_story->add_phone_story_id,
			Type_Pack_AddPhoneStory::getShardIdByTime($this->add_phone_story->created_at),
			$this->add_phone_story->created_at,
		);
	}

	/**
	 * Получаем временную метку истекания попытки
	 */
	public function getExpiresAt():int {

		return $this->add_phone_story->expires_at;
	}

	/**
	 * Проверяем, что начатое добавление номера принадлежит пользователю
	 *
	 * @throws Domain_User_Exception_UserNotAuthorized
	 */
	public function assertUserAuthorized(int $user_id):self {

		if ($this->add_phone_story->user_id !== $user_id) {
			throw new Domain_User_Exception_UserNotAuthorized();
		}

		return $this;
	}
}