<?php

namespace Compass\Company;

/**
 * Класс для управление рейтингом участников
 */
class Apiv2_Member_Rating extends \BaseFrame\Controller\Api {

	// доступные методы контроллера
	public const ALLOW_METHODS = [
		"getScreenTimeStat",
	];

	// методы, которые считаем за активность
	public const MEMBER_ACTIVITY_METHOD_LIST = [];

	// методы, требующие премиум доступа
	public const ALLOW_WITH_PREMIUM_ONLY_METHODS = [];

	/**
	 * Получить статистику экранного времени участника
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CaseException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getScreenTimeStat():array {

		$member_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			$day_list = Domain_Member_Scenario_Api::getScreenTimeStat($member_id);
		} catch (\cs_RowIsEmpty|\CompassApp\Domain\Member\Exception\AccountDeleted|\CompassApp\Domain\Member\Exception\IsLeft) {
			throw new \BaseFrame\Exception\Request\CaseException(2209006, "member not found");
		} catch (Domain_Member_Exception_StatisticIsInfinite) {
			throw new \BaseFrame\Exception\Request\CaseException(2209010, "member activity is hidden");
		}

		return $this->ok([
			"day_list" => (object) $day_list,
		]);
	}
}