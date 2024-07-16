<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;

/**
 * Action для увольнения сотрудника
 */
class Domain_Company_Action_Dismissal {

	/**
	 * Удаляем пользователя из компании
	 *
	 * @param int    $user_id
	 * @param string $reason
	 *
	 * @long много действий для увольнения
	 * @throws \parseException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 */
	public static function do(int $user_id, string $reason = Member::LEAVE_REASON_KICKED, int $administrator_user_id = 0):void {

		$try_count = 0;
		$left_at   = time();

		$member             = Gateway_Bus_CompanyCache::getMember($user_id);
		$kicked_member_role = $member->role;

		// нельзя уволить бота
		if ($member->npc_type != \CompassApp\Domain\User\Main::NPC_TYPE_HUMAN) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("cant dismiss bot");
		}

		while ($try_count++ < Domain_User_Action_Member_CheckIsLeftCompany::MAX_TRY_COUNT) {

			try {

				// проверяем, кикнут ли пользователь
				if (Domain_User_Action_Member_CheckIsLeftCompany::do($user_id)) {
					break;
				}

				// устанавливаем роль kicked в компании и в пивоте
				$need_add_user_lobby = self::_needAddUserLobbyList($reason);

				Domain_User_Action_Member_SetKickedRole::do($user_id, $kicked_member_role, $left_at, $need_add_user_lobby, $reason);

				// удаляем все сессии пользователя
				Domain_User_Action_UserSessionActive_DeleteAllByUser::do($user_id);

				// очищаем все пуш токены
				Domain_User_Action_Notifications_DeleteUserNotification::do($user_id);

				// очищаем кэш
				Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

				// убираем пользователя из наблюдения обсервером
				Type_User_Observer::removeUserFromObserver($user_id);

				// удаляем все активные ссылки-инвайты пользователя
				Domain_User_Action_UserInviteLinkActive_DeleteAllByUser::do($user_id);

				// отвязываем от анонсов
				Gateway_Announcement_Main::unbindUserFromCompany($user_id, COMPANY_ID);
			} catch (\Exception $e) {

				$text = "ошибка при исключении пользователя = {$user_id}. Error: {$e->getMessage()}";
				Type_System_Admin::log("on-user-left-company", $text);
				// тут может выскочить ошибка, типа таймаута или еще что;
				// поэтому не ломаемся, пытаемся повторить до победного
			}
		}

		// если после всех попыток уволить, пользователь уволен не до конца, отправляем уведомление
		if ($try_count > Domain_User_Action_Member_CheckIsLeftCompany::MAX_TRY_COUNT && !Domain_User_Action_Member_CheckIsLeftCompany::do($user_id)) {

			try {

				$text = "Увольнение пользователя под угрозой провала, пожалуйста проверьте лог файл \"on-user-left-company\" чтобы узнать подробности";
				Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, $text);
			} catch (\Exception|\Error) {
				// если вдруг сервис для отправки недоступен - не разваливаем увольнение
			}
		}

		// выполняем другие действия увольнения
		// если вдруг что-то произошло - не разваливаем дальнейшее действия при увольнении
		try {

			// считаем сколько пользователей
			[$space_resident_user_id_list, $guest_user_id_list] = Domain_Member_Action_GetAll::do();
			$space_resident_count = count($space_resident_user_id_list);
			$guest_count          = count($guest_user_id_list);
			Domain_User_Action_Config_SetMemberCount::do($space_resident_count);
			Domain_User_Action_Config_SetGuestCount::do($guest_count);

			$routine_key = Gateway_Bus_Sender::getWaitRoutineKeyForUser($user_id);

			// шлем ws об исключении
			Gateway_Bus_Sender::userLeftCompany($user_id, $reason, $space_resident_count, $guest_count, $routine_key);

			// добавляем уведомление всем администраторам о том, что пользователь покинул компанию
			$company_name    = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME)["value"];
			$avatar_color_id = \BaseFrame\Domain\User\Avatar::getColorByUserId($member->user_id);
			$extra           = new Domain_Member_Entity_Notification_Extra(
				0, $member->full_name, $company_name, $member->avatar_file_key, \BaseFrame\Domain\User\Avatar::getColorOutput($avatar_color_id)
			);
			Domain_Member_Action_AddNotification::do($user_id, Domain_Member_Entity_Menu::LEFT_MEMBER, $extra, exclude_receiver_id: $administrator_user_id);

			// убираем уведомление у всех администраторов от том, что участник/гость вступал в компанию
			$undo_notification_menu_type = in_array($kicked_member_role, Member::SPACE_RESIDENT_ROLE_LIST)
				? Domain_Member_Entity_Menu::ACTIVE_MEMBER : Domain_Member_Entity_Menu::GUEST_MEMBER;
			Domain_Member_Action_UndoNotification::do($user_id, $undo_notification_menu_type);

			// прочитываем все уведомления у пользователя покидающего компанию
			Domain_Member_Action_UndoNotificationsForUser::do($user_id);

			// закрываем ws
			Gateway_Bus_Sender::closeConnectionsByUserIdWithWait($user_id, $routine_key);
		} catch (\Exception $e) {

			$text = "Произошла ошибка при увольнении пользователя = {$user_id}. Error: " . $e->getMessage();
			try {

				Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, $text);
			} catch (\Exception|\Error) {
				// если вдруг сервис для отправки недоступен - не разваливаем увольнение
				Type_System_Admin::log("on-user-left-company", $text);
			}
		}
	}

	/**
	 * нужно ли добавлять пользователя в лобби (предбанник) компании
	 */
	protected static function _needAddUserLobbyList(string $reason):bool {

		return in_array($reason, [
			Member::LEAVE_REASON_KICKED,
			Member::LEAVE_REASON_BLOCKED_IN_SYSTEM,
		]);
	}
}
