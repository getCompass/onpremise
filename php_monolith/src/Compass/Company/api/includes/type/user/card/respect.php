<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс для работы с сущностью respect
 */
class Type_User_Card_Respect {

	/** @var int тип респекта по умолчанию */
	public const RESPECT_TYPE_DEFAULT = 1;

	/** @var int максимальная длина комментария респекта пользователя */
	public const MAX_RESPECT_COMMENT_LENGTH = 2000;

	/** @var int время, в течении которого можно редактировать респект */
	public const ALLOW_TO_EDIT_TIME = 60 * 10;

	/** @var int время, в течении которого можно удалять респект */
	public const ALLOW_TO_DELETE_TIME = 60 * 10;

	/**
	 * добавляем новый респект пользователя в базу
	 *
	 * @throws \parseException|\queryException
	 */
	public static function add(int $user_id, int $creator_user_id, int $type, string $respect_text, int $created_at):Struct_Domain_Usercard_Respect {

		return Gateway_Db_CompanyMember_UsercardRespectList::add($user_id, $creator_user_id, $type, $respect_text, $created_at);
	}

	/**
	 * получаем респект пользователя
	 *
	 * @throws paramException
	 */
	public static function get(int $user_id, int $respect_id):Struct_Domain_Usercard_Respect {

		try {
			return Gateway_Db_CompanyMember_UsercardRespectList::get($user_id, $respect_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("not found respect");
		}
	}

	/**
	 * возвращает список респектов пользователя
	 *
	 * @return Struct_Domain_Usercard_Respect[]
	 */
	public static function getList(int $user_id, int $last_respect_id, int $limit):array {

		// формируем и осуществляем запрос
		if ($last_respect_id == 0) {
			return Gateway_Db_CompanyMember_UsercardRespectList::getLastRespectList($user_id, $limit);
		}
		return Gateway_Db_CompanyMember_UsercardRespectList::getRespectListAfterId($user_id, $last_respect_id, $limit);
	}

	/**
	 * обновляем запись респекта в базе
	 */
	public static function set(int $user_id, int $respect_id, array $set):void {

		Gateway_Db_CompanyMember_UsercardRespectList::set($user_id, $respect_id, $set);
	}

	/**
	 * обновляем запись респекта в базе
	 */
	public static function setByRespectId(int $respect_id, array $set):void {

		Gateway_Db_CompanyMember_UsercardRespectList::setByRespectId($respect_id, $set);
	}

	/**
	 * редактируем респект
	 */
	public static function edit(int $user_id, int $respect_id, string $comment_text, array $data):void {

		$set = [
			"respect_text" => $comment_text,
			"updated_at"   => time(),
			"data"         => toJson($data),
		];
		self::set($user_id, $respect_id, $set);
	}

	/**
	 * помечаем респект удаленным
	 */
	public static function delete(int $user_id, int $respect_id):void {

		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];

		self::set($user_id, $respect_id, $set);
	}

	/**
	 * Получаем список респектов по их id
	 *
	 * @return Struct_Domain_Usercard_Respect[]
	 */
	public static function getListByIdList(array $respect_id_list):array {

		return Gateway_Db_CompanyMember_UsercardRespectList::getListByIdList($respect_id_list);
	}

	/**
	 * получаем респекты за месяц
	 */
	public static function getListByMonthAt(int $creator_user_id, int $month_start_at, int $limit, int $offset):array {

		// определяем период, за который нужно достать список респектов
		[$from_date_at, $to_date_at] = getPeriodByMonthStartAt($month_start_at);

		// получаем респекты за этот период
		return Gateway_Db_CompanyMember_UsercardRespectList::getAllByPeriod($creator_user_id, $from_date_at, $to_date_at, $limit, $offset);
	}

	/**
	 * получаем список за период
	 */
	public static function getAllByPeriod(int $creator_user_id, int $from_date_at, int $to_date_at, int $limit, int $offset):array {

		return Gateway_Db_CompanyMember_UsercardRespectList::getAllByPeriod($creator_user_id, $from_date_at, $to_date_at, $limit, $offset);
	}

