<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Action для проверки, что звонок не завершился
 */
class Domain_Call_Action_ValidateWorkingCall {

	/**
	 * выполняем
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \cs_DecryptHasFailed
	 * @long
	 */
	public static function do(array $active_user_call_list):void {

		$is_exist_not_finished_call = false; // флаг 1/0 наличия незавершённого звонка
		$is_janus_call_room_loss    = false; // флаг 1/0 потери разговорной комнаты для активного звонка

		if (count($active_user_call_list) < 1) {
			return;
		}

		$call_map_list = [];
		foreach ($active_user_call_list as $call_key => $_) {
			$call_map_list[] = Type_Pack_Call::doDecrypt($call_key);
		}

		// получаем меты звонков
		$meta_list = Type_Call_Meta::getAll($call_map_list);

		$call_room_list = [];
		foreach ($meta_list as $meta_row) {

			// если в мете в спикере отмечено, что звонок завершён, тогда как звонок активен в пивоте
			if ($meta_row["is_finished"] == 1) {

				// отмечаем флаг наличия незавершённого звонка
				$is_exist_not_finished_call = true;
				Type_System_Admin::log("validate_working_call", ["finished_call", $meta_row["call_map"], COMPANY_ID]);
			}

			$call_room_row = Type_Janus_Room::getByCallMap($meta_row["call_map"]);
			if (isset($call_room_row["room_id"])) {
				$call_room_list[] = Type_Janus_Room::getByCallMap($meta_row["call_map"]);
			}
		}

		foreach ($call_room_list as $call_room_row) {

			// если разговорная комната для активного звонка не найдена в Janus
			if (Helper_Janus::isRoomExists($call_room_row) === false) {

				$is_janus_call_room_loss = true;
				Type_System_Admin::log("validate_working_call", ["room_loss", $call_room_row["call_map"], COMPANY_ID]);
			}
		}

		if ($is_exist_not_finished_call) {

			$message = "Обнаружен незавершённый звонок у пользователя. Проверьте лог-файл в спикере info/validate_working_call.log";
			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, $message);
		} elseif ($is_janus_call_room_loss) {

			$message = "Обнаружен незавершённый звонок, у которого отсутствует разговорная комната. Проверьте лог-файл info/validate_working_call.log";
			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, $message);
		}
	}
}