<?php

namespace Compass\Company;

/**
 * Action для проверки, является ли пользователь редактором карточки
 */
class Domain_User_Action_Member_CheckIsEditor {

	/**
	 * выполняем проверку
	 *
	 * @throws \parseException
	 */
	public static function do(int $user_id, int $role, int $permissions, int $employee_card_user_id):bool {

		// получаем запись редакторов пользователя
		$editors_obj = Type_User_Card_EditorList::get($employee_card_user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($user_id, $role, $permissions, $editors_obj->editor_list)) {
			return false;
		}

		return true;
	}
}
