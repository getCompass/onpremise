<?php

namespace Compass\Speaker;

/**
 * класс для работы с автоматическими прозвонами
 */
class Type_Call_Tester {

	// добавляем задачу на проведение тестового прозвона между двумя тестовыми пользователями
	public static function addTask(int $initiator_user_id, int $opponent_user_id):int {

		$extra = Gateway_Db_CompanyCall_CallTesterQueue::initExtra($initiator_user_id, $opponent_user_id);
		return Gateway_Db_CompanyCall_CallTesterQueue::insert($extra);
	}

	// получаем идентификатор тестового пользователя для прозвона по платформе
	public static function getUserIdByPlatform(string $platform):int {

		// конфиг со всеми идентификаторами тестовых пользователей
		$tester_user_list = getConfig("TESTER_USER_LIST");

		$passed_platform_user_list = [];
		foreach ($tester_user_list as $v) {

			if ($v["platform"] == $platform) {
				$passed_platform_user_list[] = $v;
			}
		}

		// если оказалось пусто, то выбрасываем exception
		if (count($passed_platform_user_list) == 0) {
			throw new \parseException(__METHOD__ . ": not found test user for passed platform");
		}

		// выбираем случайного тестового пользователя
		return array_rand(array_values($passed_platform_user_list));
	}
}
