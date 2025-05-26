<?php

namespace Compass\Thread;

use BaseFrame\Exception\Gateway\BusFatalException;
use CompassApp\Pack\Message\Thread as ThreadMessage;
use CompassApp\Pack\Thread;

/**
 * Класс для работы с go_company - прочтение сообщений
 */
class Gateway_Bus_Company_ReadMessage extends Gateway_Bus_Company_Main {

	protected const _ENTITY_TYPE = "thread"; // название сущности для микросервиса

	/**
	 * Прочитать сообщение в чате
	 *
	 * @param string $thread_map
	 * @param int    $user_id
	 * @param string $message_map
	 * @param int    $message_created_at
	 * @param array  $hide_read_participant_list
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function add(string $thread_map, int $user_id, string $message_map, int $message_created_at, array $hide_read_participant_list):void {

		$request = new \CompanyGrpc\ReadMessageAddRequestStruct([
			"entity_type"                => self::_ENTITY_TYPE,
			"entity_map"                 => $thread_map,
			"message_map"                => $message_map,
			"message_key"                => ThreadMessage::doEncrypt($message_map),
			"message_created_at"         => $message_created_at,
			"read_at"                    => time(),
			"table_shard"                => Thread::getTableId($thread_map),
			"db_shard"                   => Thread::getShardId($thread_map),
			"entity_message_index"       => ThreadMessage::getThreadMessageIndex($message_map),
			"entity_meta_id"             => Thread::getMetaId($thread_map),
			"entity_key"                 => Thread::doEncrypt($thread_map),
			"user_id"                    => $user_id,
			"company_id"                 => COMPANY_ID,
			"hide_read_participant_list" => $hide_read_participant_list,
		]);
		[, $status] = self::_doCallGrpc("ReadMessageAdd", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}
}
