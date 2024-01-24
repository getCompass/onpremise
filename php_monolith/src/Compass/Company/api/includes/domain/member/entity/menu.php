<?php

namespace Compass\Company;

/**
 * Класс для работы с сущностью меню уведомлений пользователя
 */
class Domain_Member_Entity_Menu {

	public const JOIN_REQUEST              = 10; // заявка на вступление
	public const ADMINISTRATOR_MEMBER      = 20; // новый администратор
	public const ACTIVE_MEMBER             = 30; // новый участник
	public const LEFT_MEMBER               = 40; // участник удален
	public const MEMBER_COUNT_TRIAL_PERIOD = 50; // активирован период триала
	public const GUEST_MEMBER              = 60; // новый гость

	// массив допустимых типов уведомлений
	public const AVAILABLE_NOTIFICATION_TYPE_LIST = [
		self::JOIN_REQUEST,
		self::ADMINISTRATOR_MEMBER,
		self::ACTIVE_MEMBER,
		self::LEFT_MEMBER,
		self::MEMBER_COUNT_TRIAL_PERIOD,
		self::GUEST_MEMBER,
	];

	// требования для получений уведомлений
	public const NOTIFICATION_PERMISSION_REQUIREMENTS = [
		self::JOIN_REQUEST => \CompassApp\Domain\Member\Entity\Permission::MEMBER_INVITE,
		self::LEFT_MEMBER  => \CompassApp\Domain\Member\Entity\Permission::MEMBER_KICK,
	];

	// требования для получения пушей
	public const PUSH_PERMISSION_REQUIREMENTS = [
		self::ACTIVE_MEMBER => \CompassApp\Domain\Member\Entity\Permission::MEMBER_INVITE,
		self::JOIN_REQUEST  => \CompassApp\Domain\Member\Entity\Permission::MEMBER_INVITE,
		self::GUEST_MEMBER  => \CompassApp\Domain\Member\Entity\Permission::MEMBER_INVITE,
	];

	// массив для конвертации числового значения в строковое
	public const NOTIFICATION_TYPE_SCHEMA = [
		self::JOIN_REQUEST              => "join_request",
		self::ADMINISTRATOR_MEMBER      => "administrator_member",
		self::ACTIVE_MEMBER             => "active_member",
		self::LEFT_MEMBER               => "left_member",
		self::MEMBER_COUNT_TRIAL_PERIOD => "member_count_trial_period",
		self::GUEST_MEMBER              => "guest_member",
	];

	// конвертируем значения для чтения уведомлений с клиента
	public const CLIENT_TYPE_LIST_SCHEMA = [
		"active_member_list"        => self::ACTIVE_MEMBER,
		"left_member_list"          => self::LEFT_MEMBER,
		"join_request_list"         => self::JOIN_REQUEST,
		"administrator_member_list" => self::ADMINISTRATOR_MEMBER,
		"member_count_trial_period" => self::MEMBER_COUNT_TRIAL_PERIOD,
		"guest_member_list"         => self::GUEST_MEMBER,
	];

	// типы уведомлений, для которых может понадобиться не добавлять уведомление в базу
	public const EXCLUDE_FILTER_TYPE_LIST = [
		Domain_Member_Entity_Menu::ACTIVE_MEMBER,
		Domain_Member_Entity_Menu::GUEST_MEMBER,
		Domain_Member_Entity_Menu::LEFT_MEMBER,
		Domain_Member_Entity_Menu::ADMINISTRATOR_MEMBER,
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * Получаем список уведомлений по пользователю
	 */
	public static function getNotificationList(array $owner_id_list, int $action_user_id, int $type):array {

		return Gateway_Db_CompanyData_MemberMenu::getNotificationList($owner_id_list, $action_user_id, $type);
	}

	/**
	 * Получаем список непрочитанных уведомлений по пользователю
	 */
	public static function getUnreadNotificationList(array $owner_id_list, int $action_user_id, int $type):array {

		return Gateway_Db_CompanyData_MemberMenu::getUnreadNotificationList($owner_id_list, $action_user_id, $type);
	}

	/**
	 * Добавить или обновить записи
	 *
	 * @param array $insert_user_id_list
	 * @param array $update_user_id_list
	 * @param int   $action_user_id
	 * @param int   $type
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function createOrUpdate(array $insert_user_id_list, array $update_user_id_list, int $action_user_id, int $type):void {

		$insert_list = [];

		foreach ($insert_user_id_list as $user_id) {

			$insert_list[] = new Struct_Db_CompanyData_MemberMenu(
				null,
				$user_id,
				$action_user_id,
				$type,
				1,
				time(),
				0
			);
		}

		// если что-то надо вставить в таблицу - вставляем
		if (count($insert_list) > 0) {
			Gateway_Db_CompanyData_MemberMenu::insertList($insert_list);
		}

		$update_set = [
			"is_unread"  => 1,
			"updated_at" => time(),
		];

		// обновляем записи, если есть
		if (count($update_user_id_list) > 0) {

			// получаем записи, которые будем обновлять
			$notification_list = Gateway_Db_CompanyData_MemberMenu::getNotificationList($update_user_id_list, $action_user_id, $type);

			// обновляем записи
			Gateway_Db_CompanyData_MemberMenu::setList(array_column($notification_list, "notification_id"), $update_set);
		}
	}

	/**
	 * Получаем список всех непрочитанных уведомлений
	 */
	public static function getAllUserUnreadNotifications(int $user_id):array {

		return Gateway_Db_CompanyData_MemberMenu::getAllUserUnreadNotifications($user_id);
	}

	/**
	 * Прочитываем уведомления для массива пользователей по типу
	 */
	public static function readNotificationsByType(array $owner_user_id_list, int $action_user_id, int $type):int {

		$update_set = [
			"is_unread"  => 0,
			"updated_at" => time(),
		];

		// обновляем записи, если есть
		if (count($owner_user_id_list) > 0) {

			// получаем записи, которые будем обновлять
			$notification_list = Gateway_Db_CompanyData_MemberMenu::getNotificationList($owner_user_id_list, $action_user_id, $type);

			// обновляем записи
			Gateway_Db_CompanyData_MemberMenu::setList(array_column($notification_list, "notification_id"), $update_set);
		}

		return 0;
	}

	/**
	 * Прочитываем все непрочитанные уведомления для пользователя
	 */
	public static function readAllUnreadNotifications(int $user_id):int {

		// получаем все непрочитанные записи
		$notification_list = Gateway_Db_CompanyData_MemberMenu::getAllUserUnreadNotifications($user_id);

		$update_set = [
			"is_unread"  => 0,
			"updated_at" => time(),
		];

		// обновляем записи
		return Gateway_Db_CompanyData_MemberMenu::setList(array_column($notification_list, "notification_id"), $update_set);
	}

	/**
	 * Прочитываем все непрочитанные уведомления пользователя по типу
	 */
	public static function readAllUnreadNotificationsByType(int $user_id, array $type_list):int {

		// получаем все непрочитанные записи по типу
		$notification_list = Gateway_Db_CompanyData_MemberMenu::getUnreadNotificationsByType($user_id, $type_list);

		$update_set = [
			"is_unread"  => 0,
			"updated_at" => time(),
		];

		// обновляем записи
		return Gateway_Db_CompanyData_MemberMenu::setList(array_column($notification_list, "notification_id"), $update_set);
	}

	/**
	 * требуется ли исключить уведомление
	 */
	public static function isExcludeTypeNotification(int $type):bool {

		return in_array($type, self::EXCLUDE_FILTER_TYPE_LIST);
	}
}
