<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Сценарии по ссылкам-инвайтам api
 */
class Domain_JoinLink_Scenario_Api {

	/**
	 * Cоздаем ссылку инвайт
	 *
	 * @param int       $user_id
	 * @param int       $user_role
	 * @param int       $user_permissions
	 * @param string    $type
	 * @param int|false $lives_day_count
	 * @param int|false $lives_hour_count
	 * @param int|false $can_use_count
	 * @param bool      $is_postmoderation
	 * @param int|bool  $entry_option
	 * @param int       $method_version
	 *
	 * @return Struct_Db_CompanyData_JoinLink
	 * @throws Domain_JoinLink_Exception_IncorrectEntryOption
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_ExceededCountActiveInvite
	 * @throws cs_IncorrectCanUseCount
	 * @throws cs_IncorrectLivesDayCount
	 * @throws cs_IncorrectLivesHourCount
	 * @throws cs_IncorrectType
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function add(int $user_id, int $user_role, int $user_permissions, string $type, int|false $lives_day_count, int|false $lives_hour_count, int|false $can_use_count, bool $is_postmoderation, int|bool $entry_option, int $method_version = 1):Struct_Db_CompanyData_JoinLink {

		// если не задан параметр entry_option, то конвертируем старый параметр is_postmoderation в новый параметр entry_option, чтобы все было на одном языке
		if ($entry_option === false) {
			$entry_option = Domain_JoinLink_Entity_Main::convertPostModerationFlagToEntryOption($is_postmoderation);
		}
		if ($entry_option !== false) {
			Domain_JoinLink_Entity_Validator::assertEntryOption($entry_option);
		}

		return match ($method_version) {
			METHOD_VERSION_1                   			     => self::_addV1(
				$user_id, $user_role, $user_permissions, $type, $lives_day_count, $can_use_count, $entry_option
			),
			METHOD_VERSION_2, METHOD_VERSION_3, METHOD_VERSION_4 => self::_addV2V3V4(
				$user_id, $user_role, $user_permissions, $type, $lives_day_count, $lives_hour_count, $can_use_count, $entry_option, $method_version
			),
			default                            			     => self::_addV2V3V4(
				$user_id, $user_role, $user_permissions, $type, $lives_day_count, $lives_hour_count, $can_use_count, $entry_option, 3
			),
		};
	}

	/**
	 * создаём ссылку-приглашение (версия без single-ссылок)
	 *
	 * @param int       $user_id
	 * @param int       $user_role
	 * @param int       $user_permissions
	 * @param string    $type
	 * @param int|false $lives_day_count
	 * @param int|false $can_use_count
	 * @param int       $entry_option
	 *
	 * @long
	 * @return Struct_Db_CompanyData_JoinLink
	 * @throws Domain_JoinLink_Exception_IncorrectEntryOption
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_ExceededCountActiveInvite
	 * @throws cs_IncorrectCanUseCount
	 * @throws cs_IncorrectLivesDayCount
	 * @throws cs_IncorrectType
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _addV1(int $user_id, int $user_role, int $user_permissions, string $type, int|false $lives_day_count, int|false $can_use_count, int $entry_option):Struct_Db_CompanyData_JoinLink {

		Domain_JoinLink_Entity_Validator::assertEntryOption($entry_option);

		// проверяем параметры
		$to_type = array_flip(Domain_JoinLink_Entity_Main::TYPE_SCHEMA_LEGACY);
		if (!isset($to_type[$type])) {
			throw new cs_IncorrectType();
		}
		$type = $to_type[$type];

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::CREATE_JOIN_LINK);

		// проверяем что пользователь имеет права на создание ссылок
		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		// проверяем блокировки в зависимости от типа инвайта и вызываем действие
		switch ($type) {

			case Domain_JoinLink_Entity_Main::TYPE_REGULAR:

				if ($lives_day_count < 1 || $lives_day_count > Domain_JoinLink_Entity_Validator::METHOD_VERSION_PARAMS[METHOD_VERSION_1]["lives_day_count"]) {
					throw new cs_IncorrectLivesDayCount();
				}
				if ($can_use_count < 1 || $can_use_count > Domain_JoinLink_Entity_Validator::METHOD_VERSION_PARAMS[METHOD_VERSION_1]["can_use_count"]) {
					throw new cs_IncorrectCanUseCount();
				}

				$join_link = Domain_JoinLink_Action_Create_Regular::do($user_id, $lives_day_count, false, $can_use_count, $entry_option);
				break;
			case Domain_JoinLink_Entity_Main::TYPE_MAIN:

				$join_link = Domain_JoinLink_Action_Create_Main::do(
					$user_id,
					Domain_JoinLink_Entity_Main::DEFAULT_MAIN_LIFE_DAY_COUNT_LEGACY,
					Domain_JoinLink_Entity_Validator::METHOD_VERSION_PARAMS[METHOD_VERSION_1]["can_use_count"]
				);
				break;
			default:
				throw new ParseFatalException("unknown link type");
		}

		return $join_link;
	}

	/**
	 * создаём ссылку-приглашение (версия с single-ссылками)
	 *
	 * @param int       $user_id
	 * @param int       $user_role
	 * @param int       $user_permissions
	 * @param string    $type
	 * @param int|false $lives_day_count
	 * @param int|false $lives_hour_count
	 * @param int|false $can_use_count
	 * @param int       $entry_option
	 * @param int       $method_version
	 *
	 * @long
	 * @return Struct_Db_CompanyData_JoinLink
	 * @throws Domain_JoinLink_Exception_IncorrectEntryOption
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_ExceededCountActiveInvite
	 * @throws cs_IncorrectCanUseCount
	 * @throws cs_IncorrectLivesDayCount
	 * @throws cs_IncorrectLivesHourCount
	 * @throws cs_IncorrectType
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _addV2V3V4(int $user_id, int $user_role, int $user_permissions, string $type, int|false $lives_day_count, int|false $lives_hour_count, int|false $can_use_count, int $entry_option, int $method_version):Struct_Db_CompanyData_JoinLink {

		// проверяем параметры
		$type = Domain_JoinLink_Entity_Main::convertStringToIntType($type);

		// проверяем, что такую ссылку можно создать
		Domain_JoinLink_Entity_Permission::assertAllowedTypeForCreate($type);

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::CREATE_JOIN_LINK);

		// проверяем, что пользователь имеет права на создание ссылок
		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		// создаём ссылку в зависимости от переданного типа
		switch ($type) {

			case Domain_JoinLink_Entity_Main::TYPE_REGULAR:

				Domain_JoinLink_Entity_Validator::assertValidLivesDayCount($lives_day_count, $method_version);
				Domain_JoinLink_Entity_Validator::assertValidLivesHourCount($lives_hour_count, $method_version);
				Domain_JoinLink_Entity_Validator::assertValidCanUseCount($can_use_count, $method_version);

				$join_link = Domain_JoinLink_Action_Create_Regular::do($user_id, $lives_day_count, $lives_hour_count, $can_use_count, $entry_option);
				break;

			case Domain_JoinLink_Entity_Main::TYPE_MAIN:

				$join_link = Domain_JoinLink_Action_Create_Main::do($user_id);
				break;

			case Domain_JoinLink_Entity_Main::TYPE_SINGLE:

				Domain_JoinLink_Entity_Validator::assertValidLivesDayCount($lives_day_count, $method_version);
				Domain_JoinLink_Entity_Validator::assertValidLivesHourCount($lives_hour_count, $method_version);
				$join_link = Domain_JoinLink_Action_Create_Single::do($user_id, $lives_day_count, $lives_hour_count, $method_version, $entry_option);
				break;

			default:
				throw new ParseFatalException("unknown link type");
		}

		return $join_link;
	}

	/**
	 * редактируем ссылку инвайт
	 *
	 * @param int       $user_id
	 * @param int       $user_role
	 * @param int       $user_permissions
	 * @param string    $join_link_uniq
	 * @param int|false $lives_day_count
	 * @param int|false $lives_hour_count
	 * @param int|false $can_use_count
	 * @param bool|null $is_postmoderation
	 * @param int|null  $entry_option
	 * @param int       $method_version
	 *
	 * @return array
	 * @throws Domain_JoinLink_Exception_IncorrectEntryOption
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_IncorrectCanUseCount
	 * @throws cs_IncorrectLivesDayCount
	 * @throws cs_IncorrectLivesHourCount
	 * @throws cs_IncorrectType
	 * @throws cs_InvalidParamForEditInvite
	 * @throws cs_InvalidStatusForEditInvite
	 * @throws cs_JoinLinkDeleted
	 * @throws cs_JoinLinkNotExist
	 * @throws \parseException
	 */
	public static function edit(int $user_id, int $user_role, int $user_permissions, string $join_link_uniq, int|false $lives_day_count, int|false $lives_hour_count, int|false $can_use_count, bool|null $is_postmoderation, null|int $entry_option, int $method_version = 1):array {

		// если передан старый параметр is_postmoderation, то конвертируем его в новый параметр entry_option, чтобы все было на одном языке
		if (!is_null($is_postmoderation)) {
			$entry_option = Domain_JoinLink_Entity_Main::convertPostModerationFlagToEntryOption($is_postmoderation);
		}

		// если есть параметр entry_option
		if (!is_null($entry_option)) {
			Domain_JoinLink_Entity_Validator::assertEntryOption($entry_option);
		}

		return match ($method_version) {
			METHOD_VERSION_1                   			     => self::_editV1(
				$user_id, $user_role, $user_permissions, $join_link_uniq, $lives_day_count, $can_use_count, $entry_option
			),
			METHOD_VERSION_2, METHOD_VERSION_3, METHOD_VERSION_4 => self::_editV2V3V4(
				$user_id, $user_role, $user_permissions, $join_link_uniq, $lives_day_count, $lives_hour_count, $can_use_count, $entry_option, $method_version
			),
		};
	}

