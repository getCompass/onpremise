<?php

namespace Compass\Thread;

/**
 * класс для работы с подписчиками треда
 */
class Type_Thread_Followers {

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// отписываем пользователя от треда
	public static function doUnfollowUser(int $user_id, string $thread_map):array {

		// открываем транзакцию
		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем запись на обновление
		$follower_row = Gateway_Db_CompanyThread_ThreadFollowerList::getForUpdate($thread_map);

		// добавляем пользователя к списку отписанных
		$follower_row["unfollower_list"][$user_id] = Gateway_Db_CompanyThread_ThreadFollowerList::initUnfollowerSchema();

		// обновляем запись
		Gateway_Db_CompanyThread_ThreadFollowerList::set($thread_map, [
			"unfollower_list" => $follower_row["unfollower_list"],
		]);

		// закрываем транзакцию
		Gateway_Db_CompanyThread_Main::commitTransaction();

		return $follower_row;
	}

	// подписываем пользователя на тред
	public static function doFollowUserList(array $user_id_list, string $thread_map):array {

		Gateway_Db_CompanyThread_Main::beginTransaction();
		$follower_row = Gateway_Db_CompanyThread_ThreadFollowerList::getForUpdate($thread_map);

		foreach ($user_id_list as $user_id) {

			// убираем пользователя из списка отписанных
			unset($follower_row["unfollower_list"][$user_id]);

			// добавляем пользователя к списку подписанных
			$follower_row["follower_list"][$user_id] = Gateway_Db_CompanyThread_ThreadFollowerList::initFollowerSchema();
		}

		// обновляем запись
		Gateway_Db_CompanyThread_ThreadFollowerList::set($thread_map, [
			"unfollower_list" => $follower_row["unfollower_list"],
			"follower_list"   => $follower_row["follower_list"],
		]);

		// закрываем транзакцию
		Gateway_Db_CompanyThread_Main::commitTransaction();

		return $follower_row;
	}

	// убираем пользователя из всех списков
	public static function doClearFollowUser(int $user_id, string $thread_map):array {

		Gateway_Db_CompanyThread_Main::beginTransaction();
		$follower_row = Gateway_Db_CompanyThread_ThreadFollowerList::getForUpdate($thread_map);

		// убираем пользователя из списка отписанных
		unset($follower_row["unfollower_list"][$user_id]);
		unset($follower_row["follower_list"][$user_id]);

		// обновляем запись
		Gateway_Db_CompanyThread_ThreadFollowerList::set($thread_map, [
			"unfollower_list" => $follower_row["unfollower_list"],
			"follower_list"   => $follower_row["follower_list"],
		]);

		// закрываем транзакцию
		Gateway_Db_CompanyThread_Main::commitTransaction();

		return $follower_row;
	}

	// нужно ли подписывать пользователя на тред
	public static function isFollowUser(int $user_id, array $follower_row):bool {

		if (!self::isUserUnfollow($user_id, $follower_row) && isset($follower_row["follower_list"][$user_id])) {
			return true;
		}

		return false;
	}

	// проверяем был ли уже пользователь подписан на тред
	public static function isUserWasUnfollow(int $user_id, array $follower_row):bool {

		if (self::isUserUnfollow($user_id, $follower_row) && isset($follower_row["follower_list"][$user_id])) {
			return true;
		}

		return false;
	}

	// проверяем, отписан ли пользователь от треда (кейс создания треда на удалённое сообщение)
	public static function isUserUnfollow(int $user_id, array $follower_row):bool {

		return isset($follower_row["unfollower_list"][$user_id]);
	}

	// получает список пользователей, подписанных на тред
	public static function getFollowerUsersDiff(array $follower_row):array {

		$follower_list   = array_keys($follower_row["follower_list"]);
		$unfollower_list = array_keys($follower_row["unfollower_list"]);

		return array_values(array_diff($follower_list, $unfollower_list));
	}

	// метод для получения записи из follower_list
	public static function get(string $thread_map):array {

		return Gateway_Db_CompanyThread_ThreadFollowerList::getOne($thread_map);
	}

	// метод для получения несколько записей из follower_list
	public static function getList(array $thread_map_list, bool $is_assoc = false):array {

		$follower_list = Gateway_Db_CompanyThread_ThreadFollowerList::getList($thread_map_list);

		if (!$is_assoc) {
			return $follower_list;
		}

		$assoc_follower_list = [];
		foreach ($follower_list as $followers_row) {

			$assoc_follower_list[$followers_row["thread_map"]] = $followers_row;
		}

		return $assoc_follower_list;
	}
}