<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * здесь содержится вся логика по диалогу "Личный Heroes"
 */
class Type_Conversation_Public_WorkedHours {

	/**
	 * пытаемся создать объект worked_hours
	 *
	 * @throws \parseException
	 */
	public static function doCommit(int $user_id, float $worked_hours, int $day_start_at = null, bool $is_auto_commit = false):array {

		$ar_post = [
			"worked_hours"   => $worked_hours,
			"is_auto_commit" => $is_auto_commit === true ? 1 : 0,
		];

		// если передан day_start_at
		if (!is_null($day_start_at)) {
			$ar_post["day_start_at"] = $day_start_at;
		}

		[$status, $response] = Gateway_Socket_Company::doCall("employeecard.workedhours.doCommit", $ar_post, $user_id);
		if ($status != "ok") {

			// здесь обязательно что-то будет, но не сегодня
			throw new ParseFatalException(__METHOD__ . ": unexpected status");
		}

		return [
			"worked_hours_id"         => $response["worked_hours_id"],
			"worked_hours_created_at" => $response["worked_hours_created_at"],
			"day_start_at_iso"        => $response["day_start_at_iso"],
		];
	}

	/**
	 * зафиксировать в объекте worked_hours fixed_message_map_list закрепленных сообщений
	 *
	 * @throws \parseException
	 */
	public static function doAppendFixedMessageMap(int $user_id, int $worked_hours_id, array $message_map_list):void {

		$ar_post = [
			"worked_hours_id"  => $worked_hours_id,
			"message_map_list" => $message_map_list,
		];
		[$status] = Gateway_Socket_Company::doCall("employeecard.workedhours.doAppendFixedMessageMap", $ar_post, $user_id);
		if ($status != "ok") {

			// здесь обязательно что-то будет, но не сегодня
			throw new ParseFatalException(__METHOD__ . ": unexpected status");
		}
	}

	/**
	 * попытаться удалить объект с зафиксированным временем
	 *
	 * @throws \parseException
	 */
	public static function tryDelete(int $user_id, int $worked_hours_id, array $message_map_list):void {

		$ar_post = [
			"worked_hours_id"  => $worked_hours_id,
			"message_map_list" => $message_map_list,
		];
		[$status] = Gateway_Socket_Company::doCall("employeecard.workedhours.tryDelete", $ar_post, $user_id);
		if ($status != "ok") {

			// здесь обязательно что-то будет, но не сегодня
			throw new ParseFatalException(__METHOD__ . ": unexpected status");
		}
	}
}