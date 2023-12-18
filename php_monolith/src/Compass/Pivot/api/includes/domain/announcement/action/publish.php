<?php

namespace Compass\Pivot;

/**
 * Публикация анонсов.
 */
class Domain_Announcement_Action_Publish {

	/**
	 * Публикует новый анонс и возвращает его id.
	 */
	public static function run(array $raw_data, array $receiver_user_id_list, array $excluded_user_id_list):int {

		// добавляем получателей по необходимости
		if (count($receiver_user_id_list) > 0) {
			$raw_data["receiver_user_id_list"] = $receiver_user_id_list;
		}

		// добавляем пользователей, которым анонс не должны быть доставлен
		if (count($excluded_user_id_list) > 0) {
			$raw_data["excluded_user_id_list"] = $excluded_user_id_list;
		}

		try {

			// публикуем анонс
			return Gateway_Announcement_Main::publish($raw_data);
		} catch (\Exception) {

			// никак не зависим от анонсов
			// просто пока игнорируем то, что ничего не отключилось
			return 0;
		}
	}
}
