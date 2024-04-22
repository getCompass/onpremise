<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Класс для работы с очередями мониторинга establishing-соединения
 */
class Domain_Call_Entity_EstablishingMonitoringQueue {

	public const MONITORING_FINISH_REASON = CALL_FINISH_REASON_LOSE_CONNECTION;
	
	/**
	 * Получаем все записи
	 */
	public static function getAll():array {

		$limit  = 100;
		$offset = 0;

		// получаем все записи
		$all_list = [];
		do {

			$temp     = self::getList($limit, $offset);
			$all_list = array_merge($all_list, $temp);

			$offset += $limit;
		} while (count($temp) == $limit);

		return $all_list;
	}

	/**
	 * Получаем список записей
	 */
	public static function getList(int $limit, int $offset):array {

		return Gateway_Db_CompanyCall_CallMonitoringEstablishingConnect::getAll($limit, $offset);
	}

	/**
	 * Удаляем записи для пользователей и определённого звонка
	 */
	public static function deleteForUsers(array $user_id_list, string $call_map):void {

		Gateway_Db_CompanyCall_CallMonitoringEstablishingConnect::deleteForUsers($user_id_list, $call_map);
	}

	/**
	 * Получаем пользователей кому нужно завершить звонок
	 */
	public static function getUsersWhoNeedFinishCall(array $user_id_list, array $call_map_list):array {

		// получаем мету звонков
		$meta_list = Type_Call_Meta::getAll($call_map_list);

		// проверяем, для каких пользователей и звонков нужно завершать звонок, а для каких - уже поздно
		$need_delete_queue_for_call_and_user = [];
		$need_hand_up_call_for_users         = [];
		foreach ($meta_list as $meta_row) {

			$call_map = $meta_row["call_map"];

			foreach ($user_id_list as $user_id) {

				// если пользователь не участник звонка
				if (!Type_Call_Users::isMember($user_id, $meta_row["users"])) {

					$need_delete_queue_for_call_and_user[$call_map][] = $user_id;
					continue;
				}

				// если звонок уже завершен
				$status = Type_Call_Users::getStatus($meta_row["users"][$user_id]);
				if ($status == CALL_STATUS_HANGUP) {

					$need_delete_queue_for_call_and_user[$call_map][] = $user_id;
					continue;
				}

				// если звонок не на стадии установление соединения и пользователь не отмечен как потерявший связь
				if ($status != CALL_STATUS_ESTABLISHING && !Type_Call_Users::isLostConnection($user_id, $meta_row["users"])) {

					$need_delete_queue_for_call_and_user[$call_map][] = $user_id;
					continue;
				}

				$need_hand_up_call_for_users[$call_map][] = $user_id;
			}
		}

		return [$need_hand_up_call_for_users, $need_delete_queue_for_call_and_user];
	}
}