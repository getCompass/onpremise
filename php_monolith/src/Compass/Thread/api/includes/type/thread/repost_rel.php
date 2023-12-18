<?php

namespace Compass\Thread;

/**
 * класс для работы с историей репостов из треда (thread_map)
 * в диалог получатель ($reciever_conversation_map)
 */
class Type_Thread_RepostRel {

	// функция для добавления записи в историю
	public static function add(string $thread_map, string $receiver_conversation_map, string $message_map, int $user_id):void {

		Gateway_Db_CompanyThread_MessageRepostConversationRel::insert([
			"thread_map"                => $thread_map,
			"message_map"               => $message_map,
			"reciever_conversation_map" => $receiver_conversation_map,
			"user_id"                   => $user_id,
			"is_deleted"                => 0,
			"created_at"                => time(),
			"updated_at"                => 0,
			"deleted_at"                => 0,
		]);
	}

	// функция для добавления массива записей в историю
	public static function addList(string $thread_map, string $receiver_conversation_map, array $message_map_list, int $user_id):void {

		$set = [];

		foreach ($message_map_list as $v) {

			$set[] = [
				"thread_map"                => $thread_map,
				"message_map"               => $v,
				"reciever_conversation_map" => $receiver_conversation_map,
				"user_id"                   => $user_id,
				"is_deleted"                => 0,
				"created_at"                => time(),
				"updated_at"                => 0,
				"deleted_at"                => 0,
			];
		}

		if (count($set) > 0) {
			Gateway_Db_CompanyThread_MessageRepostConversationRel::insertArray($set);
		}
	}

	// функция для помечания удаленной записи об истории
	// - thread_map - тред которому принадлежат репостнутые сообщения
	// - message_map - само сообщение с репостом
	public static function setMessageDeleted(string $thread_map, string $message_map):void {

		Gateway_Db_CompanyThread_MessageRepostConversationRel::set($thread_map, $message_map, [
			"is_deleted" => 1,
			"deleted_at" => time(),
		]);
	}
}