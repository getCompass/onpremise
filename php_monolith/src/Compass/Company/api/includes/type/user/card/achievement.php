<?php

namespace Compass\Company;

/**
 * Класс для работы с сущностью достижения
 */
class Type_User_Card_Achievement {

	public const MAX_ACHIEVEMENT_HEADER_LENGTH  = 80;   // максимальная длина заголовка достижения пользователя
	public const MAX_ACHIEVEMENT_COMMENT_LENGTH = 2000; // максимальная длина комментария достижения пользователя

	public const ACHIEVEMENT_TYPE_DEFAULT = 0; // дефолтный тип достижения

	/**
	 * Достаем конкретное достижение пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $user_id, int $achievement_id):Struct_Domain_Usercard_Achievement {

		return Gateway_Db_CompanyMember_UsercardAchievementList::get($user_id, $achievement_id);
	}

	/**
	 * Возвращает список достижений пользователя
	 *
	 * @return Struct_Domain_Usercard_Achievement[]
	 */
	public static function getList(int $user_id, int $last_achievement_id, int $limit):array {

		// формируем запрос в зависимости от значения last_loyalty_id
		if ($last_achievement_id == 0) {
			return Gateway_Db_CompanyMember_UsercardAchievementList::getLastAchievementList($user_id, $limit);
		}

		return Gateway_Db_CompanyMember_UsercardAchievementList::getAchievementListAfterId($user_id, $last_achievement_id, $limit);
	}

	/**
	 * Достаем список достижений по их идентификаторам
	 *
	 * @return Struct_Domain_Usercard_Achievement[]
	 */
	public static function getListByIdList(array $achievement_id_list):array {

		return Gateway_Db_CompanyMember_UsercardAchievementList::getListByIdList($achievement_id_list);
	}

	/**
	 * Обновляем достижения пользователя
	 */
	public static function setByIdList(int $receiver_user_id, array $achievement_id_list, array $set):void {

		Gateway_Db_CompanyMember_UsercardAchievementList::setByIdList($receiver_user_id, $achievement_id_list, $set);
	}

	/**
	 * Добавляем новое достижение пользователя в базу
	 *
	 * @throws \parseException|\queryException
	 */
	public static function add(int $user_id, int $creator_user_id, int $type, string $header_text, string $description_text):Struct_Domain_Usercard_Achievement {

		$data = self::initData();

		return Gateway_Db_CompanyMember_UsercardAchievementList::add($user_id, $creator_user_id, $type, $header_text, $description_text, $data);
	}

	/**
	 * Обновляем запись достижения в базе
	 */
	public static function set(int $user_id, int $achievement_id, array $set):void {

		Gateway_Db_CompanyMember_UsercardAchievementList::set($user_id, $achievement_id, $set);
	}

	/**
	 * Редактируем запись достижения
	 */
	public static function edit(int $user_id, int $achievement_id, string $header_text, string $description_text, array $data):void {

		$set = [
			"header_text"      => $header_text,
			"description_text" => $description_text,
			"updated_at"       => time(),
			"data"             => self::actualData($data),
		];

		self::set($user_id, $achievement_id, $set);
	}

	/**
	 * Помечаем достижение удаленным
	 */
	public static function delete(int $user_id, int $achievement_id):void {

		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];

		self::set($user_id, $achievement_id, $set);
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * Инкремент значения достижения в card_dynamic_data
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	public static function incInDynamicData(int $user_id):Struct_Domain_Usercard_Dynamic {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем запись dynamic-данных на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();

			// создаем запись и сразу инкрементим значение
			$data = Type_User_Card_DynamicData::initData();
			$data = Type_User_Card_DynamicData::setAchievementCount($data, 1);
			return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
		}

		// достаем из data achievement_count
		$achievement_count = Type_User_Card_DynamicData::getAchievementCount($dynamic_obj->data);

		// инкрементим
		$achievement_count++;
		$dynamic_obj->data = Type_User_Card_DynamicData::setAchievementCount($dynamic_obj->data, $achievement_count);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();

		// возвращаем обновленный dynamic-data
		return $dynamic_obj;
	}

	/**
	 * Декремент значения достижения в card_dynamic_data
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
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
			$data = Type_User_Card_DynamicData::setAchievementCount($data, 0);
			return Gateway_Db_CompanyMember_UsercardDynamic::add($user_id, $data);
		}

		// достаем из data achievement_count
		$achievement_count = Type_User_Card_DynamicData::getAchievementCount($dynamic_obj->data);

		// если декрементить дальше некуда
		if ($achievement_count == 0) {

			Gateway_Db_CompanyMember_UsercardDynamic::rollback();
			return $dynamic_obj;
		}

		// декрементим
		$achievement_count--;
		$dynamic_obj->data = Type_User_Card_DynamicData::setAchievementCount($dynamic_obj->data, $achievement_count);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Type_User_Card_DynamicData::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();

		// возвращаем обновленный dynamic-data
		return $dynamic_obj;
	}

	/**
	 * формируем текст сообщения для диалога
	 */
	public static function initConversationMessage(string $header, string $description):string {

		$message_text = "*{$header}*\n";
		$message_text .= $description;

		return $message_text;
	}

	/**
	 * нужно ли редактировать сообщение в группе
	 */
	public static function isNeedMessageEdit(string $message_map, Struct_Domain_Usercard_Achievement $achievement, string $new_header, string $new_description,):bool {

		// если у достижения отсутствует сообщение в группе
		if (mb_strlen($message_map) < 1) {
			return false;
		}

		// проверяем, имеются ли изменения в достижении
		$is_exist_change = $new_header != $achievement->header_text || $new_description != $achievement->description_text;
		if (!$is_exist_change) {
			return false;
		}

		return true;
	}

	##########################################################
	# region DATA VARIABLES
	##########################################################

	protected const _DATA_VERSION = 2; // версия json структуры ачивмента
	protected const _DATA_SCHEMA  = [

		1 => [
			"link_list" => [], // список ссылок, прикрепленных к тексту достижения
		],
		2 => [
			"link_list"   => [],
			"message_map" => "", // message_map сообщения в группе Достижения
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
	 * Достаем список закрепленных за достижением ссылок
	 */
	public static function getLinkList(array $data):array {

		$data = self::actualData($data);

		return $data["link_list"];
	}

	/**
	 * Закрепляем список ссылок за достижением
	 */
	public static function setLinkList(array $data, array $link_list):array {

		$data = self::actualData($data);

		$data["link_list"] = $link_list;

		return $data;
	}

	/**
	 * Закрепляем message_map сообщения за достижением
	 */
	public static function setMessageMap(array $data, string $message_map):array {

		$data = self::actualData($data);

		$data["message_map"] = $message_map;

		return $data;
	}

	/**
	 * Достаем message_map сообщения, закрепленное за достижением
	 */
	public static function getMessageMap(array $data):string {

		$data = self::actualData($data);

		return $data["message_map"];
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