	/**
	 * помечаем респект удаленным для создателя респекта
	 *
	 * @throws \parseException
	 */
	public static function deleteForCreator(int $creator_user_id, int $respect_id):void {

		Gateway_Db_CompanyMember_UsercardRespectList::deleteForCreator($creator_user_id, $respect_id);
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * Можно ли редактировать респект, проверяем время отправки респекта
	 */
	public static function isTimeAllowToEdit(Struct_Domain_Usercard_Respect $respect):bool {

		// если время отправки сообщения не позволяет его редактировать
		if (time() > $respect->created_at + self::ALLOW_TO_EDIT_TIME) {
			return false;
		}

		return true;
	}

	/**
	 * Можно ли удалить респект, проверяем время отправки респекта
	 */
	public static function isTimeAllowToDelete(Struct_Domain_Usercard_Respect $respect):bool {

		// если время отправки респекта не позволяет его удалять
		if (time() > $respect->created_at + self::ALLOW_TO_DELETE_TIME) {
			return false;
		}

		return true;
	}

	/**
	 * Инкремент значения респекта в card_dynamic_data
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	public static function incInDynamicData(int $user_id):Struct_Domain_Usercard_Dynamic {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();

			// создаем запись и сразу инкрементим значение
			$data = Type_User_Card_DynamicData::initData();
			$data = Type_User_Card_DynamicData::setRespectCount($data, 1);
			return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
		}

		// достаем из data respect_count
		$respect_count = Type_User_Card_DynamicData::getRespectCount($dynamic_obj->data);

		// инкрементим
		$respect_count++;
		$dynamic_obj->data = Type_User_Card_DynamicData::setRespectCount($dynamic_obj->data, $respect_count);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();

		// возвращаем обновленный dynamic-data
		return $dynamic_obj;
	}

	/**
	 * Декремент значения респекта в card_dynamic_data
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	public static function decInDynamicData(int $user_id):Struct_Domain_Usercard_Dynamic {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Type_User_Card_DynamicData::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();

			// создаем запись и сразу устанавливаем значение по нулям
			$data = Type_User_Card_DynamicData::initData();
			$data = Type_User_Card_DynamicData::setRespectCount($data, 0);
			return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
		}

		// достаем из data respect_count
		$respect_count = Type_User_Card_DynamicData::getRespectCount($dynamic_obj->data);

		// если декрементить дальше некуда
		if ($respect_count == 0) {

			Gateway_Db_CompanyMember_UsercardDynamic::rollback();
			return $dynamic_obj;
		}

		// декрементим
		$respect_count--;
		$dynamic_obj->data = Type_User_Card_DynamicData::setRespectCount($dynamic_obj->data, $respect_count);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Type_User_Card_DynamicData::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();

		// возвращаем обновленный dynamic-data
		return $dynamic_obj;
	}

	##########################################################
	# region DATA VARIABLES
	##########################################################

	protected const _DATA_VERSION = 1; // версия json структуры респекта
	protected const _DATA_SCHEMA  = [

		1 => [
			"link_list"   => [], // список ссылок, прикрепленных к тексту респекта
			"message_map" => "", // ключ сообщения-респекта в группе Благодарности
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
	 * Достаем список закрепленных за респектом ссылок
	 */
	public static function getLinkList(array $data):array {

		$data = self::actualData($data);

		return $data["link_list"];
	}

	/**
	 * Закрепляем список ссылок за респектом
	 */
	public static function setLinkList(array $data, array $link_list):array {

		$data = self::actualData($data);

		$data["link_list"] = $link_list;

		return $data;
	}

	/**
	 * Достаем ключ сообщения-благодарности, которая закреплена за сущностью респекта
	 */
	public static function getMessageMap(array $data):string {

		$data = self::actualData($data);

		return $data["message_map"];
	}

	/**
	 * Закрепляем ключ сообщения-благодарности за сущностью респекта
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
