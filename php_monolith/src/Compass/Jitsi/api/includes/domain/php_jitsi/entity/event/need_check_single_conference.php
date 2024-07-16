<?php

namespace Compass\Jitsi;

use BaseFrame\Server\ServerProvider;

/**
 * класс для формирования события о необходимости проверки состояния сингл звонка
 */
class Domain_PhpJitsi_Entity_Event_NeedCheckSingleConference extends Domain_PhpJitsi_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "jitsi.need_check_single_conference";

	// время, через которое нужно выполнить таск проверки
	public const NEED_WORK_INTERVAL = 45;

	/**
	 * Создаём событие
	 *
	 * @throws \busException
	 */
	public static function create(string $conference_id):void {

		$need_work = time() + self::NEED_WORK_INTERVAL;

		// для тестового сервера
		if (ServerProvider::isTest() && getHeader("HTTP_TEST_NEED_WORK")) {
			$need_work = (int) getHeader("HTTP_TEST_NEED_WORK");
		}

		$params = [
			"conference_id" => $conference_id,
		];

		self::_sendToJitsi($params, $need_work);
	}
}