<?php

namespace Compass\Company;

/**
 * Класс для работы с сущностью Exactingness (Требовательность)
 */
class Type_User_Card_Exactingness {

	public const EXACTINGNESS_TYPE_DEFAULT = 1; // тип требовательности по умолчанию

	/**
	 * Добавляем новую требовательность в базу
	 *
	 * @throws \parseException
	 */
	public static function add(int $user_id, int $creator_user_id, int $type, int $created_at):Struct_Domain_Usercard_Exactingness {

		return Gateway_Db_CompanyMember_UsercardExactingnessList::add($user_id, $creator_user_id, $type, $created_at);
	}

	/**
	 * Получаем список требовательностей по их id
	 *
	 * @return Struct_Domain_Usercard_Exactingness[]
	 *
	 * @throws \parseException
	 */
	public static function getListByIdList(array $exactingness_id_list):array {

		return Gateway_Db_CompanyMember_UsercardExactingnessList::getListByIdList($exactingness_id_list);
	}

	/**
	 * обновляем требовательность по id требовательности
	 */
	public static function set(int $user_id, int $exactingness_id, array $set):void {

		Gateway_Db_CompanyMember_UsercardExactingnessList::set($user_id, $exactingness_id, $set);
	}

	/**
	 * обновляем список требовательностей по их id
	 */
	public static function setByIdList(int $user_id, array $exactingness_id_list, array $set):void {

		Gateway_Db_CompanyMember_UsercardExactingnessList::setByIdList($user_id, $exactingness_id_list, $set);
	}

	/**
	 * получаем записи по месяцу
	 *
	 * @return Struct_Domain_Usercard_Exactingness[]
	 *
	 * @throws \parseException
	 */
	public static function getListByMonthAt(int $creator_user_id, int $month_start_at, int $limit, int $offset):array {

		// определяем период, за который нужно достать список требовательностей
		[$from_date_at, $to_date_at] = getPeriodByMonthStartAt($month_start_at);

		// получаем требовательности за этот период
		return Gateway_Db_CompanyMember_UsercardExactingnessList::getAllByPeriod($creator_user_id, $from_date_at, $to_date_at, $limit, $offset);
	}

	##########################################################
	# region DATA VARIABLES
	##########################################################

	protected const _DATA_VERSION = 1; // версия json структуры требовательности
	protected const _DATA_SCHEMA  = [

		1 => [
			"message_map" => "", // ключ сообщения-требовательности в группе Требовательность
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
	 * Достаем ключ сообщения-требовательности, которая закреплена за сущностью требовательности
	 */
	public static function getMessageMap(array $data):string {

		$data = self::actualData($data);

		return $data["message_map"];
	}

	/**
	 * Закрепляем ключ сообщения-требовательности за сущностью требовательности
	 */
	public static function setMessageMap(array $data, string $message_map):array {

		$data = self::actualData($data);

		$data["message_map"] = $message_map;

		return $data;
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

	# endregion
	##########################################################
}