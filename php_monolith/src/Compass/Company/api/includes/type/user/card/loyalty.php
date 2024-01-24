<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы с сущностью loyalty
 */
class Type_User_Card_Loyalty {

	// максимальная длина комментария вовлеченности пользователя
	public const MAX_LOYALTY_COMMENT_LENGTH = 2000;

	public const SPORT_VALUE_TYPE      = 1;
	public const REACTION_VALUE_TYPE   = 2;
	public const DEPARTMENT_VALUE_TYPE = 3;

	// доступные типы категории оценки
	public const ALLOW_LOYALTY_TYPE_LIST = [
		self::SPORT_VALUE_TYPE,
		self::REACTION_VALUE_TYPE,
		self::DEPARTMENT_VALUE_TYPE,
	];

	/**
	 * метод для добавления записи вовлеченности
	 *
	 * @throws \parseException|\queryException
	 */
	public static function add(int $user_id, int $creator_user_id, int $reaction_value, int $sport_value, int $department_life_value, string $comment_text):Struct_Domain_Usercard_Loyalty {

		$time = time();

		$data = self::initData($sport_value, $reaction_value, $department_life_value);

		// добавляем запись в базу
		return Gateway_Db_CompanyMember_UsercardLoyaltyList::insert($user_id, $creator_user_id, 0, $time, 0, $comment_text, $data);
	}

	/**
	 * получаем вовлеченность пользователя
	 *
	 * @return Struct_Domain_Usercard_Loyalty[]
	 */
	public static function getAllLoyalty(int $user_id, int $last_loyalty_id, int $limit):array {

		// формируем и осуществляем запрос
		// формируем запрос в зависимости от значения last_loyalty_id
		if ($last_loyalty_id == 0) {
			return Gateway_Db_CompanyMember_UsercardLoyaltyList::getLastLoyaltyList($user_id, $limit);
		}
		return Gateway_Db_CompanyMember_UsercardLoyaltyList::getLoyaltyListAfterId($user_id, $last_loyalty_id, $limit);
	}

	/**
	 * получаем среднее значение по  параметрам
	 */
	public static function getAvgValue(Struct_Domain_Usercard_Loyalty $user_card_loyalty):int {

		return self::_getAvgFromData($user_card_loyalty->data);
	}

	/**
	 * получаем значение по категориям
	 */
	public static function getValueGroupedByType(Struct_Domain_Usercard_Loyalty $user_card_loyalty):array {

		return self::_getValueGroupedByTypeFromData($user_card_loyalty->data);
	}

	/**
	 * Получаем информацию о всех записях вовлеченности
	 */
	public static function getTotalData(int $user_id):array {

		$reaction_list        = [];
		$sport_list           = [];
		$department_life_list = [];

		// получаем все записи частями
		$limit           = 1000;
		$last_loyalty_id = 0;
		do {

			$loyalty_object_list = self::getAllLoyalty($user_id, $last_loyalty_id, $limit);

			// проходимся по всем записям и собираем значения
			foreach ($loyalty_object_list as $v) {

				$reaction_list[]        = self::_getValueFromData(self::REACTION_VALUE_TYPE, $v->data);
				$sport_list[]           = self::_getValueFromData(self::SPORT_VALUE_TYPE, $v->data);
				$department_life_list[] = self::_getValueFromData(self::DEPARTMENT_VALUE_TYPE, $v->data);
				$last_loyalty_id        = $v->loyalty_id;
			}
		} while (count($loyalty_object_list) == $limit);

		return self::_makeOutputForGetTotalData($reaction_list, $sport_list, $department_life_list);
	}

	/**
	 * собираем ответ для метода getTotalData
	 */
	protected static function _makeOutputForGetTotalData(array $reaction_list, array $sport_list, array $department_life_list):array {

		// считаем среднии значения
		$avg_reaction_value        = self::_getAvgValue($reaction_list);
		$avg_sport_value           = self::_getAvgValue($sport_list);
		$avg_department_life_value = self::_getAvgValue($department_life_list);
		$value                     = round(($avg_reaction_value + $avg_sport_value + $avg_department_life_value) / 3);

		return [
			"value"                 => (int) $value,
			"reaction_value"        => (int) $avg_reaction_value,
			"sport_value"           => (int) $avg_sport_value,
			"department_life_value" => (int) $avg_department_life_value,
		];
	}

