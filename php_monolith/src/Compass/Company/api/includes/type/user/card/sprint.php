<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс для работы с сущностью спринта карточки
 */
class Type_User_Card_Sprint {

	public const MAX_SPRINT_HEADER_LENGTH = 80;     // максимальная длина заголовка пользователя
	public const MAX_SPRINT_TEXT_LENGTH   = 2000;   // максимальная длина текста спринта пользователя

	protected const _STARTED_AT_PERIOD_DEFAULT = "-2 week"; // значение для начала спринта по умолчанию

	/**
	 * добавляем новый спринт пользователя в базу
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $creator_user_id, int $is_success, int $end_at, string $header, string $description):Struct_Domain_Usercard_Sprint {

		$started_at = strtotime(self::_STARTED_AT_PERIOD_DEFAULT, $end_at);
		$data       = self::initData();

		return Gateway_Db_CompanyMember_UsercardSprintList::add($user_id, $creator_user_id, $is_success, $started_at, $end_at, $header, $description, $data);
	}

	/**
	 * получаем спринт пользователя
	 *
	 * @throws paramException
	 */
	public static function get(int $user_id, int $sprint_id):Struct_Domain_Usercard_Sprint {

		try {
			return Gateway_Db_CompanyMember_UsercardSprintList::get($user_id, $sprint_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("not found sprint");
		}
	}

	/**
	 * получаем список записей
	 *
	 * @return Struct_Domain_Usercard_Sprint[]
	 */
	public static function getList(int $user_id, int $offset, int $limit):array {

		return Gateway_Db_CompanyMember_UsercardSprintList::getList($user_id, $offset, $limit);
	}

	/**
	 * обновляем запись спринтов в базе
	 */
	public static function set(int $user_id, int $sprint_id, array $set):void {

		Gateway_Db_CompanyMember_UsercardSprintList::set($user_id, $sprint_id, $set);
	}

	/**
	 * редактируем запись
	 */
	public static function edit(int $user_id, int $sprint_id, int $end_at, string $header, string $description, int $is_success, array $data):void {

		$set = [
			"is_success"       => $is_success,
			"end_at"           => $end_at,
			"updated_at"       => time(),
			"header_text"      => $header,
			"description_text" => $description,
			"data"             => $data,
		];

		Gateway_Db_CompanyMember_UsercardSprintList::set($user_id, $sprint_id, $set);
	}

	/**
	 * помечаем спринт удаленным
	 */
	public static function delete(int $user_id, int $sprint_id):void {

		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];

		Gateway_Db_CompanyMember_UsercardSprintList::set($user_id, $sprint_id, $set);
	}

	/**
	 * Получаем количество всех и успешных спринтов пользователя
	 */
	public static function getCountList(int $user_id):array {

		$count_sprint_list = [];
		$success_count     = 0;

		// получаем все записи частями
		$limit  = 1000;
		$offset = 0;
		do {

			$sprint_object_list  = Gateway_Db_CompanyMember_UsercardSprintList::getList($user_id, $offset, $limit);
			$count_sprint_list[] = count($sprint_object_list);

			// проходимся по всем записям и собираем значения успешных спринтов
			foreach ($sprint_object_list as $sprint_object) {

				if ($sprint_object->is_success == 1) {
					$success_count += 1;
				}
			}

			$offset += $limit;
		} while (count($sprint_object_list) == $limit);

		return [
			"all_count"     => array_sum($count_sprint_list),
			"success_count" => $success_count,
		];
	}

	/**
	 * Инкремент значений спринта в card_dynamic_data
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	public static function incInDynamicData(int $user_id, bool $is_total_inc, bool $is_success_inc):Struct_Domain_Usercard_Dynamic {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();

			// создаем запись и сразу инкрементим значение
			$data          = Type_User_Card_DynamicData::initData();
			$success_count = $is_success_inc ? 1 : 0;
			$data          = Type_User_Card_DynamicData::setSprintCountData($data, 1, $success_count);
			return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
		}

		// достаем из data total & success sprint_count
		$total_count   = Type_User_Card_DynamicData::getSprintTotalCount($dynamic_obj->data);
		$success_count = Type_User_Card_DynamicData::getSprintSuccessCount($dynamic_obj->data);

		// инкрементим
		$total_count       = $is_total_inc ? $total_count + 1 : $total_count;
		$success_count     = $is_success_inc ? $success_count + 1 : $success_count;
		$dynamic_obj->data = Type_User_Card_DynamicData::setSprintCountData($dynamic_obj->data, $total_count, $success_count);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();

		// возвращаем обновленный dynamic-data
		return $dynamic_obj;
	}

	/**
	 * Декремент значения спринта в card_dynamic_data
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	public static function decInDynamicData(int $user_id, bool $is_total_dec, bool $is_success_dec):Struct_Domain_Usercard_Dynamic {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();

			// создаем запись и сразу устанавливаем значения по нулям
			$data = Type_User_Card_DynamicData::initData();
			$data = Type_User_Card_DynamicData::setSprintCountData($data, 0, 0);
			return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
		}

		// достаем из data total & success sprint_count
		$total_count   = Type_User_Card_DynamicData::getSprintTotalCount($dynamic_obj->data);
		$success_count = Type_User_Card_DynamicData::getSprintSuccessCount($dynamic_obj->data);

		// декрементим
		$total_count   = $is_total_dec ? $total_count - 1 : $total_count;
		$success_count = $is_success_dec ? $success_count - 1 : $success_count;

		// если значение получилось ниже нуля
		$total_count   = $total_count < 0 ? 0 : $total_count;
		$success_count = $success_count < 0 ? 0 : $success_count;

		$dynamic_obj->data = Type_User_Card_DynamicData::setSprintCountData($dynamic_obj->data, $total_count, $success_count);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();

		// возвращаем обновленный dynamic-data
		return $dynamic_obj;
	}

	##########################################################
	# region DATA VARIABLES
	##########################################################

	protected const _DATA_VERSION = 1; // версия json структуры спринта
	protected const _DATA_SCHEMA  = [

		1 => [
			"link_list" => [], // список ссылок, прикрепленных к тексту спринта
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
	 * Достаем список закрепленных за сущностью ссылок
	 */
	public static function getLinkList(array $data):array {

		$data = self::actualData($data);

		return $data["link_list"];
	}

	/**
	 * Закрепляем список ссылок
	 */
	public static function setLinkList(array $data, array $link_list):array {

		$data = self::actualData($data);

		$data["link_list"] = $link_list;

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