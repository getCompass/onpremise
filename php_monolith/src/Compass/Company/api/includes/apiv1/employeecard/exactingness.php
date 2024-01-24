<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс для работы с требовательностью карточки
 */
class Apiv1_EmployeeCard_Exactingness extends \BaseFrame\Controller\Api {

	protected const _PAGINATION_LIMIT_PER_PAGE = 100; // максимальное количество сообщений с требовательностью на странице

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getGaveMonthList",
		"getMessageKeyListByGaveMonth",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	// -------------------------------------------------------
	// ОБЩИЕ МЕТОДЫ
	// -------------------------------------------------------

	/**
	 * получаем список месяцев выданных требовательностей
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getGaveMonthList():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$year    = $this->post(\Formatter::TYPE_INT, "year", date("Y"));

		// проверяем параметры на корректность
		if ($user_id < 1 || $year < 1) {
			throw new ParamException("incorrect params");
		}

		// проверяем что пользователь существует
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);

		// получаем данные за год
		[$month_plan_list, $months_count] = Domain_EmployeeCard_Scenario_Api::getGaveMonthList(
			$user_id, $year, Type_User_Card_MonthPlan::MONTH_PLAN_EXACTINGNESS_TYPE
		);

		// собираем ответ для клиентов
		$output = [];
		foreach ($month_plan_list as $plan_obj) {
			$output[] = (object) Apiv1_Format::monthPlanDataItem($plan_obj);
		}

		// если количество месяцев за выбранный год получили больше значит имеются данные за следующий год
		$has_next = $months_count > count($month_plan_list);

		return $this->ok([
			"gave_month_data_list" => (array) $output,
			"has_next"             => (int) $has_next,
		]);
	}

	/**
	 * получаем список ключей сообщений выданных Требовательностей за конкретный месяц
	 *
	 * @return array
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getMessageKeyListByGaveMonth():array {

		$creator_user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$row_id          = $this->post(\Formatter::TYPE_INT, "row_id");
		$limit           = $this->post(\Formatter::TYPE_INT, "limit", self::_PAGINATION_LIMIT_PER_PAGE);
		$offset          = $this->post(\Formatter::TYPE_INT, "offset", 0);

		// проверяем параметры на корректность
		if ($creator_user_id < 1 || $row_id < 1 || $limit < 1 || $limit > self::_PAGINATION_LIMIT_PER_PAGE || $offset < 0) {
			throw new ParamException("incorrect params");
		}

		// проверяем что пользователь существует
		$this->_tryGetUserInfoAndThrowIfIncorrectUserId($creator_user_id);

		$output = [
			"message_key_list" => (array) [],
			"signature"        => (string) "",
			"has_next"         => (int) 0,
		];

		// получаем запись из таблицы
		try {
			$month_plan_obj = Type_User_Card_MonthPlan::get($row_id);
		} catch (\cs_RowIsEmpty) {
			return $this->ok($output);
		}

		// достаем Требовательности, выданные за этот месяц
		$exactingness_list = Type_User_Card_Exactingness::getListByMonthAt($creator_user_id, $month_plan_obj->created_at, $limit + 1, $offset);

		// если записей элементов больше чем лимит
		$has_next = 0;
		if (count($exactingness_list) > $limit) {

			$has_next = 1;
			array_pop($exactingness_list);
		}

		return $this->_getOutputMessageKeyListByMonth($exactingness_list, $has_next, $output);
	}

	/**
	 * возвращаем ответ метода getMessageKeyListByGaveMonth
	 *
	 * @param array $exactingness_list
	 * @param int   $has_next
	 * @param array $output
	 *
	 * @return array
	 */
	protected function _getOutputMessageKeyListByMonth(array $exactingness_list, int $has_next, array $output):array {

		// собираем message_map_list
		$message_map_list = [];
		foreach ($exactingness_list as $v) {

			// если отсутствует message_map, то пропускаем
			$message_map = Type_User_Card_Exactingness::getMessageMap($v->data);
			if (mb_strlen($message_map) < 1) {
				continue;
			}

			$message_map_list[] = (string) $message_map;
		}

		// если отсутствуют сообщения-требовательности
		if (count($message_map_list) < 1) {
			return $this->ok($output);
		}

		// получаем подпись, с помощью которой пользователь сможет получить доступ даже к скрытым сообщениям
		$signature = $this->_makeSignatureForGetMessageBatchingWithoutCheckPermission($message_map_list);

		return $this->ok([
			"message_map_list" => (array) $message_map_list,
			"signature"        => (string) $signature,
			"has_next"         => (int) $has_next,
		]);
	}

	/**
	 * формируем подпись, с помощью который пользователь сможет получить доступ к скрытым сообщениям
	 *
	 * @param array $message_map_list
	 *
	 * @return string
	 */
	protected function _makeSignatureForGetMessageBatchingWithoutCheckPermission(array $message_map_list):string {

		// сортируем message_map_list
		sort($message_map_list);

		// формируем строку для хэширования
		$temp = implode(".", $message_map_list);

		// добавляем user_id в конец
		$temp .= $this->user_id;

		return sha1($temp);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получить информацию о пользователе, но в случае некорректных данных — возвращать экзепшн
	 *
	 * @param int $user_id
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _tryGetUserInfoAndThrowIfIncorrectUserId(int $user_id):void {

		if ($user_id < 1) {
			throw new ParamException("incorrect param user_id");
		}

		// получаем информацию о пользователе
		try {
			$user_info = Gateway_Bus_CompanyCache::getMember($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("dont found user in company cache");
		}

		// если это бот
		if (Type_User_Main::isBot($user_info->npc_type)) {
			throw new ParamException("you can't do this action on bot-user");
		}
	}
}