	/**
	 * получить среднее значение
	 */
	protected static function _getAvgValue(array $list):int {

		// получаем количество значений
		$count_value_list = count($list);

		// если все по нулям, возвращаем нуль
		if ($count_value_list == 0) {
			return 0;
		}

		// складываем все значения
		$total_value = array_sum($list);

		// возвращаем среднее значение
		return round($total_value / $count_value_list);
	}

	/**
	 * получить информацию об одной записи лояльности
	 */
	public static function get(int $user_id, int $loyalty_id):?Struct_Domain_Usercard_Loyalty {

		try {
			$user_card_loyalty = Gateway_Db_CompanyMember_UsercardLoyaltyList::getOne($user_id, $loyalty_id);
		} catch (\cs_RowIsEmpty) {
			return null;
		}

		return $user_card_loyalty;
	}

	/**
	 * обновить link_list у записи
	 *
	 * @param int   $user_id
	 * @param int   $loyalty_id
	 * @param array $link_list
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function updateLinkList(int $user_id, int $loyalty_id, array $link_list):void {

		try {
			$user_card_loyalty = Gateway_Db_CompanyMember_UsercardLoyaltyList::getOne($user_id, $loyalty_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("Unknown user_card loyalty");
		}

		$data = self::_updateLinkListInData($user_card_loyalty->data, $link_list);

		$set = [
			"updated_at" => time(),
			"data"       => $data,
		];
		Gateway_Db_CompanyMember_UsercardLoyaltyList::set($user_id, $loyalty_id, $set);
	}

	/**
	 * редактируем запись
	 *
	 * @throws \parseException
	 */
	public static function edit(int $user_id, int $loyalty_id, string $new_comment, int $new_reaction_value, int $new_sport_value, int $new_department_life_value, array $link_list):array {

		$data  = self::initData($new_sport_value, $new_reaction_value, $new_department_life_value, $link_list);
		$value = self::_getAvgFromData($data);
		$set   = [
			"comment_text" => $new_comment,
			"updated_at"   => time(),
			"data"         => $data,
		];

		Gateway_Db_CompanyMember_UsercardLoyaltyList::set($user_id, $loyalty_id, $set);

		return [$value, self::_getValueGroupedByTypeFromData($data)];
	}

