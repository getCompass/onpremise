<?php

namespace Compass\Pivot;

/**
 * Класс для работы с смс при добавлении номера
 */
class Domain_User_Entity_Security_AddPhone_SmsStory {

	protected Struct_Db_PivotPhone_PhoneAddViaSmsStory $phone_add_via_sms_story;

	public const STATUS_ACTIVE     = 0; // не завершенное подтверждение
	public const STATUS_SUCCESS    = 1; // успешное подтверждение
	public const STATUS_ERROR      = 2; // ошибка при добавлении номера
	public const NEXT_RESEND_AFTER = 60 * 2; // время, через которое доступна переотправка
	public const MAX_ERROR_COUNT   = 3; // максимальное кол-во ошибок
	public const MAX_RESEND_COUNT  = 3; // максимальное кол-во ошибок

	/**
	 * Domain_User_Entity_Security_AddPhone_SmsStory constructor.
	 */
	public function __construct(Struct_Db_PivotPhone_PhoneAddViaSmsStory $phone_add_via_sms_story) {

		$this->phone_add_via_sms_story = $phone_add_via_sms_story;
	}

	/**
	 * Создать данные для новой смс при добавлении номера
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws \cs_UnpackHasFailed
	 */
	public static function createNewSmsStory(
		string $add_phone_story_map,
		string $phone_number,
		int    $stage,
		string $sms_id,
		string $sms_code
	):self {

		$add_phone_story_id = Type_Pack_AddPhoneStory::getId($add_phone_story_map);

		return new static(
			new Struct_Db_PivotPhone_PhoneAddViaSmsStory(
				$add_phone_story_id,
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
	 */
	public static function createFromAnotherSmsStoryData(Struct_Db_PivotPhone_PhoneAddViaSmsStory $another_sms_story, array $set):self {

		return new static(
			new Struct_Db_PivotPhone_PhoneAddViaSmsStory(
				$set["add_phone_story_id"] ?? $another_sms_story->add_phone_story_id,
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
	 * Получить активную запись об смс
	 *
	 * @throws Domain_User_Exception_Security_Phone_SmsNotFound
	 */
	public static function getActive(string $add_phone_story_map):self {

		try {

			$story_sms = Gateway_Db_PivotPhone_PhoneAddViaSmsStory::getOneWithStatus(
				$add_phone_story_map,
				self::STATUS_ACTIVE
			);
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Security_Phone_SmsNotFound("sms not found");
		}

		return new static($story_sms);
	}

	/**
	 * Получить данные записи о добавлении номера телефона
	 */
	public function getSmsStoryData():Struct_Db_PivotPhone_PhoneAddViaSmsStory {

		return $this->phone_add_via_sms_story;
	}

	/**
	 * Проверяем, превышено ли кол-во ошибок
	 *
	 * @throws Domain_User_Exception_Security_Phone_SmsErrorCountExceeded
	 */
	public function assertErrorCountNotExceeded():self {

		if ($this->phone_add_via_sms_story->error_count >= self::MAX_ERROR_COUNT) {
			throw new Domain_User_Exception_Security_Phone_SmsErrorCountExceeded();
		}

		return $this;
	}

	/**
	 * Проверяем, не превышено ли число переотправок
	 *
	 * @throws Domain_User_Exception_Security_Phone_SmsResendCountExceeded
	 */
	public function assertResendCountNotExceeded():self {

		if ($this->phone_add_via_sms_story->resend_count >= self::MAX_RESEND_COUNT) {
			throw new Domain_User_Exception_Security_Phone_SmsResendCountExceeded("resend count not exceeded");
		}

		return $this;
	}

	/**
	 * Проверяем, доступна ли переотправка
	 *
	 * @throws Domain_User_Exception_Security_Phone_SmsResendNotAvailable
	 */
	public function assertResendIsAvailable():self {

		if ($this->phone_add_via_sms_story->next_resend_at > time()) {
			throw new Domain_User_Exception_Security_Phone_SmsResendNotAvailable($this->phone_add_via_sms_story->next_resend_at);
		}

		return $this;
	}

	/**
	 * Получить время следующей переотправки
	 */
	public function getNextResend():int {

		return $this->phone_add_via_sms_story->next_resend_at;
	}

	/**
	 * Получить доступное кол-во попыток
	 */
	public function getAvailableAttempts():int {

		return self::MAX_ERROR_COUNT - $this->phone_add_via_sms_story->error_count;
	}

	/**
	 * Получить sms_id
	 */
	public function getSmsId():string {

		return $this->phone_add_via_sms_story->sms_id;
	}

	/**
	 * Получить номер телефона
	 */
	public function getPhoneNumber():string {

		return $this->phone_add_via_sms_story->phone_number;
	}

	/**
	 * Проверяем смс код
	 *
	 * @throws cs_InvalidHashStruct
	 * @throws cs_WrongCode
	 * @throws cs_IncorrectSaltVersion
	 */
	public function assertEqualSmsCode(string $sms_code):self {

		if (!Type_Hash_Code::compareHash($this->phone_add_via_sms_story->sms_code_hash, $sms_code)) {
			throw new cs_WrongCode();
		}

		return $this;
	}
}