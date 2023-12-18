<?php

namespace Compass\Speaker;

/**
 * класс для работы с meta звонка (независимо от его типа)
 */
class Type_Call_Meta {

	// создаем meta звонка
	public static function create(int $user_id, string $initiator_session_uniq, string $initiator_ip_address, string $initiator_user_agent, string $initiator_device_id, bool $is_initiator_need_relay, int $opponent_user_id, string $conversation_map):array {

		// получаем shard_id & table_id
		$time     = time();
		$shard_id = Type_Pack_Call::getShardIdByTime($time);
		$table_id = Type_Pack_Call::getTableIdByTime($time);

		// создаем запись в meta
		$initiator_user_schema = self::_createInitiatorUserSchema(
			$initiator_session_uniq, $initiator_ip_address, $is_initiator_need_relay, $initiator_user_agent, $initiator_device_id, $conversation_map
		);
		$meta_row              = self::_createMetaRow($user_id, $initiator_user_schema, $opponent_user_id, $conversation_map);
		$meta_row["meta_id"]   = Gateway_Db_CompanyCall_CallMeta::insert($meta_row);

		// формируем call_map
		$call_map = Type_Pack_Call::doPack($shard_id, $table_id, $meta_row["meta_id"]);

		$meta_row["call_map"] = $call_map;

		return $meta_row;
	}

	// создаем user_schema инициатора
	protected static function _createInitiatorUserSchema(string $session_uniq, string $ip_address, bool $is_initiator_need_relay, string $user_agent, string $initiator_device_id, string $conversation_map):array {

		$user_schema = Type_Call_Users::initUserSchema($conversation_map, 0);
		$user_schema = Type_Call_Users::setSpeaking($user_schema, true);
		$user_schema = Type_Call_Users::setSessionUniq($user_schema, $session_uniq);
		$user_schema = Type_Call_Users::setUserAgent($user_schema, $user_agent);
		$user_schema = Type_Call_Users::setJoinedAt($user_schema, time());
		$user_schema = Type_Call_Users::setDeviceId($user_schema, $initiator_device_id);
		$user_schema = Type_Call_Users::setNeedRelay($user_schema, $is_initiator_need_relay);
		$user_schema = Type_Call_Users::setIpAddress($user_schema, $ip_address);

		return $user_schema;
	}

	// формируем мету single-звонка
	// @long — потому что формируем массив
	protected static function _createMetaRow(int $initiator_user_id, array $initiator_user_schema, int $opponent_user_id, string $conversation_map):array {

		$opponent_user_schema = Type_Call_Users::initUserSchema($conversation_map, $initiator_user_id);
		$opponent_user_schema = Type_Call_Users::setJoinedAt($opponent_user_schema, time());
		$call_id              = Type_Call_Utils::generateCallId();
		$meta_row             = [
			"meta_id"         => null,
			"type"            => CALL_TYPE_SINGLE,
			"is_finished"     => 0,
			"creator_user_id" => $initiator_user_id,
			"created_at"      => time(),
			"started_at"      => 0,
			"finished_at"     => 0,
			"updated_at"      => 0,
			"extra"           => Gateway_Db_CompanyCall_CallMeta::initExtraSchema($call_id),
			"users"           => [
				$initiator_user_id => $initiator_user_schema,
				$opponent_user_id  => $opponent_user_schema,
			],
		];

		return $meta_row;
	}

	// получаем запись meta звонка
	public static function get(string $call_map):array {

		return Gateway_Db_CompanyCall_CallMeta::getOne($call_map);
	}

	// получаем все записи
	public static function getAll(array $call_map_list):array {

		// группируем все call_key по shard_id и table_id
		$grouped_call_map_list = self::_doGroupCallMapListByShardIdAndTableId($call_map_list);

		// получаем информацию о звонках из базы
		return self::_getCallList($grouped_call_map_list);
	}

	// группируем call_map_list по shard_id и table_id
	protected static function _doGroupCallMapListByShardIdAndTableId(array $call_map_list):array {

		$output = [];
		foreach ($call_map_list as $item) {

			// получаем ключ и закидываем в output
			$full_table_name = self::_getFullTableNameFromCallMap($item);
			$meta_id         = Type_Pack_Call::getMetaId($item);

			$output[$full_table_name][$item] = $meta_id;
		}

		return $output;
	}

	// получаем ключ для группировки call_map_list
	protected static function _getFullTableNameFromCallMap(string $call_map):string {

		$shard_id = Type_Pack_Call::getShardId($call_map);
		$table_id = Type_Pack_Call::getTableId($call_map);
		return "{$shard_id}.{$table_id}";
	}

	// получаем записи из базы
	protected static function _getCallList(array $grouped_call_map_list):array {

		$output = [];
		foreach ($grouped_call_map_list as $v) {

			$call_list = Gateway_Db_CompanyCall_CallMeta::getAll($v);

			foreach ($call_list as $item) {
				$output[] = $item;
			}
		}

		return $output;
	}

	// помечаем, что пользователь потерял соединение
	public static function setUserLostConnection(string $call_map, int $user_id, bool $is_lost_connection):array {

		Gateway_Db_CompanyCall_Main::beginTransaction();

		// получаем запись на обновление
		$meta_row = Gateway_Db_CompanyCall_CallMeta::getOneForUpdate($call_map);

		// помечаем в user_schema флаг is_lost_connection для пользователя
		$meta_row["users"][$user_id] = Type_Call_Users::setLostConnection($meta_row["users"], $user_id, $is_lost_connection);
		$meta_row["updated_at"]      = time();

		// сохраняем измненеия
		$set = [
			"users"      => $meta_row["users"],
			"updated_at" => $meta_row["updated_at"],
		];
		Gateway_Db_CompanyCall_CallMeta::set($call_map, $set);

		Gateway_Db_CompanyCall_Main::commitTransaction();

		return $meta_row;
	}

	// записываем результаты пинга ноды
	public static function setPingResult(string $call_map, int $user_id, int $node_id, int $latency):void {

		Gateway_Db_CompanyCall_Main::beginTransaction();

		// получаем запись на обновление
		$meta_row = Gateway_Db_CompanyCall_CallMeta::getOneForUpdate($call_map);

		// помечаем в user_schema результаты пинга ноды пользователем
		$meta_row["users"][$user_id] = Type_Call_Users::setPingResult(
			$meta_row["users"],
			$user_id,
			$node_id,
			$latency
		);

		// сохраняем изменения
		$set = [
			"users"      => $meta_row["users"],
			"updated_at" => time(),
		];
		Gateway_Db_CompanyCall_CallMeta::set($call_map, $set);

		Gateway_Db_CompanyCall_Main::commitTransaction();
	}

	// получаем report_call_id
	public static function getReportCallId(array $extra):int {

		return Gateway_Db_CompanyCall_CallMeta::getReportCallId($extra);
	}
}