	/**
	 * редактируем ссылку-приглашение
	 *
	 * @param int       $user_id
	 * @param int       $user_role
	 * @param int       $user_permissions
	 * @param string    $join_link_uniq
	 * @param int|false $lives_day_count
	 * @param int|false $can_use_count
	 * @param bool|null $is_postmoderation
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_IncorrectCanUseCount
	 * @throws cs_IncorrectLivesDayCount
	 * @throws cs_InvalidParamForEditInvite
	 * @throws cs_InvalidStatusForEditInvite
	 * @throws cs_JoinLinkDeleted
	 * @throws cs_JoinLinkNotExist
	 * @throws \parseException
	 */
	protected static function _editV1(int $user_id, int $user_role, int $user_permissions, string $join_link_uniq, int|false $lives_day_count, int|false $can_use_count, int|null $entry_option):array {

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::EDIT_JOIN_LINK);

		// проверяем, что пользователь имеет права на редактирование ссылок
		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		if ($lives_day_count !== false) {

			if ($lives_day_count < 1 || $lives_day_count > Domain_JoinLink_Entity_Validator::METHOD_VERSION_PARAMS[METHOD_VERSION_1]["lives_day_count"]) {
				throw new cs_IncorrectLivesDayCount();
			}
		}
		if ($can_use_count !== false) {

			if ($can_use_count < 1 || $can_use_count > Domain_JoinLink_Entity_Validator::METHOD_VERSION_PARAMS[METHOD_VERSION_1]["can_use_count"]) {
				throw new cs_IncorrectCanUseCount();
			}
		}

