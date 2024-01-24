<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Контроллер для методов уведомлений администратора
 */
class Apiv2_Member_Menu extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"getNotifications",
		"readNotifications",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"readNotifications",
	];

	// методы, требующие премиум доступа
	public const ALLOW_WITH_PREMIUM_ONLY_METHODS = [];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => [
			"readNotifications",
		],
	];

	/**
	 * Получить список уведомлений
	 *
	 * @throws \Exception
	 */
	public function getNotifications():array {

		try {

			[
				$unread_active_member_list, $unread_guest_member_list, $unread_administrator_member_list, $unread_join_request_list,
				$unread_left_member_list, $is_member_count_trial_period_unread,
			] =
				Domain_Company_Scenario_Api::getNotifications($this->user_id, $this->role);
		} catch (\CompassApp\Domain\Member\Exception\IsNotAdministrator) {

			return $this->ok([
				"unread_active_member_list"           => [],
				"unread_guest_member_list"            => [],
				"unread_administrator_member_list"    => [],
				"unread_join_request_list"            => [],
				"unread_left_member_list"             => [],
				"is_member_count_trial_period_unread" => 0,
			]);
		}

		return $this->ok([
			"unread_active_member_list"           => $unread_active_member_list,
			"unread_guest_member_list"            => $unread_guest_member_list,
			"unread_administrator_member_list"    => $unread_administrator_member_list,
			"unread_join_request_list"            => $unread_join_request_list,
			"unread_left_member_list"             => $unread_left_member_list,
			"is_member_count_trial_period_unread" => $is_member_count_trial_period_unread,
		]);
	}

	/**
	 * Прочитать уведомления по типу
	 *
	 * @throws \Exception
	 */
	public function readNotifications():array {

		$type_list = $this->post(\Formatter::TYPE_ARRAY, "type_list");

		try {

			Domain_Company_Scenario_Api::readNotifications($this->user_id, $this->role, $type_list);
		} catch (\CompassApp\Domain\Member\Exception\IsNotAdministrator) {
			return $this->error(2238001, "User is not a company owner");
		} catch (Domain_Member_Exception_IncorrectMenuType) {
			throw new ParamException("incorrect type");
		}

		return $this->ok();
	}
}