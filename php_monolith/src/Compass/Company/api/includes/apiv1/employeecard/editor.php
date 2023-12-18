<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Класс для работы с редакторами сотрудника
 */
class Apiv1_EmployeeCard_Editor extends \BaseFrame\Controller\Api {

	protected const _EDITOR_LIST_LIMIT = 30; // лимит для списка редакторов

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getList",
		"addBatching",
		"remove",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"add",
		"addBatching",
		"remove",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	// -------------------------------------------------------
	// ОБЩИЕ МЕТОДЫ
	// -------------------------------------------------------

	/**
	 * Получаем список редакторов
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getList():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		// проверяем переданный id на корректность
		if ($user_id < 1) {
			throw new ParamException("incorrect param user_id");
		}

		// пробуем получить информацию о пользователе, проверяем, что тот существует
		$this->_tryGetUserInfo($user_id);

		// получаем администрацию компании
		// получаем список всех пользователей, кто может редактировать карточку
		$user_role_list = Domain_User_Action_Member_GetByPermissions::do([
			\CompassApp\Domain\Member\Entity\Permission::MEMBER_PROFILE_EDIT,
		]);

		$admin_id_list = array_keys($user_role_list);

		// получаем список id редакторов пользователя
		$editor_id_list = Type_User_Card_EditorList::getAllUserEditorIdList($user_id);

		// собираем в единый список всех, кто может редактировать карточку
		$all_editor_id_list   = array_unique(array_merge($admin_id_list, $editor_id_list));
		$all_editor_info_list = Gateway_Bus_CompanyCache::getShortMemberList($all_editor_id_list);

		// приводим информацию о пользователях к формату для клиентов и отдаем ответ
		$user_list = $this->_formattedUserList($all_editor_info_list, $admin_id_list, $editor_id_list);

		// собираем id пользователей чтобы отдать их в action
		$this->action->users(array_keys($all_editor_info_list));

		return $this->ok([
			"admin_list"  => (array) $user_list["admin_user_list"],
			"editor_list" => (array) $user_list["editor_user_list"],
		]);
	}

	/**
	 * Добавляем несколько редакторов пользователю
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public function addBatching():array {

		$add_editor_user_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "editor_user_id_list");
		$user_id                 = $this->post(\Formatter::TYPE_INT, "user_id");

		$this->_throwIfIncorrectParams($user_id, $add_editor_user_id_list);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::EDITOR_ADD_BATCHING);

		// проверяем, что все присланные пользователи существуют
		$all_user_id_list   = $add_editor_user_id_list;
		$all_user_id_list[] = $user_id;
		$all_user_id_list   = array_unique($all_user_id_list);
		$user_info_list     = Gateway_Bus_CompanyCache::getMemberList($all_user_id_list);
		if (count($user_info_list) < count($all_user_id_list)) {
			throw new ParamException("not found info about one of the users");
		}

		// проверяем, что пользователь, для которого добавляем редакторов, и новые редакторы не удалены в системе
		$disabled_user_list = Member::getDisabledUsers($user_info_list, true);
		if (count($disabled_user_list) > 0) {

			$disabled_user_id_list = array_keys($disabled_user_list);

			return $this->error(532, "users are disabled", [
				"disabled_user_id_list" => (array) $disabled_user_id_list,
				"ok_list"               => (array) array_values(array_diff($add_editor_user_id_list, $disabled_user_id_list)),
				"error_list"            => (array) $disabled_user_id_list,
			]);
		}

		// ищем удаленных пользователей
		$account_deleted_user_id_list = [];
		foreach ($user_info_list as $member) {

			if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
				$account_deleted_user_id_list[] = $member->user_id;
			}
		}

		// если аккаунт был удален
		if (count($account_deleted_user_id_list) > 0) {

			return $this->error(2106001, "User delete his account", [
				"account_deleted_user_id_list" => (array) $account_deleted_user_id_list,
				"ok_list"                      => (array) array_values(array_diff($add_editor_user_id_list, $account_deleted_user_id_list)),
				"error_list"                   => (array) $account_deleted_user_id_list,
			]);
		}

		// убираем из списка редакторов тех, кто уже может редактировать карточку по правам
		$user_role_list = Domain_User_Action_Member_GetByPermissions::do
		([\CompassApp\Domain\Member\Entity\Permission::MEMBER_PROFILE_EDIT]);

		$admin_id_list           = array_map(function(\CompassApp\Domain\Member\Struct\Main $member) {

			return $member->user_id;
		}, $user_role_list);
		$add_editor_user_id_list = array_diff($add_editor_user_id_list, $admin_id_list);

		// получаем список id редакторов пользователя
		$editor_id_list = Type_User_Card_EditorList::getAllUserEditorIdList($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editor_id_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// убираем из списка тех, кто уже является руководителем
		$add_editor_user_id_list = array_diff($add_editor_user_id_list, $editor_id_list);

		// добавляем новых редакторов пользователю
		return $this->_addEditorList($user_id, $add_editor_user_id_list);
	}

	/**
	 * Выдаем exception если пришли некорректные параметры
	 *
	 * @param int   $user_id
	 * @param array $editor_id_list
	 *
	 * @throws ParamException
	 */
	protected function _throwIfIncorrectParams(int $user_id, array $editor_id_list):void {

		// получаем количество редакторов для добавления
		$editor_id_list_count = count($editor_id_list);

		// проверяем переданные id пользователей
		if ($user_id < 1 || $editor_id_list_count < 1 || $editor_id_list_count > self::_EDITOR_LIST_LIMIT) {
			throw new ParamException("incorrect param user_id or/and editor_user_id_list");
		}

		// в массиве имеются повторяющиеся значения?
		if ($editor_id_list_count != count(array_unique($editor_id_list))) {
			throw new ParamException("incorrect param editor_user_id_list");
		}
	}

