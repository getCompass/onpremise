<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Сценарии участников компании для API
 *
 * Class Domain_Member_Scenario_ApiV2
 */
class Domain_Member_Scenario_Apiv2 {

	/**
	 * Получить список участников компании
	 *
	 * @param int    $user_id
	 * @param int    $role
	 * @param int    $permissions
	 * @param string $query
	 * @param int    $limit
	 * @param int    $offset
	 * @param array  $filter_npc_type
	 * @param array  $filter_role
	 * @param string $sort_field
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\UserIsGuest
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \CompassApp\Domain\Member\Exception\IsNotAdministrator
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 */
	public static function getList(int   $user_id, int $role, int $permissions, string $query, int $limit, int $offset, array $filter_npc_type,
						 array $filter_role = [], array $filter_query_field = [], string $sort_field = ""):array {

		Member::assertUserNotGuest($role);

		// проверяем параметры
		[$sort_fields, $filter_npc_type, $filter_role, $query] = Domain_Member_Action_GetListByQuery::prepareParams(
			$role, $limit, $offset, $sort_field, $filter_npc_type, $filter_role, $filter_query_field, $query
		);

		// если получаем ботов или удалённых, то проверяем права на админа
		if ((in_array(Type_User_Main::getUserbotNpcType(), $filter_npc_type) || in_array(Member::ROLE_USERBOT, $filter_role))) {

			Member::assertUserAdministrator($role);
		} elseif (in_array(Member::ROLE_LEFT, $filter_role)) {

			Permission::assertCanKickMember($role, $permissions);
		} else {

			Domain_Member_Entity_Permission::checkSpace($user_id, METHOD_VERSION_2, Permission::IS_SHOW_COMPANY_MEMBER_ENABLED);
		}

		// получаем список участников
		return Domain_Member_Action_GetListByQuery::do($query, $limit, $offset, $filter_npc_type, $filter_role, $filter_query_field, $sort_fields);
	}
}
