<?php

namespace Compass\Conversation;

/**
 * класс, который работает с жалобами пользователя
 */
class Gateway_Db_CompanyConversation_MessageReportHistory extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "message_report_history";

	// заносит репорт в очередь
	public static function insert(string $message_map, int $user_id, string $reason):void {

		$insert = [
			"message_map" => $message_map,
			"user_id"     => $user_id,
			"reason"      => $reason,
		];

		ShardingGateway::database(static::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}
}