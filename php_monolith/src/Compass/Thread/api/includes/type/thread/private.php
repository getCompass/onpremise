<?php

namespace Compass\Thread;

use Compass\Conversation\Domain_Search_Const;
use Compass\Conversation\Gateway_Db_SpaceSearch_EntitySearchIdRel;

/**
 * класс для работы с тредом типа private ()
 *
 * в момент создания пользователи добавляются в thread_meta
 * область применения: private каналы, любые диалоги
 * права:
 * читать сообщения - пользователи из thread_meta
 * писать сообщения - пользователи из thread_meta
 * ставить реакции - пользователи из thread_meta
 * администратор родительского диалога/канала может удалять любые сообщения и сам тред (в т.ч и в его потомках)
 */
class Type_Thread_Private {

	// метод для создания записей в основных табличка при создании треда
	public static function create(array $users, array $source_parent_rel, array $parent_rel, int $user_id, int $creator_user_id, bool $is_need_follow_creator, array $unfollowed_user_id_list_assoc = []):array {

		// получаем shard и table_id из времени
		$time     = time();
		$shard_id = \CompassApp\Pack\Thread::getShardIdByTime($time);
		$table_id = \CompassApp\Pack\Thread::getTableIdByTime($time);
		$meta_id  = Type_Autoincrement_Main::getNextId(Type_Autoincrement_Main::THREAD_META);
		Gateway_Db_CompanyThread_Main::beginTransaction();

		// вставляем запись в тред мету
		$meta_row = Gateway_Db_CompanyThread_ThreadMeta::insert($meta_id, $shard_id, 1, $time, $user_id, $users, $source_parent_rel, $parent_rel);

		// формируем thread_map
		$thread_map = \CompassApp\Pack\Thread::doPack($shard_id, $table_id, $meta_row["meta_id"]);

		self::_insertThreadDynamic($thread_map, $time);

		// подписываем создателя родительской сущности к треду
		self::_insertThreadFollowerList($thread_map, $creator_user_id, $is_need_follow_creator, $unfollowed_user_id_list_assoc);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		// добавляем search_id для треда
		Gateway_Db_SpaceSearch_EntitySearchIdRel::insert(Domain_Search_Const::TYPE_THREAD, $thread_map);

		$meta_row["thread_map"] = $thread_map;
		return $meta_row;
	}

	// метод для вставки данных в thread_dynamic
	protected static function _insertThreadDynamic(string $thread_map, int $time):void {

		$insert = [
			"thread_map"     => $thread_map,
			"is_locked"      => 0,
			"last_block_id"  => 0,
			"start_block_id" => 0,
			"created_at"     => $time,
			"updated_at"     => 0,
			"user_mute_info" => [],
			"user_hide_list" => [],
		];
		Gateway_Db_CompanyThread_ThreadDynamic::insert($insert);
	}

	// метод для вставки данных в thread.follower_list
	protected static function _insertThreadFollowerList(string $thread_map, int $creator_user_id, bool $is_need_follow_creator, array $unfollowed_user_id_list_assoc = []):void {

		$follower_list = [];
		if ($is_need_follow_creator) {
			$follower_list[$creator_user_id] = Gateway_Db_CompanyThread_ThreadFollowerList::initFollowerSchema();
		}

		Gateway_Db_CompanyThread_ThreadFollowerList::insert([
			"thread_map"      => $thread_map,
			"follower_list"   => $follower_list,
			"unfollower_list" => $unfollowed_user_id_list_assoc,
		]);
	}
}