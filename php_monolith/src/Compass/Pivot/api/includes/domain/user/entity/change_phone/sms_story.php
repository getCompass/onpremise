<?php

namespace Compass\Pivot;

/**
 * Класс для работы с смс при смене номера
 */
class Domain_User_Entity_ChangePhone_SmsStory {

	protected Struct_Db_PivotPhone_PhoneChangeViaSmsStory $phone_change_via_sms_story;

	public const STATUS_ACTIVE   = 0; // не завершенное подтверждение
	public const STATUS_SUCCESS  = 1; // успешное подтверждение
	public const STATUS_DECLINED = 2; // смена на этот номер отменена

	public const NEXT_RESEND_AFTER = 60 * 2; // время, через которое доступна переотправка

	public const MAX_ERROR_COUNT  = 3; // максимальное кол-во ошибок
	public const MAX_RESEND_COUNT = 3; // максимальное кол-во ошибок

	/**
	 * Domain_User_Entity_ChangePhone_SmsStory constructor.
	 *
	 */
	public function __construct(Struct_Db_PivotPhone_PhoneChangeViaSmsStory $phone_change_via_sms_story) {

		$this->phone_change_via_sms_story = $phone_change_via_sms_story;
	}

	/**
	 * Создать данные для новой смс при смене номера
	 *
	 * @param int $sms_id
	 *
	 * @return Domain_User_Entity_ChangePhone_SmsStory
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws \cs_UnpackHasFailed
	 */
	public static function createNewSmsStory(
		string $change_phone_story_map,
		string $phone_number,
		int    $stage,
		string $sms_id,
		string $sms_code
	):self {

		$change_phone_story_id = Type_Pack_ChangePhoneStory::getId($change_phone_story_map);

		return new static(
			new Struct_Db_PivotPhone_PhoneChangeViaSmsStory(
				$change_phone_story_id,
				$phone_number,
				self::STATUS_ACTIVE,
				$stage,
				0,
				0,
				time(),
				0,
				time() + self::NEXT_RESEND_AFTER,
				$sms_id,
				Type_Hash_Code::makeHash($sms_code),
			)
		);
	}

	/**
	 * Создать новый объект из существующего с обновлением
	 *
	 * @return static
	 */
	public static function createFromAnotherSmsStoryData(Struct_Db_PivotPhone_PhoneChangeViaSmsStory $another_sms_story, array $set):self {

		return new static(
			new Struct_Db_PivotPhone_PhoneChangeViaSmsStory(
				$set["change_phone_story_id"] ?? $another_sms_story->change_phone_story_id,
				$set["phone_number"] ?? $another_sms_story->phone_number,
				$set["status"] ?? $another_sms_story->status,
				$set["stage"] ?? $another_sms_story->stage,
				$set["resend_count"] ?? $another_sms_story->resend_count,
				$set["error_count"] ?? $another_sms_story->error_count,
				$set["created_at"] ?? $another_sms_story->created_at,
				$set["updated_at"] ?? $another_sms_story->updated_at,
				$set["next_resend_at"] ?? $another_sms_story->next_resend_at,
				$set["sms_id"] ?? $another_sms_story->sms_id,
				$set["sms_code_hash"] ?? $another_sms_story->sms_code_hash,
			)
		);
	}

	/**
	 * Получить по параметрам
	 *
	 * @return static
	 *
	 * @throws cs_PhoneChangeSmsNotFound
	 * @throws \cs_UnpackHasFailed
	 */
	public static function get(string $change_phone_story_map, string $phone_number, int $stage):self {

		try {
			$story_sms = Gateway_Db_PivotPhone_PhoneChangeViaSmsStory::getOne($change_phone_story_map, $phone_number, $stage);
		} catch (\cs_RowIsEmpty) {
			throw new cs_PhoneChangeSmsNotFound();
		}

		return new static($story_sms);
	}

