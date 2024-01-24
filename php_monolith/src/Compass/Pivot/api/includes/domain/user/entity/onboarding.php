<?php

namespace Compass\Pivot;

/**
 * Сущность онбординга
 */
class Domain_User_Entity_Onboarding {

	public const STATUS_UNAVAILABLE = 0; // недоступный онбординг
	public const STATUS_ACTIVE      = 1; // активный онбординг
	public const STATUS_FINISHED    = 2; // завершенный онбординг

	public const TYPE_COMPASS_DEFAULT    = 0; // дефолтный онбординг
	public const TYPE_SPACE_CREATOR      = 1; // онбординг владельца пространства
	public const TYPE_SPACE_MEMBER       = 2; // онбординг участника пространства
	public const TYPE_SPACE_GUEST        = 3; // онбординг гостя пространства
	public const TYPE_SPACE_JOIN_REQUEST = 4; // онбординг по принятой заявке в пространство
	public const TYPE_SPACE_CREATOR_LITE = 5; // упрощённый онбординг владельца пространства

	/**
	 * Допустимые статусы онбординга
	 */
	protected const _ALLOWED_STATUS_LIST = [
		self::STATUS_UNAVAILABLE,
		self::STATUS_ACTIVE,
		self::STATUS_FINISHED,
	];

	/**
	 * Допустимые типы онбординга
	 */
	protected const _ALLOWED_TYPE_LIST = [
		self::TYPE_SPACE_CREATOR_LITE,
		self::TYPE_SPACE_CREATOR,
		self::TYPE_SPACE_MEMBER,
		self::TYPE_SPACE_GUEST,
		self::TYPE_SPACE_JOIN_REQUEST,
		self::TYPE_COMPASS_DEFAULT,
	];

	/**
	 * Форматирование типа онбординга
	 */
	protected const _TYPE_OUTPUT_SCHEMA = [
		self::TYPE_SPACE_CREATOR_LITE => "space_creator_lite",
		self::TYPE_SPACE_CREATOR      => "space_creator",
		self::TYPE_SPACE_MEMBER       => "space_member",
		self::TYPE_SPACE_GUEST        => "space_guest",
		self::TYPE_SPACE_JOIN_REQUEST => "space_join_request",
		self::TYPE_COMPASS_DEFAULT    => "compass_default",
	];

	/**
	 * Форматирование статуса онбординга
	 */
	protected const _STATUS_OUTPUT_SCHEMA = [
		self::STATUS_UNAVAILABLE => "unavailable",
		self::STATUS_ACTIVE      => "active",
		self::STATUS_FINISHED    => "finished",
	];

	/**
	 * Получить из списка онбордингов
	 *
	 * @param int                      $type
	 * @param Struct_User_Onboarding[] $onboarding_list
	 *
	 * @return Struct_User_Onboarding|false
	 */
	public static function getFromOnboardingList(int $type, array $onboarding_list):Struct_User_Onboarding|false {

		foreach ($onboarding_list as $onboarding) {

			if ($onboarding->type === $type) {
				return $onboarding;
			}
		}

		return false;
	}

	/**
	 * Проверить наличие типа в списке онбордингов
	 *
	 * @param array                    $type_list
	 * @param Struct_User_Onboarding[] $onboarding_list
	 *
	 * @return bool
	 */
	public static function isExistOnOnboardingList(array $type_list, array $onboarding_list):bool {

		foreach ($onboarding_list as $onboarding) {

			if (in_array($onboarding->type, $type_list)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Получить первый активированный онбординг
	 *
	 * @param Struct_User_Onboarding[] $onboarding_list
	 *
	 * @return Struct_User_Onboarding|false
	 */
	public static function getFirstActivatedOnboarding(array $onboarding_list):Struct_User_Onboarding|false {

		// если есть хотя бы один завершенный онбординг - ничего не отдаем
		foreach ($onboarding_list as $onboarding) {

			if ($onboarding->status === Domain_User_Entity_Onboarding::STATUS_FINISHED) {
				return false;
			}
		}

		// оставляем только активные онбординги
		$onboarding_list = array_filter(
			$onboarding_list,
			static fn(Struct_User_Onboarding $onboarding) => $onboarding->status === Domain_User_Entity_Onboarding::STATUS_ACTIVE
		);

		if ($onboarding_list === []) {
			return false;
		}

		// сортируем, чтобы получить первый активный
		usort(
			$onboarding_list,
			static fn(Struct_User_Onboarding $a, Struct_User_Onboarding $b) => $a->activated_at <=> $b->activated_at);

		return $onboarding_list[0];
	}

	/**
	 * Допустимый ли статус онбординга
	 *
	 * @param int $status
	 *
	 * @return bool
	 */
	public static function isAllowedStatus(int $status):bool {

		return in_array($status, self::_ALLOWED_STATUS_LIST);
	}

	/**
	 * Вернуть исключение, если недопустимый статус онбординга
	 *
	 * @param int $status
	 *
	 * @return void
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 */
	public static function assertAllowedStatus(int $status):void {

		if (!self::isAllowedStatus($status)) {
			throw new Domain_User_Exception_Onboarding_NotAllowedStatus("not allowed status");
		}
	}

	/**
	 * Допустимый ли тип онбординга
	 *
	 * @param int $type
	 *
	 * @return bool
	 */
	public static function isAllowedType(int $type):bool {

		return in_array($type, self::_ALLOWED_TYPE_LIST);
	}

	/**
	 * Вернуть исключение, если недопустимый тип онбординга
	 *
	 * @param int $type
	 *
	 * @return void
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 */
	public static function assertAllowedType(int $type):void {

		if (!self::isAllowedType($type)) {
			throw new Domain_User_Exception_Onboarding_NotAllowedType("not allowed type");
		}
	}

	/**
	 * Приводим тип к числовому значению
	 *
	 * @param string $type
	 *
	 * @return int
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 */
	public static function formatTypeToInt(string $type):int {

		$flipped_type_schema = array_flip(self::_TYPE_OUTPUT_SCHEMA);

		if (!isset($flipped_type_schema[$type])) {
			throw new Domain_User_Exception_Onboarding_NotAllowedType("not allowed type");
		}

		return $flipped_type_schema[$type];
	}

	/**
	 * Форматируем выходные данные по онбордингу
	 *
	 * @param Struct_User_Onboarding $onboarding
	 *
	 * @return array
	 */
	public static function formatOutput(Struct_User_Onboarding $onboarding):array {

		return [
			"type"         => (string) self::_TYPE_OUTPUT_SCHEMA[$onboarding->type],
			"status"       => (string) self::_STATUS_OUTPUT_SCHEMA[$onboarding->status],
			"data"         => (object) $onboarding->data,
			"activated_at" => (int) $onboarding->activated_at,
			"finished_at"  => (int) $onboarding->finished_at,
		];
	}
}