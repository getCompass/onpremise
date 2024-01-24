<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Действие получения списка пользователей по запросу
 */
class Domain_Member_Action_GetListByQuery {

	protected const _SORT_FIELD_LIST = [
		"joined_at" => "company_joined_at",
		"left_at"   => "left_at",
		"role"      => "role",
	];

	protected const _FILTER_ROLE_LIST = [
		"guest"         => Member::ROLE_GUEST,
		"member"        => Member::ROLE_MEMBER,
		"administrator" => Member::ROLE_ADMINISTRATOR,
		"userbot"       => Member::ROLE_USERBOT,
		"left"          => Member::ROLE_LEFT,
	];

	/**
	 * Получаем список пользователей по запросу
	 */
	public static function do(string $query, int $limit, int $offset, array $filter_npc_type, array $filter_role, array $sort_fields, bool $is_legacy = false):array {

		// для старых версий получаем пользователей старым способом
		if ($is_legacy) {
			return Gateway_Db_CompanyData_MemberList::getListByQueryLegacy($query, $limit, $offset, $filter_npc_type, $filter_role);
		} else {
			return Gateway_Db_CompanyData_MemberList::getListByQuery($query, $limit, $offset, $filter_npc_type, $filter_role, $sort_fields);
		}
	}

	/**
	 * преобразуем параметры для получения списка пользователей
	 *
	 * @param int    $member_role
	 * @param int    $limit
	 * @param int    $offset
	 * @param string $sort_field
	 * @param array  $filter_npc_type
	 * @param array  $filter_role
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public static function prepareParams(int $member_role, int $limit, int $offset, string $sort_field, array $filter_npc_type, array $filter_role):array {

		if ($limit < 1 || $offset < 0) {
			throw new ParamException("invalid limit or offset");
		}

		if (count($filter_npc_type) < 1) {
			$filter_npc_type = [Type_User_Main::NPC_TYPE_HUMAN];
		} else {
			$filter_npc_type = self::_prepareFilterNpcTypeList($filter_npc_type);
		}

		if (count($filter_role) < 1) {

			$filter_role = [Member::ROLE_MEMBER, Member::ROLE_ADMINISTRATOR];

			if (in_array(Type_User_Main::getUserbotNpcType(), $filter_npc_type)) {
				$filter_role[] = Member::ROLE_USERBOT;
			}
		} else {
			$filter_role = self::_prepareFilterRole($filter_role);
		}

		$sort_field = self::_prepareSortFields($member_role, $sort_field);

		return [$sort_field, $filter_npc_type, $filter_role];
	}

	/**
	 * получаем список npc_type для фильтра
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _prepareFilterNpcTypeList(array $filter_client_npc_type):array {

		$filter_npc_type = [];
		foreach ($filter_client_npc_type as $user_type) {
			$filter_npc_type[] = Type_User_Main::getNpcTypeByUserType($user_type);
		}

		return $filter_npc_type;
	}

	/**
	 * получаем список ролей для фильтра
	 *
	 * @throws ParamException
	 */
	protected static function _prepareFilterRole(array $filter_client_role):array {

		$filter_role = [];
		foreach ($filter_client_role as $role) {

			if (!isset(self::_FILTER_ROLE_LIST[$role])) {
				throw new ParamException("unknown filter_role: {$role}");
			}

			$filter_role[] = self::_FILTER_ROLE_LIST[$role];
		}

		return $filter_role;
	}

	/**
	 * получаем строку для сортировки
	 *
	 * @param int    $member_role
	 * @param string $sort_field
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	protected static function _prepareSortFields(int $member_role, string $sort_field):array {

		$sort_fields = [];

		if ($member_role === Member::ROLE_ADMINISTRATOR && $sort_field !== "left_at") {
			$sort_fields[] = "role";
		}

		$sort_fields[] = mb_strlen($sort_field) < 1 ? "joined_at" : $sort_field;

		$sort_fields = array_map(function(string $sort_field) {

			if (!isset(self::_SORT_FIELD_LIST[$sort_field])) {
				throw new \BaseFrame\Exception\Request\ParamException("unknown sort_field: {$sort_field}");
			}

			return self::_SORT_FIELD_LIST[$sort_field];
		}, $sort_fields);

		return array_unique($sort_fields);
	}
}