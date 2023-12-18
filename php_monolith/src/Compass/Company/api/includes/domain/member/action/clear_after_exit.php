<?php

namespace Compass\Company;

/**
 * Действие очистки пользователя как участника компании
 */
class Domain_Member_Action_ClearAfterExit {

	/**
	 * Выполняем
	 */
	public static function do(int $exit_user_id):bool {

		// чистим dynamic данные пользователя о часах
		self::_clearDynamicWorkedHours($exit_user_id);

		// чистим редакторов
		self::_clearEditors($exit_user_id);

		// удаляем запрос на оплату, если нужно
		self::_deletePaymentRequestIfNeed($exit_user_id);

		return true;
	}

	/**
	 * чистим dynamic данные пользователя о часах
	 */
	protected static function _clearDynamicWorkedHours(int $exit_user_id):void {

		Type_User_Card_DynamicData::clearAllWorkHours($exit_user_id);
	}

	/**
	 * чистим редакторов
	 *
	 * @throws \parseException
	 */
	protected static function _clearEditors(int $exit_user_id):void {

		// получаем общее количество участников в компании
		$config               = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::MEMBER_COUNT);
		$company_member_count = $config["value"] ?? 10;

		$member_list = Gateway_Db_CompanyData_MemberList::getAllActiveMember($company_member_count);

		// убираем у всех пользователей редактора
		foreach ($member_list as $member) {

			// удаляем нашего редактора - если он администратор, просто пропускаем
			try {

				$editor_id_list = Type_User_Card_EditorList::getAllUserEditorIdList($member->user_id);
				Domain_EmployeeCard_Action_Editor_Remove::do($editor_id_list, $member->user_id, $exit_user_id);
			} catch (cs_AdministrationNotIsDeletingEditor) {
				continue;
			}
		}
	}

	/**
	 * удаляем запрос на оплату от пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _deletePaymentRequestIfNeed(int $exit_user_id):void {

		Domain_Premium_Action_PaymentRequest_Delete::do($exit_user_id);
	}
}