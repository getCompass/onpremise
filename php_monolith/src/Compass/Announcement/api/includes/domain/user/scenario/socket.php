<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Сценарии анонсов для API
 */
class Domain_User_Scenario_Socket {

	public const TOKEN_EXPIRE_TIME = 60 * 60 * 24;

	/**
	 * Создает токен
	 *
	 * @param int    $user_id
	 * @param string $device_id
	 * @param array  $new_list_id_companies
	 *
	 * @return string
	 * @throws \queryException
	 */
	public static function addToken(int $user_id, string $device_id, array $new_list_id_companies):string {

		return Domain_User_Action_AddToken::do($user_id, $device_id, $new_list_id_companies, time() + self::TOKEN_EXPIRE_TIME);
	}

	/**
	 * Привязывает пользователя к компании
	 *
	 * @param int $user_id
	 * @param int $company_id
	 *
	 */
	public static function bindUserToCompany(int $user_id, int $company_id):void {

		Domain_User_Action_BindUserToCompany::do($user_id, $company_id, time() + self::TOKEN_EXPIRE_TIME);
	}

	/**
	 * Отвязывает пользователя от компании
	 *
	 * @param int $user_id
	 * @param int $company_id
	 */
	public static function unBindUserFromCompany(int $user_id, int $company_id):void {

		Domain_User_Action_UnbindUserToCompany::do($user_id, $company_id);
	}

	/**
	 * Удаляет данные доступа для указанного пользователя.
	 *
	 * @param int $user_id
	 */
	public static function invalidateUser(int $user_id):void {

		// удаляем все токены для указанного пользователя
		Gateway_Db_AnnouncementSecurity_TokenUser::deleteAllUserTokens($user_id);
	}

	/**
	 * Меняем пользователей, которым приходят анонсы
	 * Только для уникальных анонсов
	 *
	 * @param int   $company_id
	 * @param array $type_list
	 * @param array $add_user_id_list
	 * @param array $remove_user_id_list
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function changeReceiverUserList(int $company_id, array $type_list, array $add_user_id_list = [], array $remove_user_id_list = []):void {

		// отметаем все неуникальные анонсы
		$type_list = array_filter($type_list, fn (int $type) => Domain_Announcement_Entity::isUniqueType($type));

		if (count($type_list) < 1) {
			return;
		}

		$announcement_list = Domain_Announcement_Action_GetListByType::do($company_id, $type_list);

		// меняем каждый анонс
		foreach ($announcement_list as $announcement) {

			$receiver_user_id_list = array_merge($announcement->receiver_user_id_list, $add_user_id_list);
			$receiver_user_id_list = array_diff($receiver_user_id_list, $remove_user_id_list);

			self::_updateReceiverUserList($announcement, $receiver_user_id_list);
		}
	}

	/**
	 * Обновить анонсу список пользователей, которые его получат
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 * @param array                                   $receiver_user_id_list
	 *
	 * @return void
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _updateReceiverUserList(Struct_Db_AnnouncementMain_Announcement $announcement, array $receiver_user_id_list):void {

		$announcement = new Struct_Db_AnnouncementMain_Announcement(
			0,
			$announcement->is_global,
			$announcement->type,
			$announcement->status,
			$announcement->company_id,
			$announcement->priority,
			$announcement->created_at,
			time(),
			$announcement->expires_at,
			$announcement->resend_repeat_time,
			$receiver_user_id_list,
			$announcement->excluded_user_id_list,
			$announcement->extra
		);

		Domain_Announcement_Action_Publish::do($announcement);
	}
}
