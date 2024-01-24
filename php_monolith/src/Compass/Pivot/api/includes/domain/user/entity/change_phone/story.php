<?php

namespace Compass\Pivot;

/**
 * Класс для работы со сменой номера телефона
 */
class Domain_User_Entity_ChangePhone_Story {

	/** @var Struct_Db_PivotPhone_PhoneChangeStory Запись о смене номера */
	protected Struct_Db_PivotPhone_PhoneChangeStory $change_phone_story;

	public const STATUS_ACTIVE  = 1; // активно
	public const STATUS_SUCCESS = 2; // успешно завершено
	public const STATUS_FAIL    = 3; // завершено с ошибкой

	public const STAGE_FIRST  = 1; // первый этап - старый номер
	public const STAGE_SECOND = 2; // второй этап - новый номер

	public const EXPIRE_AFTER = 60 * 20; // через сколько истекает

	/**
	 * Domain_User_Entity_ChangePhone_Story constructor.
	 *
	 */
	public function __construct(Struct_Db_PivotPhone_PhoneChangeStory $change_phone_story) {

		$this->change_phone_story = $change_phone_story;
	}

	/**
	 * Получить запись по сессии
	 *
	 * @return static
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getFromSessionCache():self {

		$cached_story = Type_Session_Main::getCache(self::class);

		if ($cached_story === []) {
			throw new cs_CacheIsEmpty();
		}

		return new static(
			new Struct_Db_PivotPhone_PhoneChangeStory(...array_values($cached_story))
		);
	}

	/**
	 * Получить запись по сессии
	 *
	 * @return static
	 *
	 * @throws cs_PhoneChangeStoryWrongMap
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getByMap(int $user_id, string $change_phone_story_map):self {

		try {
			$story_data = Gateway_Db_PivotPhone_PhoneChangeStory::getOneForUser($user_id, $change_phone_story_map);
		} catch (\cs_RowIsEmpty) {
			throw new cs_PhoneChangeStoryWrongMap();
		}

		return new static($story_data);
	}

	/**
	 * Создать данные для новой смены номера
	 *
	 * @return Domain_User_Entity_ChangePhone_Story
	 */
	public static function createNewStory(int $user_id, string $session_uniq):self {

		return new static(
			new Struct_Db_PivotPhone_PhoneChangeStory(
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
	 *
	 * @return static
	 */
	public static function createFromAnotherStoryData(Struct_Db_PivotPhone_PhoneChangeStory $another_story, array $set):self {

		return new static(
			new Struct_Db_PivotPhone_PhoneChangeStory(
				$set["change_phone_story_id"] ?? $another_story->change_phone_story_id,
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
	 * сохранить в кэше сессии
	 *
	 */
	public function storeInSessionCache():void {

		Type_Session_Main::setCache(self::class, (array) $this->change_phone_story, self::EXPIRE_AFTER + 3 * 60);
	}

	/**
	 * Получить данные записи о смене номера
	 *
	 */
	public function getStoryData():Struct_Db_PivotPhone_PhoneChangeStory {

		return $this->change_phone_story;
	}

	/**
	 * Получить мапу записи
	 *
	 * @throws \parseException
	 */
	public function getStoryMap():string {

		return Type_Pack_ChangePhoneStory::doPack(
			$this->change_phone_story->change_phone_story_id,
			Type_Pack_ChangePhoneStory::getShardIdByTime($this->change_phone_story->created_at),
			$this->change_phone_story->created_at,
		);
	}

	/**
	 * Ожидаем, что процесс не истек
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeIsExpired
	 */
	public function assertNotExpire():self {

		if ($this->change_phone_story->expires_at < time()) {
			throw new cs_PhoneChangeIsExpired();
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс активен
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeIsNotActive
	 */
	public function assertActive():self {

		if ($this->change_phone_story->status !== self::STATUS_ACTIVE) {
			throw new cs_PhoneChangeIsNotActive();
		}

		return $this;
	}

	/**
	 * Ожидаем, что процесс не завершен успешно
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeIsSuccess
	 */
	public function assertNotSuccess():self {

		if ($this->change_phone_story->status === self::STATUS_SUCCESS) {
			throw new cs_PhoneChangeIsSuccess();
		}

		return $this;
	}

	/**
	 * Ожидаем именно первый этап смены номера
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeWrongStage
	 */
	public function assertFirstStage():self {

		if ($this->change_phone_story->stage !== self::STAGE_FIRST) {
			throw new cs_PhoneChangeWrongStage();
		}

		return $this;
	}

	/**
	 * Ожидаем именно второй этап смены номера
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeWrongStage
	 */
	public function assertSecondStage():self {

		if ($this->change_phone_story->stage !== self::STAGE_SECOND) {
			throw new cs_PhoneChangeWrongStage();
		}

		return $this;
	}

	/**
	 * Получить этап смены номера
	 *
	 */
	public function getStage():int {

		return $this->change_phone_story->stage;
	}

	/**
	 * Проверяем, что начатая смена номера принадлежит пользователю
	 *
	 * @return $this
	 *
	 * @throws \userAccessException
	 */
	public function assertUserAuthorized(int $user_id):self {

		if ($this->change_phone_story->user_id !== $user_id) {
			throw new Domain_User_Exception_UserNotAuthorized();
		}

		return $this;
	}

	/**
	 * Получить запись об смс для текущего этапа
	 *
	 * @throws cs_PhoneChangeSmsNotFound
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public function getActiveSmsStoryForCurrentStage():Domain_User_Entity_ChangePhone_SmsStory {

		if ($this->getStage() === self::STAGE_FIRST) {
			return $this->getActiveFirstSmsStoryEntity();
		}

		return $this->getActiveSecondSmsStoryEntity();
	}

	/**
	 * Получить объект первой смски для смены номера
	 *
	 * @throws cs_PhoneChangeSmsNotFound
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public function getActiveFirstSmsStoryEntity():Domain_User_Entity_ChangePhone_SmsStory {

		return Domain_User_Entity_ChangePhone_SmsStory::getActiveForStage(
			$this->getStoryMap(),
			self::STAGE_FIRST,
		);
	}

	/**
	 * Получить объект смски для нового номера
	 *
	 * @throws cs_PhoneChangeSmsNotFound
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public function getActiveSecondSmsStoryEntity():Domain_User_Entity_ChangePhone_SmsStory {

		return Domain_User_Entity_ChangePhone_SmsStory::getActiveForStage(
			$this->getStoryMap(),
			self::STAGE_SECOND,
		);
	}

	/**
	 * Выполнить подтверждение для текущего этапа смены номера
	 *
	 * @return array
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public function doConfirmActionForCurrentStage(Domain_User_Entity_ChangePhone_SmsStory $sms_story):array {

		if ($this->getStage() === self::STAGE_FIRST) {
			return Domain_User_Action_ChangePhone_ConfirmFirstStage::do($sms_story, $this);
		}

		return Domain_User_Action_ChangePhone_ConfirmSecondStage::do($sms_story, $this);
	}

	/**
	 * получаем временную метку протухания попытки
	 *
	 * @return int
	 */
	public function getExpiresAt():int {

		return $this->change_phone_story->expires_at;
	}

}