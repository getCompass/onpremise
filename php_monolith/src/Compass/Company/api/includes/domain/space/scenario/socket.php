<?php

namespace Compass\Company;

/**
 * Сценарии для пространства
 */
class Domain_Space_Scenario_Socket {

	/**
	 * Опубликовать анонс
	 *
	 * @param int   $announcement_type
	 * @param array $data
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function publishAnnouncement(int $announcement_type, array $data):void {

		Domain_Space_Entity_Tariff_Announcement::publish($announcement_type, $data);
	}

	/**
	 * Отключить анонсы
	 *
	 * @return void
	 */
	public static function disableAnnouncements():void {

		Domain_Space_Entity_Tariff_Announcement::disable();
	}

	/**
	 * Проверяем, что пространство разблокировано
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function checkIsUnblocked():bool {

		$plan_info     = [];
		$tariff_config = \CompassApp\Conf\Company::instance()->get("COMPANY_TARIFF");

		if (isset($tariff_config["plan_info"])) {
			$plan_info = $tariff_config["plan_info"];
		}

		$tariff = Domain_SpaceTariff_Tariff::load($plan_info);

		if ($tariff->memberCount()->isRestricted(time())) {
			return false;
		}

		Gateway_Bus_Sender::accessStatusUpdated();

		return true;
	}

	/**
	 * Пытается выполнить переинлексацию пространства.
	 */
	public static function tryReindex():void {

		Gateway_Socket_Conversation::tryReindex();
	}
}