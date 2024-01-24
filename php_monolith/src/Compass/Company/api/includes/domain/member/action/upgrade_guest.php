<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс описывает действие повышения гостя до участника в пространстве
 */
class Domain_Member_Action_UpgradeGuest {

	/**
	 * Выполняем действие
	 *
	 * @param int    $user_id
	 * @param int    $guest_id
	 * @param string $locale
	 *
	 * @throws Domain_Space_Exception_ActionRestrictedByTariff
	 * @throws \Throwable
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do(int $user_id, int $guest_id, string $locale):void {

		// делаем socket-запрос в pivot – оповещаем, что в пространство вступает еще один участник, если есть свободный слот в тарифном плане – займи его
		[$can_increase, $is_trial_activated] = Gateway_Socket_Pivot::increaseMemberCountLimit($user_id);

		// если не можем повысить до участника
		if (!$can_increase) {
			throw new Domain_Space_Exception_ActionRestrictedByTariff("cant confirm due tariff restrictions");
		}

		// обновляем данные со стороны пространства
		/** @var \CompassApp\Domain\Member\Struct\Main $member */
		[$member, $member_count, $guest_count] = self::_updateSpaceData($user_id, $guest_id, $locale);

		// обновляем данные со стороны пивота
		Gateway_Socket_Pivot::onUpgradeGuest();

		// отправляем ws событие о том, что у пользователя изменена роль
		Gateway_Bus_Sender::guestUpgraded($member->user_id, $member_count, $guest_count);

		// если активировали триал - оставляем уведомление об этом
		if ($is_trial_activated) {

			$company_name    = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::COMPANY_NAME)["value"];
			$avatar_color_id = \BaseFrame\Domain\User\Avatar::getColorByUserId($member->user_id);
			$extra           = new Domain_Member_Entity_Notification_Extra(
				0, $member->full_name, $company_name,
				$member->avatar_file_key, \BaseFrame\Domain\User\Avatar::getColorOutput($avatar_color_id)
			);
			Domain_Member_Action_AddNotification::do(0, Domain_Member_Entity_Menu::MEMBER_COUNT_TRIAL_PERIOD, $extra);
		}
	}

	/**
	 * Обновляем данные со стороны пространства
	 *
	 * @param int    $user_id
	 * @param int    $guest_id
	 * @param string $locale
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	protected static function _updateSpaceData(int $user_id, int $guest_id, string $locale):array {

		// получаем дефолтное описание и бейдж
		[$short_description, $badge_color_id, $badge_content] = Domain_Member_Entity_EmployeeCard::getJoiningInitialData(Member::ROLE_MEMBER, false, $locale);

		Gateway_Db_CompanyData_Main::beginTransaction();

		// получаем сущность пользователя
		$member = Gateway_Db_CompanyData_MemberList::getForUpdate($guest_id);

		// обновляем роль, бейдж, короткое описание
		$member->role              = Member::ROLE_MEMBER;
		$member->extra             = \CompassApp\Domain\Member\Entity\Extra::setBadgeInExtra($member->extra, $badge_color_id, $badge_content);
		$member->short_description = $short_description;

		// обновляем запись
		Gateway_Db_CompanyData_MemberList::set($guest_id, [
			"role"              => $member->role,
			"extra"             => $member->extra,
			"short_description" => $member->short_description,
		]);

		Gateway_Db_CompanyData_Main::commitTransaction();

		// обновляем кэш
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($guest_id);

		// пересчитываем счетчики кол-ва участников и гостей пространства
		[$space_resident_user_id_list, $guest_user_id_list] = Domain_Member_Action_GetAll::do();
		$member_count = count($space_resident_user_id_list);
		$guest_count  = count($guest_user_id_list);
		Domain_User_Action_Config_SetMemberCount::do($member_count);
		Domain_User_Action_Config_SetGuestCount::do($guest_count);

		// добавляем пользователя в дефолтные группы пространства
		Gateway_Socket_Conversation::addToDefaultGroups($guest_id, false, $member->role, $locale);

		// отправляем ивент о смене роли
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserCompany_MemberRoleChanged::create($member->user_id, $user_id, Member::ROLE_GUEST, $member->role), true);

		return [$member, $member_count, $guest_count];
	}
}