<?php

namespace Compass\Company;

/**
 * Класс для работы с редакторами карточки пользователя
 */
class Type_User_Card_EditorList {

	/**
	 * Получаем запись с редакторами пользователя
	 *
	 * @throws \parseException
	 */
	public static function get(int $user_id, int $role = Type_User_Profile_RelationshipUserList::ROLE_TO_EDIT_ALL_EMPLOYEE_CARD):Struct_Domain_Usercard_EditorList {

		// получаем всех кто имеют нужную роль для выбранного пользователя
		$relationship_user_obj_list = Type_User_Profile_RelationshipUserList::getByUserIdAndRole($user_id, $role);

		// собираем id редакторов в единый список
		$editors_list = [];
		foreach ($relationship_user_obj_list as $relationship_user_obj) {

			if ($relationship_user_obj->is_deleted == 1) {
				continue;
			}

			$editors_list[] = $relationship_user_obj->recipient_user_id;
		}

		return new Struct_Domain_Usercard_EditorList($user_id, $editors_list);
	}

	/**
	 * добавляем редактора карточки
	 *
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $recipient_user_id, int $role = Type_User_Profile_RelationshipUserList::ROLE_TO_EDIT_ALL_EMPLOYEE_CARD):void {

		Type_User_Profile_RelationshipUserList::add($user_id, $role, $recipient_user_id);
	}

	/**
	 * убираем редактора карточки у пользователя
	 */
	public static function remove(int $user_id, int $recipient_user_id):void {

		Type_User_Profile_RelationshipUserList::remove($user_id, $recipient_user_id);
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * Возвращаем всех редакторов (которые обладают всеми привилегиями) пользователя
	 *
	 * @throws \parseException
	 */
	public static function getAllUserEditorIdList(int $user_id):array {

		// получаем запись с редакторами
		$editors_obj = self::get($user_id);

		return self::getAllUserEditorIdListFromEditorListObj($editors_obj);
	}

	/**
	 * Проверяем, имеется ли привилегия у пользователя
	 */
	public static function isHavePrivileges(int $user_id, int $role, int $permissions, array $editor_list):bool {

		// если пользователь в числе редакторов карточки
		if (in_array($user_id, $editor_list)) {
			return true;
		}

		// если пользователь в числе тех, кто может изменять профиль
		if (\CompassApp\Domain\Member\Entity\Permission::canEditMemberProfile($role, $permissions)) {
			return true;
		}

		return false;
	}

	/**
	 * возвращаем всех редакторов пользователя из объекта Struct_Domain_Usercard_EditorList
	 */
	public static function getAllUserEditorIdListFromEditorListObj(Struct_Domain_Usercard_EditorList $editors_obj):array {

		return $editors_obj->editor_list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------
}