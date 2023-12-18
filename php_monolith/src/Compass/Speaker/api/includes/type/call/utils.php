<?php

namespace Compass\Speaker;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * вспомогательные функции для звонков
 */
class Type_Call_Utils {

	// подготавливает строку из meta к передаче в Apiv1_Format
	public static function prepareCallForFormat(array $meta_row, int $user_id):array {

		$output = self::_getCallOutput($meta_row, $user_id);

		// выставляем тип звонка
		$output["type"] = self::_getTypeTitle($meta_row["type"]);

		// получаем статус звонка
		$output["status"] = self::_getStatus($meta_row, $user_id);

		// дополняем поле data в зависимости от статуса звонка
		$output["data"] = self::_getData($meta_row, $user_id);

		return $output;
	}

	// получаем стандартный вывод ответа
	#[ArrayShape(["call_map" => "mixed", "type" => "mixed", "creator_user_id" => "mixed", "started_at" => "int", "finished_at" => "int", "member_list" => "array", "users" => "array", "data" => "array"])]
	protected static function _getCallOutput(array $meta_row, int $user_id):array {

		// в зависимости от типа звонка
		$started_at  = Type_Call_Users::getStartedAt($meta_row["users"][$user_id]);
		$finished_at = Type_Call_Users::getFinishedAt($meta_row["users"][$user_id]);

		// получаем содержимое для поля member_list
		$member_list = self::_getMemberList($user_id, $meta_row);

		return [
			"call_map"        => $meta_row["call_map"],
			"type"            => $meta_row["type"],
			"creator_user_id" => $meta_row["creator_user_id"],
			"started_at"      => $started_at,
			"finished_at"     => $finished_at,
			"member_list"     => array_values($member_list),
			"users"           => array_keys($member_list),
			"data"            => [],
		];
	}

	// функция формирует содержимое поля member_list
	protected static function _getMemberList(int $user_id, array $meta_row):array {

		// если пользователь кикнут, то возвращаем пустоту
		if (Type_Call_Users::getRole($meta_row["users"][$user_id]) == Type_Call_Users::ROLE_LEAVED) {
			return [];
		}

		// получаем member_list и сортируем по joined_at
		$member_list = Type_Call_Users::getMemberList($meta_row["users"]);
		uasort($member_list, function(array $a, array $b) {

			return $a["joined_at"] <=> $b["joined_at"];
		});

		$output = [];
		foreach ($member_list as $k => $v) {

			$status     = Type_Call_Users::getStatus($v);
			$output[$k] = [
				"user_id" => $k,
				"status"  => self::getStatusTitle($status, $meta_row["is_finished"] ? true : false),
			];
		}

		return $output;
	}

	// функция выставляет корректный тип звонка
	protected static function _getTypeTitle(int $type):string {

		switch ($type) {

			case CALL_TYPE_SINGLE:
				return "single";

			case CALL_TYPE_GROUP:
				return "group";

			default:
				throw new \parseException("Unknown type of the call");
		}
	}

	// функция возвращает статус звонка для конкретного пользователя
	protected static function _getStatus(array $meta_row, int $user_id):string {

		// если пользователя кикнули
		$role = Type_Call_Users::getRole($meta_row["users"][$user_id]);
		if ($role == Type_Call_Users::ROLE_LEAVED) {
			return "finished";
		}

		$status = Type_Call_Users::getStatus($meta_row["users"][$user_id]);
		return self::getStatusTitle($status, $meta_row["is_finished"] ? true : false);
	}

	// функция выставляет корректный статус звонка
	public static function getStatusTitle(int $status, bool $is_finished):string {

		if ($status == CALL_STATUS_HANGUP && $is_finished) {
			return "finished";
		}

		switch ($status) {

			case CALL_STATUS_DIALING:
				return "dialing";

			case CALL_STATUS_ESTABLISHING:
				return "establishing";

			case CALL_STATUS_SPEAKING:
				return "speak";

			case CALL_STATUS_HANGUP:
				return "hangup";

			default:
				throw new \parseException("Unknown status of the call");
		}
	}

	// функция формирует структуру data сущности call
	// @long
	protected static function _getData(array $meta_row, int $user_id):array {

		$output = [];

		// в зависимости от статуса
		$status = Type_Call_Users::getStatus($meta_row["users"][$user_id]);
		switch ($status) {

			case CALL_STATUS_ESTABLISHING:
			case CALL_STATUS_DIALING:
				break;

			case CALL_STATUS_SPEAKING:

				$output["is_lost_connection"] = Type_Call_Users::isLostConnection($user_id, $meta_row["users"]) ? 1 : 0;
				break;

			case CALL_STATUS_HANGUP:

				$output = self::_getDataForFinished($user_id, $meta_row);
				break;

			default:
				throw new \parseException("Unknown status of the call");
		}

		$conversation_map = Type_Call_Users::getConversationMap($user_id, $meta_row["users"]);
		if (mb_strlen($conversation_map) > 0) {
			$output["conversation_map"] = $conversation_map;
		}

		$output["report_call_id"]   = Gateway_Db_CompanyCall_CallMeta::getReportCallId($meta_row["extra"]);
		$output["opponent_user_id"] = self::_getOpponentUserId($user_id, $meta_row["type"], $meta_row["users"]);

		return $output;
	}

