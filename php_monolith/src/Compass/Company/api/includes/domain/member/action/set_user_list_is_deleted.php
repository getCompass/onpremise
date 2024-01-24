<?php

namespace Compass\Company;

/**
 * Действие установки удаления аккаунта
 */
class Domain_Member_Action_SetUserListIsDeleted {

	/**
	 * Установим что пользователь удалил аккаунт
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function do(array $user_id_is_deleted_list, array $user_info_is_deleted_list):void {

		// пройдемся по каждому пользователю
		foreach ($user_id_is_deleted_list as $user_id) {

			// обновляем бейдж в транзакции
			Gateway_Db_CompanyData_Main::beginTransaction();
			$user_info = Gateway_Db_CompanyData_MemberList::getForUpdate($user_id);

			// если уже пометили удалившим - ничего не делаем
			if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {

				Gateway_Db_CompanyData_Main::rollback();
				continue;
			}

			// устанавливаем что пользователь удалил аккаунт
			$extra = \CompassApp\Domain\Member\Entity\Extra::setIsDeleted($user_info->extra, 1);
			if (isset($user_info_is_deleted_list[$user_id]["disabled_at"])) {
				$extra = \CompassApp\Domain\Member\Entity\Extra::setAliasDisabledAt($extra, $user_info_is_deleted_list[$user_id]["disabled_at"]);
			}

			$set = [
				"updated_at" => time(),
				"extra"      => $extra,
			];
			Gateway_Db_CompanyData_MemberList::set($user_id, $set);
			Gateway_Db_CompanyData_Main::commitTransaction();

			// удаляем ссылки т.к если компания была спящей то ссылки не трогали
			Domain_User_Action_UserInviteLinkActive_DeleteAllByUser::do($user_id);
		}
	}
}