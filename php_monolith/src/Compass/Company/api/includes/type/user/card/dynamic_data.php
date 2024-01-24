<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы с dynamic-данными карточки пользователя
 */
class Type_User_Card_DynamicData {

	// типы сущностей карточки, с которыми работаем в dynamic-данных
	protected const _RESPECT_TYPE      = "respect";
	protected const _ACHIEVEMENT_TYPE  = "achievement";
	protected const _SPRINT_TYPE       = "sprint";
	protected const _LOYALTY_TYPE      = "loyalty";
	protected const _WORKED_HOURS_TYPE = "worked_hours";

	/**
	 * Добавляем новую запись dynamic-данных пользователя в базу
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Domain_Usercard_Dynamic
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function add(int $user_id):Struct_Domain_Usercard_Dynamic {

		$data = self::initData();

		return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
	}

	/**
	 * Обновляем запись с dynamic-данными в базе
	 */
	public static function set(int $user_id, array $set):void {

		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);
	}

	/**
	 * Достаем dynamic-данные карточки определенного пользователя
	 *
	 * @throws \parseException
	 */
	public static function get(int $user_id):Struct_Domain_Usercard_Dynamic {

		try {
			return Gateway_Db_CompanyMember_UsercardDynamic::get($user_id);
		} catch (\cs_RowIsEmpty) {
			return self::create($user_id);
		}
	}

	/**
	 * чистим данные пользователя о отработанных часах
	 */
	public static function clearAllWorkHours(int $user_id):void {

		// пишем в базу
		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();
			return;
		}

		// устанавливаем среднее время рабочих часов на 0 и всех отметок
		$dynamic_obj->data = Type_User_Card_DynamicData::clearAvgWorkedHours($dynamic_obj->data);

		// обновляем dynamic-данные
		$set = [
			"data"       => $dynamic_obj->data,
			"updated_at" => time(),
		];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();
	}

	/**
	 * создаем сущность dynamicData
	 */
	public static function create(int $user_id):Struct_Domain_Usercard_Dynamic {

		return new Struct_Domain_Usercard_Dynamic(
			$user_id,
			time(),
			0,
			self::initData()
		);
	}

	/**
	 * Достаем dynamic-данные карточки определенного пользователя для обновления
	 *
	 * @throws \parseException|\cs_RowIsEmpty
	 */
	public static function getForUpdate(int $user_id):Struct_Domain_Usercard_Dynamic {

		return Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
	}

	##########################################################
	# region DATA VARIABLES
	##########################################################

	protected const _DATA_VERSION = 1; // версия json структуры dynamic-данных
	protected const _DATA_SCHEMA  = [

		1 => [
			self::_RESPECT_TYPE      => [
				"total_count" => 0,
			],
			self::_ACHIEVEMENT_TYPE  => [
				"total_count" => 0,
			],
			self::_SPRINT_TYPE       => [
				"total_count"   => 0,
				"success_count" => 0,
			],
			self::_LOYALTY_TYPE      => [
				"total_count"            => 0,
				"total_sport_value"      => 0,
				"total_reaction_value"   => 0,
				"total_department_value" => 0,
			],
			self::_WORKED_HOURS_TYPE => [
				"total_worked_hours" => 0,
				"total_worked_count" => 0,
				"avg_worked_hours"   => 0,
			],
		],
	];

	/**
	 * инициализируем поле data
	 */
	public static function initData():array {

		$data            = self::_DATA_SCHEMA[self::_DATA_VERSION];
		$data["version"] = self::_DATA_VERSION;

		return $data;
	}

	/**
	 * Получаем общее количество респектов выданных пользователем
	 */
	public static function getRespectCount(array $dynamic_data):int {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности респекта
		$respect_data = $dynamic_data[self::_RESPECT_TYPE];

		// получаем нужное значение
		return $respect_data["total_count"];
	}

	/**
	 * Получаем общее количество достижений пользователя
	 */
	public static function getAchievementCount(array $dynamic_data):int {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности достижений
		$achievement_data = $dynamic_data[self::_ACHIEVEMENT_TYPE];

		// получаем нужное значение
		return $achievement_data["total_count"];
	}

	/**
	 * Получаем общее количество спринтов пользователя
	 */
	public static function getSprintTotalCount(array $dynamic_data):int {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности спринта
		$sprint_data = $dynamic_data[self::_SPRINT_TYPE];

		// получаем нужное значение
		return $sprint_data["total_count"];
	}

	/**
	 * Получаем количество успешных спринтов пользователя
	 */
	public static function getSprintSuccessCount(array $dynamic_data):int {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности спринта
		$sprint_data = $dynamic_data[self::_SPRINT_TYPE];

		// получаем нужное значение
		return $sprint_data["success_count"];
	}

	/**
	 * Получаем процент успешных спринтов пользователя
	 */
	public static function getSprintSuccessPercent(array $dynamic_data):int {

		$dynamic_data = self::actualData($dynamic_data);

		// получаем количество всех спринтов
		$total_count = self::getSprintTotalCount($dynamic_data);

		// получаем количество успешных спринтов
		$success_count = self::getSprintSuccessCount($dynamic_data);

		// если успешных спринтов по нулям, то и процент успешных такой же
		if ($success_count == 0) {
			return 0;
		}

		return floor(100 / $total_count * $success_count);
	}

	/**
	 * Получаем количество оценок вовлеченности пользователя
	 */
	public static function getLoyaltyCount(array $dynamic_data):int {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности оценки лояльности
		$loyalty_data = $dynamic_data[self::_LOYALTY_TYPE];

		// получаем нужное значение
		return $loyalty_data["total_count"];
	}

	/**
	 * Получаем total-значение рабочих часов пользователя
	 */
	public static function getTotalWorkedHours(array $dynamic_data):float {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности рабочих часов
		$worked_hours_data = $dynamic_data[self::_WORKED_HOURS_TYPE];

		// получаем нужное значение
		return intToFloat($worked_hours_data["total_worked_hours"]);
	}

	/**
	 * Получаем total-значение количество сколько рабочих часов пользователя
	 */
	public static function getTotalWorkedCount(array $dynamic_data):int {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности рабочих часов
		$worked_hours_data = $dynamic_data[self::_WORKED_HOURS_TYPE];

		// получаем нужное значение
		return $worked_hours_data["total_worked_count"];
	}

	/**
	 * Получаем среднее значение рабочих часов пользователя
	 */
	public static function getAvgWorkedHours(array $dynamic_data):float {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности рабочих часов
		$worked_hours_data = $dynamic_data[self::_WORKED_HOURS_TYPE];

		// получаем нужное значение
		return intToFloat($worked_hours_data["avg_worked_hours"]);
	}

	/**
	 * Получаем количество оценок вовлеченности пользователя
	 */
	public static function getLoyaltyValueGroupedByCategory(array $dynamic_data):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности оценки лояльности
		$loyalty_data = $dynamic_data[self::_LOYALTY_TYPE];

		// получаем значения
		return [
			Type_User_Card_Loyalty::SPORT_VALUE_TYPE      => $loyalty_data["total_sport_value"],
			Type_User_Card_Loyalty::REACTION_VALUE_TYPE   => $loyalty_data["total_reaction_value"],
			Type_User_Card_Loyalty::DEPARTMENT_VALUE_TYPE => $loyalty_data["total_department_value"],
		];
	}

	/**
	 * получаем total-значение оценки вовлеченности
	 */
	public static function getTotalLoyaltyValue(array $dynamic_data):int {

		$dynamic_data = self::actualData($dynamic_data);

		$value_grouped_by_category = self::getLoyaltyValueGroupedByCategory($dynamic_data);

		return round(array_sum($value_grouped_by_category) / count($value_grouped_by_category));
	}

	/**
	 * Устанавливаем общее количество выданных респектов пользователем
	 */
	public static function setRespectCount(array $dynamic_data, int $respect_count):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности респекта
		$respect_data = $dynamic_data[self::_RESPECT_TYPE];

		// устанавливаем нужное значение
		$respect_data["total_count"] = $respect_count;

		// добавляем данные респекта в dynamic-данные
		$dynamic_data[self::_RESPECT_TYPE] = $respect_data;

		return $dynamic_data;
	}

	/**
	 * Устанавливаем общее количество выданных респектов пользователем
	 */
	public static function setAchievementCount(array $dynamic_data, int $achievement_count):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности достижения
		$achievement_data = $dynamic_data[self::_ACHIEVEMENT_TYPE];

		// устанавливаем нужное значение
		$achievement_data["total_count"] = $achievement_count;

		// добавляем данные достижения в dynamic-данные
		$dynamic_data[self::_ACHIEVEMENT_TYPE] = $achievement_data;

		return $dynamic_data;
	}

	/**
	 * Устанавливаем количество для спринтов пользователя
	 */
	public static function setSprintCountData(array $dynamic_data, int $total_count, int $success_count):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности спринта
		$sprint_data = $dynamic_data[self::_SPRINT_TYPE];

		// устанавливаем нужное значение
		$sprint_data["total_count"]   = $total_count;
		$sprint_data["success_count"] = $success_count;

		// добавляем данные спринта в dynamic-данные
		$dynamic_data[self::_SPRINT_TYPE] = $sprint_data;

		return $dynamic_data;
	}

	/**
	 * Устанавливаем тотал-значение оценок вовлеченности пользователя
	 */
	public static function setLoyaltyTotalValueData(array $dynamic_data, int $loyalty_count):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности оценки лояльности
		$loyalty_data = $dynamic_data[self::_LOYALTY_TYPE];

		// устанавливаем нужное значение
		$loyalty_data["total_value"] = $loyalty_count;

		// добавляем данные оценки в dynamic-данные
		$dynamic_data[self::_LOYALTY_TYPE] = $loyalty_data;

		return $dynamic_data;
	}

	/**
	 * Устанавливаем количество оценок вовлеченности пользователя
	 */
	public static function setLoyaltyCountData(array $dynamic_data, int $loyalty_count):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности оценки лояльности
		$loyalty_data = $dynamic_data[self::_LOYALTY_TYPE];

		// устанавливаем нужное значение
		$loyalty_data["total_count"] = $loyalty_count;

		// добавляем данные оценки в dynamic-данные
		$dynamic_data[self::_LOYALTY_TYPE] = $loyalty_data;

		return $dynamic_data;
	}

	/**
	 * Устанавливаем значения для определенной категории оценки вовлеченности пользователя
	 *
	 * @throws \parseException
	 */
	public static function setLoyaltyValueByCategory(array $dynamic_data, int $loyalty_value, int $loyalty_category):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности оценки лояльности
		$loyalty_data = $dynamic_data[self::_LOYALTY_TYPE];

		// в зависимости от категории оценки вовлеченности
		$category_dynamic_name = match ($loyalty_category) {

			Type_User_Card_Loyalty::SPORT_VALUE_TYPE      => "total_sport_value",

			Type_User_Card_Loyalty::REACTION_VALUE_TYPE   => "total_reaction_value",

			Type_User_Card_Loyalty::DEPARTMENT_VALUE_TYPE => "total_department_value",

			default                                       => throw new ParseFatalException("not exist this category ('{$loyalty_category}') for loyalty"),
		};

		// устанавливаем нужное значение для нужной категории в dynamic-даннх
		$loyalty_data[$category_dynamic_name] = $loyalty_value;

		// добавляем данные оценки в dynamic-данные
		$dynamic_data[self::_LOYALTY_TYPE] = $loyalty_data;

		return $dynamic_data;
	}

	/**
	 * Устанавливаем значения для всех категории оценки вовлеченности пользователя
	 *
	 * @throws \parseException
	 */
	public static function setValueForAllLoyaltyCategory(array $dynamic_data, int $sport_value, int $reaction_value, int $department_value):array {

		$dynamic_data = self::actualData($dynamic_data);

		// устанавливаем нужное значение для нужной категории
		$dynamic_data = self::setLoyaltyValueByCategory($dynamic_data, $sport_value, Type_User_Card_Loyalty::SPORT_VALUE_TYPE);
		$dynamic_data = self::setLoyaltyValueByCategory($dynamic_data, $reaction_value, Type_User_Card_Loyalty::REACTION_VALUE_TYPE);
		$dynamic_data = self::setLoyaltyValueByCategory($dynamic_data, $department_value, Type_User_Card_Loyalty::DEPARTMENT_VALUE_TYPE);

		return $dynamic_data;
	}

	/**
	 * устанавливаем total-значение рабочих часов пользователя
	 */
	public static function setWorkedHoursValues(array $dynamic_data, int $total_worked_hours_values):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности рабочих часов
		$worked_hours_data = $dynamic_data[self::_WORKED_HOURS_TYPE];

		// устанавливаем нужное значение
		$worked_hours_data["total_worked_hours"] = $total_worked_hours_values;

		// добавляем данные в dynamic-данные
		$dynamic_data[self::_WORKED_HOURS_TYPE] = $worked_hours_data;

		return $dynamic_data;
	}

	/**
	 * устанавливаем total-значение количества рабочих часов пользователя
	 */
	public static function setWorkedHoursCount(array $dynamic_data, int $total_worked_hours_count):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности рабочих часов
		$worked_hours_data = $dynamic_data[self::_WORKED_HOURS_TYPE];

		// устанавливаем нужное значение
		$worked_hours_data["total_worked_count"] = $total_worked_hours_count;

		// добавляем данные в dynamic-данные
		$dynamic_data[self::_WORKED_HOURS_TYPE] = $worked_hours_data;

		return $dynamic_data;
	}

	/**
	 * устанавливаем среднее значение рабочих часов
	 */
	public static function setAvgWorkedHours(array $dynamic_data, int $avg_worked_hours):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности рабочих часов
		$worked_hours_data = $dynamic_data[self::_WORKED_HOURS_TYPE];

		// устанавливаем нужное значение
		$worked_hours_data["avg_worked_hours"] = $avg_worked_hours;

		// добавляем данные в dynamic-данные
		$dynamic_data[self::_WORKED_HOURS_TYPE] = $worked_hours_data;

		return $dynamic_data;
	}

	/**
	 * Получить актуальную структуру data
	 */
	public static function actualData(array $data_schema):array {

		// если версия совпадает
		if (isset($data_schema["version"]) && $data_schema["version"] == self::_DATA_VERSION) {
			return $data_schema;
		}

		// иначе - дополняем её до текущей
		$current_data_schema = self::_DATA_SCHEMA[self::_DATA_VERSION];

		$data_schema            = array_merge($current_data_schema, $data_schema);
		$data_schema["version"] = self::_DATA_VERSION;

		return $data_schema;
	}

	/**
	 * Чистим данные о часах
	 */
	public static function clearAvgWorkedHours(array $dynamic_data):array {

		$dynamic_data = self::actualData($dynamic_data);

		// достаем из dynamic-данных данные сущности рабочих часов
		$worked_hours_data = $dynamic_data[self::_WORKED_HOURS_TYPE];

		// устанавливаем нужное значение
		$worked_hours_data["avg_worked_hours"]   = 0;
		$worked_hours_data["total_worked_count"] = 0;
		$worked_hours_data["total_worked_hours"] = 0;

		// добавляем данные в dynamic-данные
		$dynamic_data[self::_WORKED_HOURS_TYPE] = $worked_hours_data;

		return $dynamic_data;
	}

	# endregion
	##########################################################
}