	// функция дополнит поле data информацией для завершенных звонков
	// @long
	protected static function _getDataForFinished(int $user_id, array $meta_row):array {

		$output = [];

		// проходимся по причине завершения звонка
		$finish_reason = Type_Call_Users::getFinishReason($meta_row["users"][$user_id]);
		switch ($finish_reason) {

			case CALL_FINISH_REASON_NONE:

				$output["finished_reason"]   = "undefined";
				$output["hangup_by_user_id"] = $meta_row["extra"]["hangup_by_user_id"];
				break;

			case CALL_FINISH_REASON_LINE_IS_BUSY:

				$output["finished_reason"]   = "line_is_busy";
				$output["hangup_by_user_id"] = $meta_row["extra"]["hangup_by_user_id"];
				break;

			case CALL_FINISH_REASON_IGNORED:

				$output["finished_reason"]   = "ignored";
				$output["hangup_by_user_id"] = $meta_row["extra"]["hangup_by_user_id"];
				break;

			case CALL_FINISH_REASON_LOSE_CONNECTION:

				$output["finished_reason"]   = "lose_connection";
				$output["hangup_by_user_id"] = $meta_row["extra"]["hangup_by_user_id"];
				break;

			case CALL_FINISH_REASON_HANGUP:

				$output["finished_reason"]   = "hangup";
				$output["hangup_by_user_id"] = $meta_row["extra"]["hangup_by_user_id"];
				break;

			case CALL_FINISH_REASON_CANCELED:

				$output["finished_reason"]   = "canceled";
				$output["hangup_by_user_id"] = $meta_row["extra"]["hangup_by_user_id"];
				break;

			default:
				throw new \parseException("Unknown finish_reason of the call");
		}

		return $output;
	}

	// получаем user_id собеседника в звонке (как для single, так и для group звонков)
	protected static function _getOpponentUserId(int $user_id, int $type, array $users):int {

		if ($type == CALL_TYPE_SINGLE) {
			return Type_Call_Users::getOpponentFromSingleCall($user_id, $users);
		}

		// если есть пригласитель
		$invited_by_user_id = Type_Call_Users::getInvitedByUserId($users[$user_id]);
		if ($invited_by_user_id > 0) {
			return $invited_by_user_id;
		}

		// иначе сортируем по started_at типов, которых мы пригласили и отдаем самого раннего
		$member_list           = Type_Call_Users::getMemberList($users);
		$early_invited_user_id = self::_getEarlyInvitedUserId($user_id, $member_list);
		if ($early_invited_user_id > 0) {
			return $early_invited_user_id;
		}

		// иначе отдаем рандомного, кроме себя
		unset($member_list[$user_id]);
		$temp                    = array_keys($member_list);
		$random_opponent_user_id = array_pop($temp);
		return is_null($random_opponent_user_id) ? 0 : $random_opponent_user_id;
	}

	// получаем самого раннего приглашенного нами пользователя
	protected static function _getEarlyInvitedUserId(int $user_id, array $member_list):int {

		foreach ($member_list as $k => $v) {

			if (Type_Call_Users::getInvitedByUserId($v) != $user_id) {
				unset($member_list[$k]);
			}
		}
		uasort($member_list, function(array $a, array $b) {

			return $a["started_at"] <=> $b["started_at"];
		});

		if (count($member_list) < 1) {
			return 0;
		}

		$temp = array_keys($member_list);
		return array_pop($temp);
	}

	// генерируем рандомный call_id
	#[Pure]
	public static function generateCallId():int {

		return rand(100000, 999999);
	}

	// содержание для voip пуша
	public static function getVoIPPushBody(int $status, array $node_list = [], int $user_id = 0):array {

		switch ($status) {

			case CALL_STATUS_DIALING:

				$push_body["action"]       = "incoming";
				$push_body["node_list"]    = $node_list;
				$push_body["time_to_live"] = Gateway_Db_CompanyCall_CallMonitoringDialing::DIALING_TIMEOUT;
				$push_body["user_id"]      = $user_id;
				break;

			case CALL_STATUS_HANGUP:

				$push_body["action"] = "finished";
				break;
			default:

				throw new \paramException("unknown status of call");
		}

		return $push_body;
	}

	// title для voip пуша
	public static function getVoIPPushTitle(string $full_name):string {

		return $full_name;
	}

	// обновить need_work для задачи крона Cron_Call_Dialing
	public static function setDialingTaskNeedWork(int $user_id, string $call_map, int $need_work):void {

		Gateway_Db_CompanyCall_CallMonitoringDialing::set($user_id, $call_map, [
			"need_work" => $need_work,
		]);
	}

	// обновить need_work для задачи крона Cron_Call_MonitoringEstablishingConnect
	public static function setMonitoringEstablishingConnectTaskNeedWork(string $call_map, int $user_id, int $need_work):void {

		Gateway_Db_CompanyCall_CallMonitoringEstablishingConnect::set($call_map, $user_id, [
			"need_work" => $need_work,
		]);
	}

	// нужно ли кикнуть пользователя, если он отклоняет или игнорирует первый входящий звонок
	public static function isNeedKickUserIfHeDeclineOrIgnoreFirstIncomingCall(int $user_id, int $creator_user_id, int $type, array $users):bool {

		// если это не групповой диалог
		if ($type != CALL_TYPE_GROUP) {
			return false;
		}

		// если статус пользователя не dialing
		$status = Type_Call_Users::getStatus($users[$user_id]);
		if ($status != CALL_STATUS_DIALING) {
			return false;
		}

		// если пользователь ранее принимал звонок
		if (Type_Call_Users::getAcceptedAt($users[$user_id]) > 0) {
			return false;
		}

		// если это создатель
		if ($user_id == $creator_user_id) {
			return false;
		}

		return true;
	}
}