	/**
	 * Получить активную запись об смс для конкретного этапа смены номера
	 *
	 * @return static
	 *
	 * @throws cs_PhoneChangeSmsNotFound
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getActiveForStage(string $change_phone_story_map, int $stage):self {

		try {

			$story_sms = Gateway_Db_PivotPhone_PhoneChangeViaSmsStory::getOneWithStatus(
				$change_phone_story_map,
				$stage,
				self::STATUS_ACTIVE
			);
		} catch (\cs_RowIsEmpty) {
			throw new cs_PhoneChangeSmsNotFound();
		}

		return new static($story_sms);
	}

	/**
	 * Получить данные записи об смс для смены номера
	 *
	 */
	public function getSmsStoryData():Struct_Db_PivotPhone_PhoneChangeViaSmsStory {

		return $this->phone_change_via_sms_story;
	}

	/**
	 * Получить время следующей переотправки
	 *
	 */
	public function getNextResend():int {

		return $this->phone_change_via_sms_story->next_resend_at;
	}

	/**
	 * Поулчить доступное кол-во попыток
	 *
	 */
	public function getAvailableAttempts():int {

		return self::MAX_ERROR_COUNT - $this->phone_change_via_sms_story->error_count;
	}

	/**
	 * Убеждаемся, что смс еще не подтверждено
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeSmsAlreadyConfirmed
	 */
	public function assertNotSuccess():self {

		if ($this->phone_change_via_sms_story->status === self::STATUS_SUCCESS) {
			throw new cs_PhoneChangeSmsAlreadyConfirmed();
		}

		return $this;
	}

	/**
	 * Убеждаемся, что смс не отклонено
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeSmsDeclined
	 */
	public function assertNotDeclined():self {

		if ($this->phone_change_via_sms_story->status === self::STATUS_DECLINED) {
			throw new cs_PhoneChangeSmsDeclined();
		}

		return $this;
	}

	/**
	 * Проверяем, превышено ли кол-во ошибок
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeSmsErrorCountExceeded
	 */
	public function assertErrorCountNotExceeded():self {

		if ($this->phone_change_via_sms_story->error_count >= self::MAX_ERROR_COUNT) {
			throw new cs_PhoneChangeSmsErrorCountExceeded();
		}

		return $this;
	}

	/**
	 * Проверяем, доступна ли переотправка
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeSmsResendNotAvailable
	 */
	public function assertResendIsAvailable():self {

		if ($this->phone_change_via_sms_story->next_resend_at > time()) {
			throw new cs_PhoneChangeSmsResendNotAvailable($this->phone_change_via_sms_story->next_resend_at);
		}

		return $this;
	}

	/**
	 * Проверяем, не превышено ли число переотправок
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeSmsResendCountExceeded
	 */
	public function assertResendCountNotExceeded():self {

		if ($this->phone_change_via_sms_story->resend_count >= self::MAX_RESEND_COUNT) {
			throw new cs_PhoneChangeSmsResendCountExceeded();
		}

		return $this;
	}

	/**
	 * Проверяем смс код
	 *
	 * @return $this
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 * @throws cs_WrongCode
	 */
	public function assertEqualSmsCode(string $sms_code):self {

		if (!Type_Hash_Code::compareHash($this->phone_change_via_sms_story->sms_code_hash, $sms_code)) {
			throw new cs_WrongCode();
		}

		return $this;
	}

	/**
	 * Ожидаем, что номер будет отличаться
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneChangeSmsStoryAlreadyExist
	 */
	public function assertNotEqualPhoneNumber(string $phone_number):self {

		if ($this->phone_change_via_sms_story->phone_number === $phone_number) {
			throw new cs_PhoneChangeSmsStoryAlreadyExist();
		}

		return $this;
	}

	/**
	 * Получить sms_id
	 */
	public function getSmsId():string {

		return $this->phone_change_via_sms_story->sms_id;
	}

	/**
	 * Получить номер телефона
	 */
	public function getPhoneNumber():string {

		return $this->phone_change_via_sms_story->phone_number;
	}
}