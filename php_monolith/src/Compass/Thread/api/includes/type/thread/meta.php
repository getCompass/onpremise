<?php

namespace Compass\Thread;

use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\ParamException;

/**
 * класс для работы с сущностью meta треда
 */
class Type_Thread_Meta {

	// получаем сущность meta

	/**
	 * Получаем сущность meta
	 *
	 * @param string $thread_map
	 *
	 * @return array
	 * @throws ParamException
	 */
	public static function getOne(string $thread_map):array {

		try {
			return Gateway_Db_CompanyThread_ThreadMeta::getOne($thread_map);
		} catch (RowNotFoundException) {
			throw new ParamException("thread not found");
		}
	}

	// получаем все записи
	public static function getAll(array $thread_map_list):array {

		// получаем информацию о тредах из базы
		return Gateway_Db_CompanyThread_ThreadMeta::getAll($thread_map_list);
	}

	// получаем все записи, где пользователь является создателем
	public static function getAllWhereCreator(int $user_id, array $thread_map_list):array {

		return Gateway_Db_CompanyThread_ThreadMeta::getAllWhereCreator($user_id, $thread_map_list);
	}

	// изменить существующую meta
	public static function set(string $thread_map, array $set):void {

		Gateway_Db_CompanyThread_ThreadMeta::set($thread_map, $set);
	}

	// обновляет is_readonly для треда
	public static function setIsReadOnly(string $thread_map, bool $is_readonly):void {

		// обновляем запись
		Gateway_Db_CompanyThread_ThreadMeta::set($thread_map, [
			"is_readonly" => $is_readonly ? 1 : 0,
			"updated_at"  => time(),
		]);
	}

	// добавить количество скрытых сообщений у пользователей
	public static function incCountHiddenMessage(string $thread_map, int $user_id, int $inc_count = 1):array {

		// открываем транзакцию
		Gateway_Db_CompanyThread_ThreadMeta::beginTransaction();

		// получаем мету на обновление
		$meta_row = Gateway_Db_CompanyThread_ThreadMeta::getForUpdate($thread_map);

		// наращиваем количество скрытых сообщений у пользователя
		$meta_row["users"][$user_id] = Type_Thread_Meta_Users::incCountHiddenMessage($meta_row["users"][$user_id], $inc_count);

		// записываем обновленную информацию пользователя в базу данных
		$set = [
			"users"      => $meta_row["users"],
			"updated_at" => time(),
		];
		Gateway_Db_CompanyThread_ThreadMeta::set($thread_map, $set);

		// закрываем транзакцию
		Gateway_Db_CompanyThread_ThreadMeta::commitTransaction();

		return $meta_row;
	}

	// помечаем родителя удаленным в parent_rel
	public static function setParentRelIsDeleted(string $thread_map):array {

		// открываем транзакцию
		Gateway_Db_CompanyThread_ThreadMeta::beginTransaction();

		// получаем мету на обновление
		$meta_row            = Gateway_Db_CompanyThread_ThreadMeta::getForUpdate($thread_map);
		$parent_rel = Type_Thread_ParentRel::setIsDeleted($meta_row["parent_rel"], true);

		// обновляем
		$set = [
			"is_readonly"         => 1,
			"parent_rel" => $parent_rel,
			"updated_at"          => time(),
		];
		Gateway_Db_CompanyThread_ThreadMeta::set($thread_map, $set);

		// закрываем транзакцию
		Gateway_Db_CompanyThread_ThreadMeta::commitTransaction();

		return $meta_row;
	}

	// помечаем родителя скрытым в parent_rel
	public static function setParentRelIsHiddenOrUnhiddenForUser(string $thread_map, bool $is_hidden, int $user_id):array {

		// открываем транзакцию
		Gateway_Db_CompanyThread_ThreadMeta::beginTransaction();

		// получаем мету на обновление
		$meta_row = Gateway_Db_CompanyThread_ThreadMeta::getForUpdate($thread_map);

		// обновляем, если есть изменения
		if (Type_Thread_ParentRel::isMessageHiddenByUserId($meta_row["parent_rel"], $user_id) !== $is_hidden) {

			$parent_rel = Type_Thread_ParentRel::setMessageHiddenOrUnhiddenByUserId($meta_row["parent_rel"], $user_id, $is_hidden);

			// обновляем
			$set = [
				"parent_rel" => $parent_rel,
				"updated_at"          => time(),
			];
			Gateway_Db_CompanyThread_ThreadMeta::set($thread_map, $set);
		}

		// закрываем транзакцию
		Gateway_Db_CompanyThread_ThreadMeta::commitTransaction();

		return $meta_row;
	}

	// обновляет is_readonly для тредов
	public static function setListIsReadOnly(array $thread_map_list, bool $is_readonly):void {

		Gateway_Db_CompanyThread_ThreadMeta::setAll($thread_map_list, [
			"is_readonly" => $is_readonly ? 1 : 0,
			"updated_at"  => time(),
		]);
	}

	// получаем список юзеров для action users
	// через отдельную функцию - на случай если в meta придется затем возвращать юзеров не только из last_sender_data
	public static function getActionUsersList(array $meta_row):array {

		return Type_Thread_Meta_LastSenderData::getActionUsersList($meta_row["last_sender_data"]);
	}

	// убираем сообщение из last_sender_data при системном скрытии
	public static function updateLastSenderDataOnMessageSystemDeleted(string $thread_map, string $message_map):void {

		Gateway_Db_CompanyThread_ThreadMeta::beginTransaction();
		Gateway_Db_CompanyThread_ThreadMeta::updateThreadMetaOnMessageSystemDeleted($thread_map, $message_map);
		Gateway_Db_CompanyThread_ThreadMeta::commitTransaction();
	}
}