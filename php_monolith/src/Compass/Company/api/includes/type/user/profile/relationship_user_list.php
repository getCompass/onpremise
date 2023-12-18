<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы с пользователями и их ролями
 */
class Type_User_Profile_RelationshipUserList {

	##########################################################
	# region список ролей пользователя
	##########################################################

	/** @var int роль, которая может редактировать ВСЁ */
	public const ROLE_TO_EDIT_ALL_EMPLOYEE_CARD = 1;

	/** @var int роль, которая может редактировать спринты карточки */
	public const ROLE_TO_EDIT_SPRINT = 2;

	/** @var int роль, которая может редактировать достижения карточки */
	public const ROLE_TO_EDIT_ACHIEVEMENT = 3;

	/** @var int роль, которая может редактировать вовлеченность в карточке */
	public const ROLE_TO_EDIT_LOYALTY = 4;

	/** @var int роль, которая может редактировать план на месяце в карточк */
	public const ROLE_TO_EDIT_MONTH_PLAN = 5;

	/** @var int роль, которая может редактировать список редакторов карточки */
	public const ROLE_TO_EDIT_EDITOR_LIST = 6;

	/** @var int роль, которая может редактировать благодарность в карточке */
	public const ROLE_TO_EDIT_RESPECT = 7;

	/** @var array список доступных ролей для редактирования */
	public const ALLOW_ROLE_TO_EDIT_LIST = [
		self::ROLE_TO_EDIT_ALL_EMPLOYEE_CARD,
		self::ROLE_TO_EDIT_SPRINT,
		self::ROLE_TO_EDIT_ACHIEVEMENT,
		self::ROLE_TO_EDIT_LOYALTY,
		self::ROLE_TO_EDIT_MONTH_PLAN,
		self::ROLE_TO_EDIT_EDITOR_LIST,
		self::ROLE_TO_EDIT_RESPECT,
	];

	# endregion
	##########################################################

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * получаем записи для пользователя определенной роли
	 *
	 * @param int $user_id
	 * @param int $role
	 *
	 * @return Struct_Domain_Usercard_MemberRel[]
	 *
	 * @throws ParseFatalException
	 */
	public static function getByUserIdAndRole(int $user_id, int $role):array {

		if (!self::isAllowRole($role)) {
			throw new ParseFatalException("incorrect role " . __METHOD__);
		}

		return Gateway_Db_CompanyMember_UsercardMemberRel::getByUserIdAndRole($user_id, $role);
	}

	/**
	 * добавляем пользователя с ролью по отношению к другому пользователю
	 *
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $role, int $recipient_user_id):void {

		Gateway_Db_CompanyMember_UsercardMemberRel::insertOrUpdate($user_id, $role, $recipient_user_id);
	}

	/**
	 * удаляем пользователя с ролью у другого пользователя
	 */
	public static function remove(int $user_id, int $recipient_user_id):void {

		Gateway_Db_CompanyMember_UsercardMemberRel::delete($user_id, $recipient_user_id);
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * проверяем доступна ли выбранная роль
	 */
	public static function isAllowRole(int $role):bool {

		return in_array($role, self::ALLOW_ROLE_TO_EDIT_LIST);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------
}