		// проверяем, что такую ссылку можно отредактировать
		$join_link = Domain_JoinLink_Action_Get::do($join_link_uniq);
		if ($join_link->type == Domain_JoinLink_Entity_Main::TYPE_SINGLE) {
			throw new cs_InvalidParamForEditInvite();
		}

		Domain_JoinLink_Entity_Main::assertLinkIsNotDeleted($join_link);
		Domain_JoinLink_Entity_Main::assertLinkCanBeEdited($join_link);

		if ($join_link->status != Domain_JoinLink_Entity_Main::STATUS_ACTIVE) {
			throw new cs_InvalidStatusForEditInvite();
		}

		// редактируем
		return [Domain_JoinLink_Action_Edit_Mass::do($join_link, $lives_day_count, false, $can_use_count, $entry_option), []];
	}

	/**
	 * редактируем ссылку-приглашение
	 *
	 * @long
	 *
	 * @param int       $user_id
	 * @param int       $user_role
	 * @param int       $user_permissions
	 * @param string    $join_link_uniq
	 * @param int|false $lives_day_count
	 * @param int|false $lives_hour_count
	 * @param int|false $can_use_count
	 * @param bool|null $is_postmoderation
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_IncorrectCanUseCount
	 * @throws cs_IncorrectLivesDayCount
	 * @throws cs_IncorrectLivesHourCount
	 * @throws cs_IncorrectType
	 * @throws cs_InvalidParamForEditInvite
	 * @throws cs_InvalidStatusForEditInvite
	 * @throws cs_JoinLinkDeleted
	 * @throws cs_JoinLinkNotExist
	 * @throws \parseException
	 */
	protected static function _editV2V3V4(int $user_id, int $user_role, int $user_permissions, string $join_link_uniq, int|false $lives_day_count, int|false $lives_hour_count, int|false $can_use_count, int|null $entry_option, int $method_version):array {

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::EDIT_JOIN_LINK);

		// проверяем, что пользователь имеет права на редактирование ссылок
		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		if ($lives_day_count !== false) {
			Domain_JoinLink_Entity_Validator::assertValidLivesDayCount($lives_day_count, $method_version);
		}
		if ($lives_hour_count !== false) {
			Domain_JoinLink_Entity_Validator::assertValidLivesHourCount($lives_hour_count, $method_version);
		}
		if ($can_use_count !== false) {
			Domain_JoinLink_Entity_Validator::assertValidCanUseCount($can_use_count, $method_version);
		}

		// проверяем, что такую ссылку можно отредактировать
		$join_link = Domain_JoinLink_Action_Get::do($join_link_uniq);
		Domain_JoinLink_Entity_Permission::assertAllowedTypeForEdit($join_link->type);
		if ($join_link->status == Domain_JoinLink_Entity_Main::STATUS_DELETED) {
			throw new cs_JoinLinkDeleted();
		}
		if (!Domain_JoinLink_Entity_Main::isLinkWithoutExpiresLimit($join_link) && $join_link->expires_at < time()) {
			throw new cs_InvalidStatusForEditInvite();
		}
		if ($join_link->status != Domain_JoinLink_Entity_Main::STATUS_ACTIVE) {
			throw new cs_InvalidStatusForEditInvite();
		}

		// редактируем
		$join_link = match ($join_link->type) {

			Domain_JoinLink_Entity_Main::TYPE_REGULAR, Domain_JoinLink_Entity_Main::TYPE_MAIN =>
			Domain_JoinLink_Action_Edit_Mass::do($join_link, $lives_day_count, $lives_hour_count, $can_use_count, $entry_option),

			Domain_JoinLink_Entity_Main::TYPE_SINGLE                                          =>
			Domain_JoinLink_Action_Edit_Single::do($join_link, $lives_day_count, $lives_hour_count, $entry_option),

			default                                                                           => throw new ParseFatalException("unknown link type"),
		};

		// получаем список id пользователей, вступивших по ссылке
		$entry_user_id_list = Domain_JoinLink_Action_GetEntryUserIdList::do($join_link_uniq);

		return [$join_link, $entry_user_id_list];
	}

	/**
	 * удаляем ссылку инвайт
	 *
	 * @param int    $user_id
	 * @param int    $user_role
	 * @param int    $user_permissions
	 * @param string $join_link_uniq
	 *
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_IncorrectJoinLinkUniq
	 * @throws cs_IncorrectType
	 * @throws cs_JoinLinkNotExist
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function delete(int $user_id, int $user_role, int $user_permissions, string $join_link_uniq):void {

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::DELETE_JOIN_LINK);

		// проверяем параметры
		Domain_JoinLink_Entity_Validator::assertJoinLinkUniq($join_link_uniq);

		// проверяем права что такой пользователь может удалять
		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		// проверяем права что такой тип ссылок можно удалять
		$join_link = Domain_JoinLink_Action_Get::do($join_link_uniq);
		Domain_JoinLink_Entity_Permission::assertAllowedTypeForDelete($join_link->type);

		// удаляем ссылку
		Domain_JoinLink_Action_Delete::do($join_link, $user_id);
	}

	/**
	 * получаем активные инвайты
	 *
	 * @param string $type
	 * @param int    $user_id
	 * @param int    $user_role
	 * @param int    $user_permissions
	 * @param int    $method_version
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_IncorrectType
	 */
	public static function getActiveList(string $type, int $user_id, int $user_role, int $user_permissions, int $method_version = 1):array {

		return match ($method_version) {
			METHOD_VERSION_1 => self::_getActiveListV1($user_id, $user_role, $user_permissions),
			METHOD_VERSION_2 => self::_getActiveListV2($type, $user_id, $user_role, $user_permissions),
		};
	}

	/**
	 * получаем активные инвайты
	 *
	 * @param string $type
	 * @param int    $user_id
	 * @param int    $user_role
	 * @param int    $user_permissions
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_IncorrectType
	 */
	protected static function _getActiveListV1(int $user_id, int $user_role, int $user_permissions):array {

		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		// получаем список ссылок-приглашений, фильтруя по запрашиваемому типу
		$type           = Domain_JoinLink_Action_GetFilteredTypeList::MASS_FILTER_TYPE;
		$type_list      = Domain_JoinLink_Action_GetFilteredTypeList::do($type);
		$join_link_list = Domain_JoinLink_Action_GetActiveListByType::do($type_list);

		// сортируем ссылки по времени
		usort($join_link_list, function(Struct_Db_CompanyData_JoinLink $join_link_a, Struct_Db_CompanyData_JoinLink $join_link_b) {

			return $join_link_a->updated_at < $join_link_b->updated_at ? 1 : -1;
		});

		// поднимаем свою собственную main ссылку на самый верх
		$join_link_list = self::_moveUpUserMainLink($join_link_list, $user_id);
		$join_link_list = array_values($join_link_list);

		return [$join_link_list, []];
	}

	/**
	 * получаем активные инвайты
	 *
	 * @param string $type
	 * @param int    $user_id
	 * @param int    $user_role
	 * @param int    $user_permissions
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_IncorrectType
	 */
	protected static function _getActiveListV2(string $type, int $user_id, int $user_role, int $user_permissions):array {

		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		// получаем список ссылок-приглашений, фильтруя по запрашиваемому типу
		$type_list      = Domain_JoinLink_Action_GetFilteredTypeList::do($type);
		$join_link_list = Domain_JoinLink_Action_GetActiveListByType::do($type_list);

		// получаем список id пользователей, вступивших по ссылке
		$entry_user_id_list_by_uniq = [];
		foreach ($join_link_list as $join_link) {
			$entry_user_id_list_by_uniq[$join_link->join_link_uniq] = Domain_JoinLink_Action_GetEntryUserIdList::do($join_link->join_link_uniq);
		}

		// сортируем ссылки по времени
		usort($join_link_list, function(Struct_Db_CompanyData_JoinLink $join_link_a, Struct_Db_CompanyData_JoinLink $join_link_b) {

			return $join_link_a->updated_at < $join_link_b->updated_at ? 1 : -1;
		});

		// поднимаем свою собственную main ссылку на самый верх
		$join_link_list = self::_moveUpUserMainLink($join_link_list, $user_id);
		$join_link_list = array_values($join_link_list);

		return [$join_link_list, $entry_user_id_list_by_uniq];
	}

	/**
	 * Поднимаем свою собственную main ссылку на самый верх
	 */
	protected static function _moveUpUserMainLink(array $join_link_list, int $user_id):array {

		foreach ($join_link_list as $k => $v) {

			if ($v->creator_user_id === $user_id && $v->type === Domain_JoinLink_Entity_Main::TYPE_MAIN) {

				// закидываем себя наверх
				unset($join_link_list[$k]);
				array_unshift($join_link_list, $v);

				break;
			}
		}

		return $join_link_list;
	}

	/**
	 * получаем неактивные инвайты
	 *
	 * @param int $user_role
	 * @param int $user_permissions
	 * @param int $count
	 * @param int $offset
	 * @param int $method_version
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_IncorrectType
	 */
	public static function getInactiveList(int $user_role, int $user_permissions, int $count, int $offset, int $method_version = 1):array {

		return match ($method_version) {
			METHOD_VERSION_1 => self::_getInactiveListV1($user_role, $user_permissions, $count, $offset),
			METHOD_VERSION_2 => self::_getInactiveListV2($user_role, $user_permissions, $count, $offset),
		};
	}

	/**
	 * получаем неактивные инвайты
	 *
	 * @param int $user_role
	 * @param int $user_permissions
	 * @param int $count
	 * @param int $offset
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws cs_IncorrectType
	 */
	protected static function _getInactiveListV1(int $user_role, int $user_permissions, int $count, int $offset):array {

		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		$offset = Domain_JoinLink_Entity_Sanitizer::sanitizeOffset($offset);
		$count  = Domain_JoinLink_Entity_Sanitizer::sanitizeCount($count);

		// получаем список неактивных ссылок
		$type_list      = Domain_JoinLink_Action_GetFilteredTypeList::do(Domain_JoinLink_Action_GetFilteredTypeList::MASS_FILTER_TYPE);
		$join_link_list = Gateway_Db_CompanyData_JoinLinkList::getInactiveListByType(
			$type_list, Domain_JoinLink_Entity_Main::STATUS_ACTIVE, Domain_JoinLink_Entity_Main::STATUS_USED, time(), 0, $count, $offset
		);
		$has_next       = count($join_link_list) == $count ? 1 : 0;

		// сортируем ссылки по времени
		uasort($join_link_list, function(Struct_Db_CompanyData_JoinLink $join_link_a, Struct_Db_CompanyData_JoinLink $join_link_b) {

			return $join_link_a->updated_at < $join_link_b->updated_at ? 1 : -1;
		});
		$join_link_list = array_values($join_link_list);

		return [$join_link_list, $has_next, []];
	}

	/**
	 * получаем неактивные инвайты
	 *
	 * @param int $user_role
	 * @param int $user_permissions
	 * @param int $count
	 * @param int $offset
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 */
	protected static function _getInactiveListV2(int $user_role, int $user_permissions, int $count, int $offset):array {

		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		$offset = Domain_JoinLink_Entity_Sanitizer::sanitizeOffset($offset);
		$count  = Domain_JoinLink_Entity_Sanitizer::sanitizeCount($count);

		// получаем список неактивных ссылок
		$join_link_list = Domain_JoinLink_Action_GetInactiveList::do($count, $offset, true);
		$has_next       = count($join_link_list) == $count ? 1 : 0;

		// получаем список id пользователей, вступивших по ссылке
		$entry_user_id_list_by_uniq = [];
		foreach ($join_link_list as $join_link) {
			$entry_user_id_list_by_uniq[$join_link->join_link_uniq] = Domain_JoinLink_Action_GetEntryUserIdList::do($join_link->join_link_uniq);
		}

		// сортируем ссылки по времени
		uasort($join_link_list, function(Struct_Db_CompanyData_JoinLink $join_link_a, Struct_Db_CompanyData_JoinLink $join_link_b) {

			return $join_link_a->updated_at < $join_link_b->updated_at ? 1 : -1;
		});
		$join_link_list = array_values($join_link_list);

		return [$join_link_list, $has_next, $entry_user_id_list_by_uniq];
	}

	/**
	 * получаем инвайты по типу
	 *
	 * @param int $user_role
	 * @param int $user_permissions
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 */
	public static function getListByType(int $user_role, int $user_permissions):array {

		\CompassApp\Domain\Member\Entity\Permission::assertCanInviteMember($user_role, $user_permissions);

		// получаем список ссылок
		$invite_link_list = Domain_JoinLink_Action_GetListByType::do(50);

		// сортируем ссылки по времени
		uasort($invite_link_list, function(Struct_Db_CompanyData_JoinLink $invite_link_a, Struct_Db_CompanyData_JoinLink $invite_link_b) {

			return $invite_link_a->created_at < $invite_link_b->created_at ? 1 : -1;
		});
		return array_values($invite_link_list);
	}
}

