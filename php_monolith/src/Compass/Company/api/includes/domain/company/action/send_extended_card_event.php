<?php

namespace Compass\Company;

/**
 * Action для изменения карточки компании
 */
class Domain_Company_Action_SendExtendedCardEvent {

	/**
	 * Изменяем настройку карточки компании
	 *
	 * @param int $user_id
	 * @param int $is_enabled
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 */
	public static function do(int $user_id, int $is_enabled):void {

		if ($is_enabled === 1) {
			self::_sendExtendedEmployeeCardEvent($user_id);
		}
		Gateway_Bus_Sender::employeeCardSettingsChanged($is_enabled);
	}

	/**
	 * Отправляем события о смене настройки карточки
	 *
	 * @param int $user_id
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 */
	protected static function _sendExtendedEmployeeCardEvent(int $user_id):void {

		Gateway_Socket_Conversation::createCompanyExtendedEmployeeCardGroups($user_id);

		$limit  = 50;
		$offset = 0;
		do {

			[$user_list, $has_next] = Gateway_Db_CompanyData_MemberList::getAllActiveMemberWithPagination($offset, $limit);

			$user_id_list = [];

			/** @var \CompassApp\Domain\Member\Struct\Main $user */
			foreach ($user_list as $user) {

				// пропускаем гостей
				if ($user->role === \CompassApp\Domain\Member\Entity\Member::ROLE_GUEST) {
					continue;
				}

				if (Type_User_Main::isHuman($user->npc_type)) {
					$user_id_list[] = $user->user_id;
				}
			}
			$event = Type_Event_Company_ExtendedEmployeeCardEnabled::create($user_id, $user_id_list);
			Gateway_Event_Dispatcher::dispatch($event, true);

			$offset += $limit;
		} while ($has_next);
	}
}