	/**
	 * помечаем запись вовлеченности удаленной
	 *
	 * @throws \parseException
	 */
	public static function delete(int $user_id, int $loyalty_id):void {

		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];
		Gateway_Db_CompanyMember_UsercardLoyaltyList::set($user_id, $loyalty_id, $set);
	}

	/**
	 * Получаем dynamic данные для вовлеченности пользователя
	 */
	public static function getDynamicData(array $loyalty_value_data, int $loyalty_count):array {

		$total_value           = 0;
		$total_reaction        = 0;
		$total_sport           = 0;
		$total_department_life = 0;
		if ($loyalty_count > 0) {

			$total_sport           = round($loyalty_value_data[self::SPORT_VALUE_TYPE] / $loyalty_count);
			$total_reaction        = round($loyalty_value_data[self::REACTION_VALUE_TYPE] / $loyalty_count);
			$total_department_life = round($loyalty_value_data[self::DEPARTMENT_VALUE_TYPE] / $loyalty_count);

			// получаем total значение
			$total_value = round(($total_sport + $total_reaction + $total_department_life) / 3);
		}

		return [
			"value"                 => $total_value,
			"reaction_value"        => $total_reaction,
			"sport_value"           => $total_sport,
			"department_life_value" => $total_department_life,
		];
	}

	/**
	 * получаем reaction value из даты по типу
	 */
	public static function getLinkList(Struct_Domain_Usercard_Loyalty $loyalty):array {

		return self::_getLinkListFromData($loyalty->data);
	}

	/**
	 * обновляем значения оценки вовлеченности в card_dynamic_data при добавлении оценки
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 * @long
	 */
	public static function updateDynamicDataIfAdd(int $user_id, int $new_sport_value, int $new_reaction_value, int $new_department_life_value):Struct_Domain_Usercard_Dynamic {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();

			// создаем запись и сразу добавляем значения оценки
			$data = Type_User_Card_DynamicData::initData();
			$data = Type_User_Card_DynamicData::setLoyaltyCountData($data, 1);
			$data = Type_User_Card_DynamicData::setValueForAllLoyaltyCategory($data, $new_sport_value, $new_reaction_value, $new_department_life_value);
			return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
		}

		// достаем из data loyalty_count & значения оценки, разбитые по категориям
		$loyalty_count          = Type_User_Card_DynamicData::getLoyaltyCount($dynamic_obj->data);
		$loyalty_data           = Type_User_Card_DynamicData::getLoyaltyValueGroupedByCategory($dynamic_obj->data);
		$total_sport_value      = $loyalty_data[Type_User_Card_Loyalty::SPORT_VALUE_TYPE];
		$total_reaction_value   = $loyalty_data[Type_User_Card_Loyalty::REACTION_VALUE_TYPE];
		$total_department_value = $loyalty_data[Type_User_Card_Loyalty::DEPARTMENT_VALUE_TYPE];

		// инкрементим и суммируем значения оценки
		$loyalty_count++;
		$dynamic_obj->data               = Type_User_Card_DynamicData::setLoyaltyCountData($dynamic_obj->data, $loyalty_count);
		$new_total_sport_value           = $total_sport_value + $new_sport_value;
		$new_total_reaction_value        = $total_reaction_value + $new_reaction_value;
		$new_total_department_life_value = $total_department_value + $new_department_life_value;
		$dynamic_obj->data               = Type_User_Card_DynamicData::setValueForAllLoyaltyCategory(
			$dynamic_obj->data, $new_total_sport_value, $new_total_reaction_value, $new_total_department_life_value
		);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();

		// возвращаем обновленный dynamic-data
		return $dynamic_obj;
	}

	/**
	 * обновляем значения оценки вовлеченности в card_dynamic_data при удалении оценки
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 * @long
	 */
	public static function updateDynamicDataIfRemove(int $user_id, array $value_grouped_by_type):Struct_Domain_Usercard_Dynamic {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Type_User_Card_DynamicData::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();

			// создаем запись и сразу устанавливаем значение по нулям
			$data = Type_User_Card_DynamicData::initData();
			$data = Type_User_Card_DynamicData::setLoyaltyCountData($data, 0);
			$data = Type_User_Card_DynamicData::setValueForAllLoyaltyCategory($data, 0, 0, 0);
			return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
		}

		// достаем из data loyalty_count
		$loyalty_count = Type_User_Card_DynamicData::getLoyaltyCount($dynamic_obj->data);

		// если декрементить дальше некуда
		if ($loyalty_count == 0) {

			Gateway_Db_CompanyMember_UsercardDynamic::rollback();
			return $dynamic_obj;
		}

		// получаем из dynamic-данных значения оценки, сгруппированные по категориям
		$total_value_by_category = Type_User_Card_DynamicData::getLoyaltyValueGroupedByCategory($dynamic_obj->data);

		// декрементим для каждой категории
		foreach ($value_grouped_by_type as $category => $value) {

			$total_value                        = $total_value_by_category[$category] - $value;
			$total_value_by_category[$category] = $total_value < 0 ? 0 : $total_value;

			// полученное значение сразу устанавливаем в dynamic-данные
			$dynamic_obj->data = Type_User_Card_DynamicData::setLoyaltyValueByCategory($dynamic_obj->data, $total_value, $category);
		}

		// декрементим общее значение
		$loyalty_count--;
		$dynamic_obj->data = Type_User_Card_DynamicData::setLoyaltyCountData($dynamic_obj->data, $loyalty_count);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Type_User_Card_DynamicData::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();

		// возвращаем обновленный dynamic-data
		return $dynamic_obj;
	}

	/**
	 * обновляем значения оценки вовлеченности в card_dynamic_data при редактировании оценки
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 * @long
	 */
	public static function updateDynamicDataIfEdit(int $user_id, array $new_values_by_category, array $old_values_by_category):Struct_Domain_Usercard_Dynamic {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();

			// создаем запись и сразу добавляем значения оценки
			$data = Type_User_Card_DynamicData::initData();
			$data = Type_User_Card_DynamicData::setLoyaltyCountData($data, 1);

			foreach ($new_values_by_category as $category => $value) {
				$data = Type_User_Card_DynamicData::setLoyaltyValueByCategory($data, $value, $category);
			}

			return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
		}

		// получаем данные для обновления
		$dynamic_obj->data = self::_getDataForUpdateDynamicIfEdit($dynamic_obj->data, $new_values_by_category, $old_values_by_category);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();

		// возвращаем обновленный dynamic-data
		return $dynamic_obj;
	}

	// получаем данные для обновления после редактирования оценки
	protected static function _getDataForUpdateDynamicIfEdit(array $data, array $new_values_by_category, array $old_values_by_category):array {

		// достаем значения оценки вовлеченности, разбитые по категориям
		$loyalty_data = Type_User_Card_DynamicData::getLoyaltyValueGroupedByCategory($data);

		// для каждой оценки проверяем как она изменилась, и в зависимости от результата добавляем или убавляем значения
		$new_data = [];
		foreach ($new_values_by_category as $category => $new_value) {

			$total_category_value = $loyalty_data[$category];
			$old_category_value   = $old_values_by_category[$category];

			// если значение категории не изменилось
			if ($new_value == $old_category_value) {
				continue;
			}

			// если новая оценка категории больше старой
			if ($new_value > $old_category_value) {
				$total_category_value = $total_category_value + ($new_value - $old_category_value);
			}

			// если старая оценка категории больше новой
			if ($new_value < $old_category_value) {
				$total_category_value = $total_category_value - ($old_category_value - $new_value);
			}

			$new_data[$category] = $total_category_value;
		}

		// меняем значения оценки для тех категорий что изменились
		foreach ($new_data as $category => $value) {
			$data = Type_User_Card_DynamicData::setLoyaltyValueByCategory($data, $value, $category);
		}

		// меняем общее значение оценки

		return $data;
	}

	// -------------------------------------------------------
	// DATA METHODS
	// -------------------------------------------------------

	protected const _DATA_LAST_VERSION = 1;
	protected const _DATA_SCHEME       = [
		1 => [
			"link_list"           => [],
			"category_value_list" => [
				self::SPORT_VALUE_TYPE      => 0,
				self::REACTION_VALUE_TYPE   => 0,
				self::DEPARTMENT_VALUE_TYPE => 0,
			],
		],
	];

	/**
	 * инициализируем дату для лояльности
	 */
	public static function initData(int $sport_value, int $reaction_value, int $department_life_value, array $link_list = []):array {

		$scheme = self::_DATA_SCHEME[self::_DATA_LAST_VERSION];

		$scheme["link_list"]                                        = $link_list;
		$scheme["category_value_list"][self::SPORT_VALUE_TYPE]      = $sport_value;
		$scheme["category_value_list"][self::REACTION_VALUE_TYPE]   = $reaction_value;
		$scheme["category_value_list"][self::DEPARTMENT_VALUE_TYPE] = $department_life_value;

		// текущая версия
		$scheme["version"] = self::_DATA_LAST_VERSION;

		return $scheme;
	}

	/**
	 * устанавливаем значения для категорий оценки вовлеченности
	 */
	public static function setCategoryValueList(array $data, int $sport_value, int $reaction_value, int $department_life_value):array {

		$data = self::actualData($data);

		$data["category_value_list"][self::SPORT_VALUE_TYPE]      = $sport_value;
		$data["category_value_list"][self::REACTION_VALUE_TYPE]   = $reaction_value;
		$data["category_value_list"][self::DEPARTMENT_VALUE_TYPE] = $department_life_value;

		return $data;
	}

	/**
	 * актуализируем схему data
	 */
	public static function actualData(array $data):array {

		if ($data["version"] == self::_DATA_LAST_VERSION) {
			return $data;
		}

		// сливаем текущую версию data и полученную
		$data["extra"]   = array_merge(self::_DATA_SCHEME[self::_DATA_LAST_VERSION], $data);
		$data["version"] = self::_DATA_LAST_VERSION;

		return $data;
	}

	/**
	 * получаем среднее значение из даты
	 */
	protected static function _getAvgFromData(array $data):int {

		return round((
				self::_getValueFromData(self::SPORT_VALUE_TYPE, $data) +
				self::_getValueFromData(self::REACTION_VALUE_TYPE, $data) +
				self::_getValueFromData(self::DEPARTMENT_VALUE_TYPE, $data)
			) / 3);
	}

	/**
	 * получаем значение по категориям
	 */
	protected static function _getValueGroupedByTypeFromData(array $data):array {

		return [
			self::REACTION_VALUE_TYPE   => self::_getValueFromData(self::REACTION_VALUE_TYPE, $data),
			self::SPORT_VALUE_TYPE      => self::_getValueFromData(self::SPORT_VALUE_TYPE, $data),
			self::DEPARTMENT_VALUE_TYPE => self::_getValueFromData(self::DEPARTMENT_VALUE_TYPE, $data),
		];
	}

	/**
	 * получаем reaction value из даты по типу
	 */
	protected static function _getValueFromData(int $value_type, array $data):int {

		return $data["category_value_list"][$value_type];
	}

	/**
	 * получаем link_list из даты
	 */
	protected static function _getLinkListFromData(array $data):array {

		return $data["link_list"];
	}

	/**
	 * обновляем link_list в дате
	 */
	protected static function _updateLinkListInData(array $data, array $link_list):array {

		$data["link_list"] = $link_list;
		return $data;
	}
}
