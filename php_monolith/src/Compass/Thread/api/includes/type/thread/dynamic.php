<?php

namespace Compass\Thread;

/**
 * класс для работы с dynamic треда
 */
class Type_Thread_Dynamic {

	// получает запись из dynamic
	public static function get(string $thread_map):Struct_Db_CompanyThread_ThreadDynamic {

		return Gateway_Db_CompanyThread_ThreadDynamic::getOne($thread_map);
	}

	/**
	 * Получить список динамических записей для тредов
	 *
	 * @param array $thread_map_list
	 *
	 * @return Struct_Db_CompanyThread_ThreadDynamic[]
	 */
	public static function getList(array $thread_map_list):array {

		return Gateway_Db_CompanyThread_ThreadDynamic::getAll($thread_map_list, true);
	}

	// обновляет запись в dynamic
	public static function set(string $thread_map, array $set):void {

		// обновляем запись
		Gateway_Db_CompanyThread_ThreadDynamic::set($thread_map, $set);
	}

	// обновляет user_mute_info для пользователя в треде
	public static function setIsMuted(string $thread_map, int $user_id, bool $is_muted):void {

		// открываем транзакцию
		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем запись на обновление
		$dynamic_obj = Gateway_Db_CompanyThread_ThreadDynamic::getForUpdate($thread_map);

		// меняем структуру
		$user_mute_info_item = $dynamic_obj->user_mute_info[$user_id] ?? Gateway_Db_CompanyThread_ThreadDynamic::initUserMuteInfoItem();
		$user_mute_info_item = Gateway_Db_CompanyThread_ThreadDynamic::setIsMuted($user_mute_info_item, $is_muted);

		$dynamic_obj->user_mute_info[$user_id] = $user_mute_info_item;

		// обновляем запись
		Gateway_Db_CompanyThread_ThreadDynamic::set($thread_map, [
			"user_mute_info" => $dynamic_obj->user_mute_info,
			"updated_at"     => time(),
		]);

		// коммитим транзакцию
		Gateway_Db_CompanyThread_Main::commitTransaction();
	}

	// узнать в муте ли тред
	public static function isMuted(int $user_id, array $user_mute_info):bool {

		return Gateway_Db_CompanyThread_ThreadDynamic::isMuted($user_mute_info, $user_id);
	}

	/**
	 * Добавляем пользователя в скрывшие тред
	 */
	public static function addHideUser(int $user_id, string $thread_map):void {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		$dynamic_row = Gateway_Db_CompanyThread_ThreadDynamic::getForUpdate($thread_map);

		// если запись не существует
		if (!isset($dynamic_row->thread_map)) {

			// откатываем транзакцию и выходим
			Gateway_Db_CompanyThread_Main::rollback();
			return;
		}

		array_push($dynamic_row->user_hide_list, $user_id);

		// обновляем запись
		Gateway_Db_CompanyThread_ThreadDynamic::set($thread_map, [
			"user_hide_list" => $dynamic_row->user_hide_list,
		]);

		Gateway_Db_CompanyThread_Main::commitTransaction();
	}
}