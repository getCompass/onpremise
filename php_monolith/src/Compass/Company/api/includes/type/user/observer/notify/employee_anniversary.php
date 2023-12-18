<?php

namespace Compass\Company;

/**
 * Класс для работы с уведомлениями в обсервере
 */
class Type_User_Observer_Notify_EmployeeAnniversary extends Type_User_Observer_Default {

	/**
	 * Создает дополнительные данные для генератора задачи.
	 * DependencyInjection штука, чтобы генератор можно было затестить в любой момент.
	 */
	public static function provideJobExtra(int $user_id, int $job_type):array {

		return [
			"user_id"     => $user_id,  // пользователь, с которым работаем
			"job_type"    => $job_type, // тип выполняем задачи (нужен чтобы исполнять разные задачи данного класса)
			"action_time" => time(),    // время выполнения задачи
		];
	}

	/**
	 * Генерирует задачи, которые нужно взять на исполнение
	 *
	 * @param array $observer_data обсервер data
	 * @param array $job_extra     экстра для генерации задачи
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 */
	public static function provideJobList(array $observer_data, array $job_extra):array {

		// тип выполняемой задачи
		$job_type = $job_extra["job_type"];

		// если время не пришло, то просто выходим
		if ($observer_data[$job_type]["need_work"] > $job_extra["action_time"]) {
			return [];
		}

		// генерируем данные для выполнения задачи
		$job_data = [
			"user_id"       => $job_extra["user_id"],
			"job_type"      => $job_type,
			"observer_data" => $observer_data,
			"next_time"     => self::getNextWorkTime($job_extra),
		];

		return [
			$job_type => $job_data,
		];
	}

	/**
	 * Выполняем задачу
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function doJob(array $job_data):int {

		// если включены уведомления в главный чат
		if (Domain_Company_Action_GetGeneralChatNotificationSettings::do() === 1) {

			// отправляем сообщение о годовщине сотрудника в компании
			self::_sendMessageAboutEmployeeAnniversary($job_data);
		}

		return $job_data["next_time"];
	}

	/**
	 * Отправляем сообщение о годовщине сотрудника в компании
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \apiAccessException
	 */
	protected static function _sendMessageAboutEmployeeAnniversary(array $job_data):void {

		// получаем информацию о пользователе
		$user_id   = $job_data["user_id"];
		$user_info = Gateway_Bus_CompanyCache::getMember($user_id);

		// получаем редакторов карточки пользователя
		$editor_user_id_list = Type_User_Card_EditorList::getAllUserEditorIdList($user_id);

		// получаем администраторов с правами изменения профиля
		$admin_list = Domain_User_Action_Member_GetByPermissions::do([\CompassApp\Domain\Member\Entity\Permission::MEMBER_PROFILE_EDIT]);

		$admin_id_list = \CompassApp\Domain\Member\Entity\Member::getUserIdListFromMemberStruct($admin_list);

		// собираем в единые список кто может редактировать карточку сотрудника
		$editor_user_id_list = array_unique(array_merge($editor_user_id_list, $admin_id_list));

		// убираем нашего пользователя из списка редакторов если он там был
		$editor_user_id_list = arrayValuesInt(array_diff($editor_user_id_list, [$user_id]));

		// получаем ключ чата в который шлем эвент
		$conversation_config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME);
		$conversation_map    = $conversation_config["value"];

		// отправляем системное сообщение от бота всем собранным пользователям
		Gateway_Event_Dispatcher::dispatch(
			Type_Event_Member_AnniversaryReached::create($conversation_map, $user_id, $editor_user_id_list, $user_info->company_joined_at), true);
	}

	/**
	 * Возвращает время следующего выполнения задачи
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getNextWorkTime(array $job_extra):int {

		// получаем информацию о пользователе
		$user_id = $job_extra["user_id"];

		try {
			$user_info         = Gateway_Bus_CompanyCache::getMember($user_id);
			$company_joined_at = $user_info->company_joined_at;
		} catch (\cs_RowIsEmpty) {

			// если пользователь не успел появиться в кэше после вступления
			$company_joined_at = time();
		}

		// получаем объект Date времени вступления пользователя в компанию
		$date = new \DateTime();
		$date->setTimestamp($company_joined_at);

		// разница между $date и текущим днем
		$current_date = new \DateTime();
		$current_date->setTimestamp(dayEnd());

		// если дата вступления еще не наступила - ставим год от даты вступления
		if ($current_date < $date) {
			return strtotime("+1 years 10:00", $company_joined_at);
		}

		$interval = $date->diff($current_date);

		// сколько прошло лет со дня вступления пользователя
		$years = $interval->y;

		// добавляем годик для следующего выполнения
		$years++;

		// получаем время следующего выполнения
		return strtotime("+ {$years} years 10:00", $company_joined_at);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------
}