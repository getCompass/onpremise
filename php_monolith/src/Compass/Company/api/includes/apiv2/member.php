<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\CaseException;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\IsNotAdministrator;
use CompassApp\Domain\Member\Exception\UserIsGuest;

/**
 * Контроллер для работы с пользователями.
 */
class Apiv2_Member extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getList",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [];

	// методы, требующие премиум доступа
	public const ALLOW_WITH_PREMIUM_ONLY_METHODS = [];

	/**
	 * Получить список участников по запросу
	 *
	 * @return array
	 * @throws \ParamException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws ParamException
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 */
	public function getList():array {

		$query              = $this->post(\Formatter::TYPE_STRING, "query", "");
		$offset             = $this->post(\Formatter::TYPE_INT, "offset", 0);
		$limit              = $this->post(\Formatter::TYPE_INT, "limit", 100);
		$filter_npc_type    = $this->post(\Formatter::TYPE_ARRAY, "filter_npc_type", []);
		$filter_role        = $this->post(\Formatter::TYPE_ARRAY, "filter_role", []);
		$filter_query_field = $this->post(\Formatter::TYPE_ARRAY, "filter_query_field", []);
		$sort_field         = $this->post(\Formatter::TYPE_STRING, "sort_field", "");

		try {

			$member_id_list = Domain_Member_Scenario_Apiv2::getList(
				$this->user_id, $this->role, $this->permissions, $query, $limit + 1, $offset, $filter_npc_type, $filter_role, $filter_query_field, $sort_field
			);
		} catch (IsNotAdministrator | \CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (UserIsGuest) {

			// для гостя отдаем пустой ответ
			return $this->ok([
				"member_list" => (array) [],
				"has_next"    => (int) 0,
			]);
		}

		$this->action->users($member_id_list);

		return $this->ok([
			"member_list" => (array) array_slice($member_id_list, 0, $limit),
			"has_next"    => (int) (count($member_id_list) > $limit),
		]);
	}
}