	/**
	 * Добавляем редакторов в карточку сотруднику
	 *
	 * @param int   $user_id
	 * @param array $add_editor_user_id_list
	 *
	 * @return array
	 * @throws \queryException
	 */
	protected function _addEditorList(int $user_id, array $add_editor_user_id_list):array {

		if (count($add_editor_user_id_list) == 0) {
			return $this->ok();
		}

		// добавляем новых редакторов
		foreach ($add_editor_user_id_list as $v) {
			Type_User_Card_EditorList::add($user_id, $v);
		}

		return $this->ok();
	}

	/**
	 * Удаляем редактора у пользователя
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function remove():array {

		$editor_user_id = $this->post(\Formatter::TYPE_INT, "editor_user_id");
		$user_id        = $this->post(\Formatter::TYPE_INT, "user_id");

		// проверяем переданные id пользователей
		if ($user_id < 1 || $editor_user_id < 1) {
			throw new ParamException("incorrect param user_id/editor_user_id");
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::EDITOR_REMOVE);

		// проверяем, что такие пользователи существуют
		$user_id_list   = $user_id == $editor_user_id ? [$editor_user_id] : [$user_id, $editor_user_id];
		$user_info_list = Gateway_Bus_CompanyCache::getMemberList($user_id_list);
		if (count($user_info_list) < count($user_id_list)) {
			throw new ParamException("not found info about one of the users");
		}

		// если пользователь, у которого убираем редактора, уволен - то отдаем ошибку
		foreach ($user_info_list as $v) {

			if ($v->user_id == $user_id && Member::isDisabledProfile($v->role)) {
				return $this->error(532, "user is disabled");
			}

			if ($v->user_id == $user_id && \CompassApp\Domain\Member\Entity\Extra::getIsDeleted($v->extra)) {
				return $this->error(2106001, "User delete his account");
			}
		}

		// получаем список id редакторов пользователя
		$editor_id_list = Type_User_Card_EditorList::getAllUserEditorIdList($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editor_id_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		try {

			Domain_EmployeeCard_Action_Editor_Remove::do($editor_id_list, $user_id, $editor_user_id);
		} catch (cs_AdministrationNotIsDeletingEditor) {
			return $this->error(929, "you can't do action on administration");
		}

		return $this->ok();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Пробуем получить информацию о пользователе
	 *
	 * @param int $user_id
	 *
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _tryGetUserInfo(int $user_id):void {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		if (!isset($user_info_list[$user_id])) {
			throw new ParamException("dont found user in company cache");
		}
	}

	/**
	 * Формируем список пользователей для frontend
	 *
	 * @param \CompassApp\Domain\Member\Struct\Short[] $user_list_info
	 * @param array                                   $admin_id_list
	 * @param array                                   $editor_id_list
	 *
	 * @return array
	 */
	#[ArrayShape([
		"admin_user_list"  => "array",
		"editor_user_list" => "array",
	])]
	protected function _formattedUserList(array $user_list_info, array $admin_id_list, array $editor_id_list):array {

		// проходимся по каждому пользователю
		$formatted_admin_list  = [];
		$formatted_editor_list = [];

		foreach ($user_list_info as $item) {

			// если пользователь уволен, то не может отдаваться в списке редакторов
			if (Member::isDisabledProfile($item->role)) {
				continue;
			}

			if (in_array($item->user_id, $admin_id_list)) {

				$formatted_admin_list[] = (int) $item->user_id;
				continue;
			}

			if (in_array($item->user_id, $editor_id_list)) {
				$formatted_editor_list[] = (int) $item->user_id;
			}
		}

		return [
			"admin_user_list"  => $formatted_admin_list,
			"editor_user_list" => $formatted_editor_list,
		];
	}
}