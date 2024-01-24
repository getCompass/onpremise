<?php

namespace Compass\Company;

/**
 * Базовый класс для удаления редакторов пользователю
 */
class Domain_EmployeeCard_Action_Editor_Remove {

	/**
	 * Выполняем действие удаления редакторов сотруднику
	 *
	 * @param array $editor_id_list
	 * @param int   $user_id
	 * @param int   $editor_user_id
	 *
	 * @throws cs_AdministrationNotIsDeletingEditor
	 */
	public static function do(array $editor_id_list, int $user_id, int $editor_user_id):void {

		// проверим что не пытаемся удалить того, кто может приглашать и увольнять (человек с правами редактирования профиля)
		$user_role_list = Domain_User_Action_Member_GetByPermissions::do([
			\CompassApp\Domain\Member\Entity\Permission::MEMBER_PROFILE_EDIT,
		]);

		$admin_id_list = array_keys($user_role_list);

		if (in_array($editor_user_id, $admin_id_list)) {
			throw new cs_AdministrationNotIsDeletingEditor();
		}

		// если редактор отсутствует в списке редакторов
		if (!in_array($editor_user_id, $editor_id_list)) {
			return;
		}

		// убираем выбранного редактора из редакторов пользователя
		Type_User_Card_EditorList::remove($user_id, $editor_user_id);
	}
}
