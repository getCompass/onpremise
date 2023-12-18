<?php

namespace Compass\Company;

/**
 * Класс для работы с сущностью рабочих часов карточки
 */
class Type_User_Card_WorkedHours {

	public const WORKED_HOURS_USER_TYPE   = 1; // рабочие часы пользователя
	public const WORKED_HOURS_SYSTEM_TYPE = 2; // рабочие часы системы (например, при автофиксации)

	public const WORKED_HOURS_FOR_DAY_STANDARD  = 9;  // стандарт для рабочего дня ("отработал больше этого значения? you are my Херо")
	public const WORKED_HOURS_FOR_WEEK_STANDARD = 45; // стандарт для рабочей недели ("отработал больше этого значения? you are my Херо")
	public const WORKED_HOURS_DAYS_IN_WEEK      = 5;  // количество рабочих дней в недели

	/** @var int отвечает за какое количество последних недель высчитываются средние рабочие часы */
	public const LAST_WEEKS_COUNT_FOR_AVG_HOURS = 2;

	/** @var int время, в течении которого можно редактировать итем рабочего времени */
	protected const _ALLOW_TO_EDIT_TIME = 60 * 10;

	/** @var int количество пользователей, для списка первых мест по рабочему времени */
	public const WORKSHEET_USER_PER_LIST_COUNT = 3;

	/** @var int коэффициент деления для рабочих часов */
	protected const _DIVISION_COEFFICIENT_OF_WORKED_HOURS = 10;

	/**
	 * Получаем список итемов фиксации рабочего времени
	 *
	 * @return Struct_Domain_Usercard_WorkedHours[]
	 */
	public static function getList(array $worked_hour_id_list):array {

		return Gateway_Db_CompanyMember_UsercardWorkedHourList::getList($worked_hour_id_list);
	}

	/**
	 * Фиксируем отработанные часы
	 * Время не может фиксироваться чаще чем 1 раз за день, но его можно отредактировать через отдельный метод
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function doCommit(int $user_id, int $day_start_at, float $value, int $type = self::WORKED_HOURS_USER_TYPE):Struct_Domain_Usercard_WorkedHours {

		// пытаемся добавить новую запись
		try {
			$worked_hours_obj = self::add($user_id, $day_start_at, $value, $type);
		} catch (cs_RowAlreadyExist) {

			// если запись уже имеется в базе, то достаем ее
			return self::getOneByDayStartAt($user_id, $day_start_at);
		}

		// если удалось вставить запись
		// также добавляем значение в таблицу dynamic пользователя
		// обновляем dynamic-данные при добавлении рабочих часов
		Type_User_Card_WorkedHours::updateDynamicDataIfAdd($user_id, $value);

		return $worked_hours_obj;
	}

	/**
	 * Добавляем новую запись в таблицу
	 *
	 * @throws cs_RowAlreadyExist
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $day_start_at, float $value, int $type):Struct_Domain_Usercard_WorkedHours {

		// инициализируем новую data
		$data = self::initData();

		return Gateway_Db_CompanyMember_UsercardWorkedHourList::add($user_id, $type, $day_start_at, $value, $data);
	}

	/**
	 * Получаем запись за определенный день
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOneByDayStartAt(int $user_id, int $day_start_at):Struct_Domain_Usercard_WorkedHours {

		return Gateway_Db_CompanyMember_UsercardWorkedHourList::getOneByDayStartAt($user_id, $day_start_at);
	}

	/**
	 * Обновляем запись в таблице
	 */
	public static function set(int $worked_hour_id, array $set):void {

		Gateway_Db_CompanyMember_UsercardWorkedHourList::set($worked_hour_id, $set);
	}

	/**
	 * Получаем записи по временной метке
	 *
	 * @return Struct_Domain_Usercard_WorkedHours[]
	 */
	public static function getListByDayStartAt(int $user_id, int $day_start_at, int $limit = 100):array {

		return Gateway_Db_CompanyMember_UsercardWorkedHourList::getListByDayStartAt($user_id, $day_start_at, $limit);
	}

	/**
	 * Получаем запись рабочих часов
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $worked_hour_id):Struct_Domain_Usercard_WorkedHours {

		return Gateway_Db_CompanyMember_UsercardWorkedHourList::getOne($worked_hour_id);
	}

	/**
	 * Меняем время рабочих часов
	 */
	public static function editWorkedHours(int $worked_hour_id, float $worked_hours):void {

		self::set($worked_hour_id, [
			"value_1000" => floatToInt($worked_hours),
			"updated_at" => time(),
		]);
	}

	/**
	 * Добавить идентификаторы зафиксированных сообщений
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function doAppendFixedMessageMap(int $worked_hour_id, array $message_map_list):void {

		// ставим блокировку на запись
		Gateway_Db_CompanyMember_UsercardWorkedHourList::beginTransaction();
		$worked_hours_obj = Gateway_Db_CompanyMember_UsercardWorkedHourList::getOneForUpdate($worked_hour_id);
		if ($worked_hours_obj->type == self::WORKED_HOURS_SYSTEM_TYPE) {

			Gateway_Db_CompanyMember_UsercardWorkedHourList::rollback();
			return;
		}

		// достаем из data список идентификаторов закрепленных за рабочими часами сообщений
		$fixed_message_map_list = self::getFixedMessageMapList($worked_hours_obj->data);

		// добавляем в массив прикрепленные сообщения
		$fixed_message_map_list = array_merge($fixed_message_map_list, $message_map_list);
		$fixed_message_map_list = array_unique($fixed_message_map_list);

		// обязательно обнуляем is_deleted и устанавливаем актуальный created_at в том случае, если запись была ранее удалена
		// на тот случай, если пользователь проделал следующие шаги:
		// 1. зафиксировал время
		// 2. удалил полностью фиксацию
		// 3. фиксирует снова
		if ($worked_hours_obj->is_deleted == 1) {

			$worked_hours_obj->is_deleted = 0;
			$worked_hours_obj->created_at = time();
		}

		$worked_hours_obj->data = self::setFixedMessageMapList($worked_hours_obj->data, $fixed_message_map_list);

		// обновляем запись
		Gateway_Db_CompanyMember_UsercardWorkedHourList::set($worked_hour_id, [
			"is_deleted" => $worked_hours_obj->is_deleted,
			"created_at" => $worked_hours_obj->created_at,
			"updated_at" => time(),
			"data"       => $worked_hours_obj->data,
		]);

		Gateway_Db_CompanyMember_UsercardWorkedHourList::commitTransaction();
	}

	/**
	 * Попытаться удалить объект с зафиксированным временем
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function tryDelete(int $worked_hour_id, array $deleted_fixed_message_map_list):Struct_Domain_Usercard_WorkedHours {

		// ставим блокировку на запись
		Gateway_Db_CompanyMember_UsercardWorkedHourList::beginTransaction();
		$worked_hours_obj = Gateway_Db_CompanyMember_UsercardWorkedHourList::getOneForUpdate($worked_hour_id);

		// достаем из data список идентификаторов закрепленных за рабочими часами сообщений
		$fixed_message_map_list = self::getFixedMessageMapList($worked_hours_obj->data);

		// удаляем все переданные сообщения
		$fixed_message_map_list = self::_doRemoveDeletedMessages($fixed_message_map_list, $deleted_fixed_message_map_list);

		// определяем, нужно ли вовсе удалить объект
		$worked_hours_obj->is_deleted = count($fixed_message_map_list) == 0 ? 1 : 0;

		// показываем что объект обновился
		$worked_hours_obj->updated_at = time();

		// устанавливаем обновленный список закрепленных сообщений
		$worked_hours_obj->data = self::setFixedMessageMapList($worked_hours_obj->data, $fixed_message_map_list);

		// обновляем запись
		self::set($worked_hour_id, [
			"is_deleted" => $worked_hours_obj->is_deleted,
			"updated_at" => $worked_hours_obj->updated_at,
			"data"       => $worked_hours_obj->data,
		]);

		Gateway_Db_CompanyMember_UsercardWorkedHourList::commitTransaction();

		return $worked_hours_obj;
	}

	/**
	 * Удаляем из массива с закрепленными сообщениями конкретный список сообщений message_map_list
	 */
	public static function _doRemoveDeletedMessages(array $old_message_map_list, array $deleted_fixed_message_map_list):array {

		// делаем ассоциативный массив, чтобы было легче
		$deleted_fixed_message_map_list_by_message_map = [];
		foreach ($deleted_fixed_message_map_list as $item) {
			$deleted_fixed_message_map_list_by_message_map[$item] = $deleted_fixed_message_map_list_by_message_map;
		}

		// пробегаемся по всем существующим зафиксированным сообщениям
		$output = [];
		foreach ($old_message_map_list as $item) {

			// если такое сообщение не удаляли
			if (isset($deleted_fixed_message_map_list_by_message_map[$item])) {
				continue;
			}

			$output[] = $item;
		}

		return $output;
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * Метод для получения времени начала и конца недели
	 */
	public static function getStartedAtAndEndAt(int $week, int $year):array {

		// устанавливаем дату
		$date = new \DateTime();
		$date->setISODate($year, $week);

		// получаем начало недели в формате строки
		$week_started_at_string = $date->format("Y-m-d");

		// получаем конец недели в формате строки
		$date->modify("+6 days");
		$week_end_at_string = $date->format("Y-m-d");

		return [
			"week_started_at_string" => $week_started_at_string,
			"week_end_at_string"     => $week_end_at_string,
		];
	}

	/**
	 * Получаем все записи фиксации рабочего времени
	 *
	 * @return Struct_Domain_Usercard_WorkedHours[]
	 */
	public static function getAllWorkedHoursObjList(int $user_id):array {

		$limit                       = 1000;
		$last_worked_hour_id         = 0;
		$total_worked_hours_obj_list = [];
		do {

			// достаем из базы
			$worked_hours_obj_list = Gateway_Db_CompanyMember_UsercardWorkedHourList::getByLastWorkedId($user_id, $last_worked_hour_id, $limit);

			// если записей нет, то останавливаем выполнение цикла
			if (count($worked_hours_obj_list) == 0) {
				break;
			}

			// получаем последний итем
			$last_worked_hours_item = end($worked_hours_obj_list);

			// получаем id последнего итема для следующего запроса в бд
			$last_worked_hour_id = $last_worked_hours_item->worked_hour_id;

			// добавляем список к тем что получили ранее
			$total_worked_hours_obj_list = array_merge($total_worked_hours_obj_list, $worked_hours_obj_list);
		} while (count($worked_hours_obj_list) == $limit);

		return $total_worked_hours_obj_list;
	}

	/**
	 * Получаем флаг, позволяет ли время редактировать итем рабочего времени
	 */
	public static function isTimeAllowToEdit(int $item_created_at):bool {

		// если с создания записи прошло слишком много времени
		if (time() > $item_created_at + self::_ALLOW_TO_EDIT_TIME) {
			return false;
		}

		return true;
	}

	/**
	 * обновляем dynamic-данные при добавлении рабочих часов
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function updateDynamicDataIfAdd(int $user_id, float $worked_hours):void {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();
			return;
		}

		// получаем total-значение рабочих часов пользователя
		$total_worked_hours_value = Type_User_Card_DynamicData::getTotalWorkedHours($dynamic_obj->data);
		$total_worked_hours_count = Type_User_Card_DynamicData::getTotalWorkedCount($dynamic_obj->data);

		// добавляем рабочие часы
		$total_worked_hours_value = $total_worked_hours_value + $worked_hours;
		$dynamic_obj->data        = Type_User_Card_DynamicData::setWorkedHoursValues($dynamic_obj->data, floatToInt($total_worked_hours_value));

		// инкрементим количество рабочих часов пользователя
		$dynamic_obj->data = Type_User_Card_DynamicData::setWorkedHoursCount($dynamic_obj->data, $total_worked_hours_count + 1);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();
	}

	/**
	 * обновляем dynamic-данные при редактировании рабочих часов
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function updateDynamicDataIfEdit(int $user_id, float $new_value, float $old_value):void {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();
			return;
		}

		// получаем total-значение рабочих часов пользователя
		$total_worked_hours_value = Type_User_Card_DynamicData::getTotalWorkedHours($dynamic_obj->data);

		// в зависимости от нового значения рабочих часов
		if ($new_value > $old_value) {
			$total_worked_hours_value = $total_worked_hours_value + ($new_value - $old_value);
		} else {
			$total_worked_hours_value = $total_worked_hours_value - ($old_value - $new_value);
		}

		$dynamic_obj->data = Type_User_Card_DynamicData::setWorkedHoursValues($dynamic_obj->data, floatToInt($total_worked_hours_value));

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();
	}

	/**
	 * обновляем dynamic-данные при удалении рабочих часов
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function updateDynamicDataIfRemove(int $user_id, float $remove_worked_value):void {

		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();
			return;
		}

		// получаем total-значение рабочих часов пользователя
		$total_worked_hours_value = Type_User_Card_DynamicData::getTotalWorkedHours($dynamic_obj->data);
		$total_worked_hours_count = Type_User_Card_DynamicData::getTotalWorkedCount($dynamic_obj->data);

		// декрементим total-значение рабочих часов
		$total_worked_hours_value = $total_worked_hours_value - $remove_worked_value;
		$total_worked_hours_value = $total_worked_hours_value < 0 ? 0 : $total_worked_hours_value;

		// декрементим количество рабочих часов
		$dynamic_obj->data = Type_User_Card_DynamicData::setWorkedHoursValues($dynamic_obj->data, floatToInt($total_worked_hours_value));
		$dynamic_obj->data = Type_User_Card_DynamicData::setWorkedHoursCount($dynamic_obj->data, $total_worked_hours_count - 1);

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();
	}

	/**
	 * Пересчитываем среднее значение рабочих часов за последние 2 недели
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function recountAvgWorkedHours(int $user_id):void {

		$member_info = Gateway_Bus_CompanyCache::getMember($user_id);

		// получаем все фиксации рабочих часов с даты вступления в компанию
		$worked_hours_obj_list = self::getListByDayStartAt($user_id, dayStart($member_info->created_at), 100 * 1000);

		// высчитываем значение для обновления
		$avg_value = self::_calculateAvgWorkedHours($worked_hours_obj_list);

		// пишем в базу
		Gateway_Db_CompanyMember_UsercardDynamic::beginTransaction();

		// получаем dynamic-данные на обновление
		try {
			$dynamic_obj = Gateway_Db_CompanyMember_UsercardDynamic::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			// если вдруг запись не нашлась
			Gateway_Db_CompanyMember_UsercardDynamic::rollback();
			return;
		}

		// устанавливаем среднее время рабочих часов
		$dynamic_obj->data = Type_User_Card_DynamicData::setAvgWorkedHours($dynamic_obj->data, floatToInt($avg_value));

		// обновляем dynamic-данные
		$set = ["data" => $dynamic_obj->data, "updated_at" => time()];
		Gateway_Db_CompanyMember_UsercardDynamic::set($user_id, $set);

		Gateway_Db_CompanyMember_UsercardDynamic::commitTransaction();
	}

	/**
	 * высчитываем значение среднего времени
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 */
	protected static function _calculateAvgWorkedHours(array $worked_hours_obj_list):float {

		// если рабочие часы у пользователя вообще отсутствуют
		if (count($worked_hours_obj_list) < 1) {
			return 0;
		}

		// получаем подсчет рабочего календаря за все время, кроме текущей недели (сумма отработанных часов + количество недель)
		$worked_data_without_current_week = self::_doCountWorkedDataWithoutCurrentWeek($worked_hours_obj_list);

		// получаем подсчет рабочего календаря за текущую неделю (сумма отработанных часов + количество отработанных дней)
		$worked_data_by_current_week = self::_doCountWorkedDataByCurrentWeek($worked_hours_obj_list);

		// считаем среднее количество отработанных часов
		// как их считать из документа с UC:
		// Среднее время за все время. При подсчете учитывается сумма всех недель + сумма времени за дни текущей недели (до 7 дней включительно), деленная на сумму дней (по 5 в неделю) + количество прошедших дней (до 5 дней включительно).
		// Пример: 30 недель (210 дней), в которых в сумме 400 часов. В текущей неделе прошло 3 дня и 3 раза зафиксировали время по 12 часов.
		// 400 часов за 30 недель + 12 часов х 3 дня с фиксацией времени / (30 недель х 5 дней + 3 дня) = 2,8 часов.
		return bcdiv(($worked_data_without_current_week["total_worked_hours"] + $worked_data_by_current_week["total_worked_hours"])
			/ ($worked_data_without_current_week["total_worked_days"] + $worked_data_by_current_week["total_worked_days"]), 1, 1);
	}

	/**
	 * подсчитываем сумму отработанных часов за все время кроме текущей недели
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 *
	 * @return int[]
	 * @long возможно можно переделать метод
	 */
	protected static function _doCountWorkedDataWithoutCurrentWeek(array $worked_hours_obj_list):array {

		// получаем начало текущей недели
		$current_week_start_at = weekStart(time());

		// бежим по всем записям с фиксацией времени, пока не доберемся до фиксацией за эту неделю
		$total_worked_hours        = 0;
		$total_worked_days_by_week = [];
		foreach ($worked_hours_obj_list as $obj) {

			if ($obj->day_start_at >= $current_week_start_at) {
				break;
			}

			// собираем общую сумму значения рабочих часов
			$total_worked_hours += $obj->float_value;

			// получаем номер недели и года
			$year = yearNum($obj->day_start_at);
			$week = weekNum($obj->day_start_at);

			// если суббота или воскресенье, то не учитываем их как рабочие дни
			if (in_array(date("l", $obj->day_start_at), ["Saturday", "Sunday"])) {
				continue;
			}

			// собираем количество фиксаций за неделю (не более максимума = 5)
			if (isset($total_worked_days_by_week[$year][$week])) {

				if ($total_worked_days_by_week[$year][$week] < self::WORKED_HOURS_DAYS_IN_WEEK) {
					$total_worked_days_by_week[$year][$week] += 1;
				}
			} else {
				$total_worked_days_by_week[$year][$week] = 1;
			}
		}

		$total_worked_days = 0;
		foreach ($total_worked_days_by_week as $v) {
			$total_worked_days += array_sum($v);
		}

		// если работали только в выходные, чтобы не сбилась вычитка средних значений
		if ($total_worked_days == 0 && $total_worked_hours > 0) {
			$total_worked_days = 1;
		}

		return [
			"total_worked_hours" => $total_worked_hours,
			"total_worked_days"  => $total_worked_days,
		];
	}

	/**
	 * подсчитываем рабочий календарь за текущую неделю (сумма отработанных часов + количество отработанных дней)
	 *
	 * @param Struct_Domain_Usercard_WorkedHours[] $worked_hours_obj_list
	 *
	 * @return int[]
	 */
	protected static function _doCountWorkedDataByCurrentWeek(array $worked_hours_obj_list):array {

		// переворачиваем массив чтобы идти от последних записей к первой
		$temp = array_reverse($worked_hours_obj_list);

		// когда началась текущая неделя
		$current_week_start_at = weekStart(time());

		// бежим по всем записям, пока не упираемся в фиксации не за текущую неделю
		$total_worked_hours = 0;
		$total_worked_days  = 0;
		foreach ($temp as $obj) {

			if ($obj->day_start_at < $current_week_start_at) {
				break;
			}

			$total_worked_days  += 1;
			$total_worked_hours += $obj->float_value;
		}

		// общее количество рабочих дней не может превышать 5! так как в неделе 5 рабочих дней, а остальное overwork
		$total_worked_days = min($total_worked_days, 5);

		// если работали только в выходные, чтобы не сбилась вычитка средних значений
		if ($total_worked_days == 0 && $total_worked_hours > 0) {
			$total_worked_days = 1;
		}

		return [
			"total_worked_hours" => $total_worked_hours,
			"total_worked_days"  => $total_worked_days,
		];
	}

	// получаем переменную для высчитывания среднего времени рабочих часов
	public static function getVariableForAvgWorkedHours(int $worked_hours_obj_count, int $current_time, int $join_company_at):int {

		// если отсутствуют рабочие часы, то в этом случае возвращаем коэффициент = 1
		$worked_hours_obj_count = $worked_hours_obj_count == 0 ? 1 : $worked_hours_obj_count;

		// если фиксаций рабочих часов меньше 5, то возвращаем кол-во отработанных часов
		if ($worked_hours_obj_count <= 5) {
			return $worked_hours_obj_count;
		}

		// если фиксаций больше 5, но человек работает только первую неделю
		if (dayStart($current_time) < strtotime("+1 week", $join_company_at)) {
			return self::_DIVISION_COEFFICIENT_OF_WORKED_HOURS / 2;
		}

		// прошло больше недели, но фиксаций не больше 10
		if ($worked_hours_obj_count <= 10) {
			return $worked_hours_obj_count;
		}

		return self::_DIVISION_COEFFICIENT_OF_WORKED_HOURS;
	}

	/**
	 * получаем накопленные сверхчасы
	 */
	public static function getOverTimeHours(array $worked_hours_grouping_by_week):int {

		// по умолчанию считаем что у пользователя сверхчасы равны нулю
		$over_time_hours = 0;

		// собираем сверхчасы пользователя
		// (сверхчасы только те часы, что в неделю накопилось выше 45ч)
		foreach ($worked_hours_grouping_by_week as $data_grouping_by_week) {

			foreach ($data_grouping_by_week as $total_worked_hours_value) {

				if ($total_worked_hours_value > Type_User_Card_WorkedHours::WORKED_HOURS_FOR_WEEK_STANDARD) {
					$over_time_hours += $total_worked_hours_value - Type_User_Card_WorkedHours::WORKED_HOURS_FOR_WEEK_STANDARD;
				}
			}
		}

		// округляем значение накопленных сверхчасов в сторону большего
		return round($over_time_hours);
	}

	##########################################################
	# region DATA VARIABLES
	##########################################################

	protected const _DATA_VERSION = 1; // версия json структуры рабочих часов
	protected const _DATA_SCHEMA  = [

		1 => [
			"fixed_message_map_list" => [], // список ключей сообщений, за которыми закреплена запись рабочих часов
		],
	];

	/**
	 * инициализируем поле data
	 */
	public static function initData():array {

		$data            = self::_DATA_SCHEMA[self::_DATA_VERSION];
		$data["version"] = self::_DATA_VERSION;

		return $data;
	}

	/**
	 * Достаем список ключей сообщений, которые закреплены за записью рабочих часов
	 */
	public static function getFixedMessageMapList(array $data):array {

		$data = self::actualData($data);

		return $data["fixed_message_map_list"];
	}

	/**
	 * Закрепляем список ключей сообщений за записью рабочих часов
	 */
	public static function setFixedMessageMapList(array $data, array $fixed_message_map_list):array {

		$data = self::actualData($data);

		$data["fixed_message_map_list"] = $fixed_message_map_list;

		return $data;
	}

	/**
	 * Получить актуальную структуру data
	 */
	public static function actualData(array $data_schema):array {

		// если версия совпадает
		if (isset($data_schema["version"]) && $data_schema["version"] == self::_DATA_VERSION) {
			return $data_schema;
		}

		// иначе - дополняем её до текущей
		$current_data_schema = self::_DATA_SCHEMA[self::_DATA_VERSION];

		$data_schema            = array_merge($current_data_schema, $data_schema);
		$data_schema["version"] = self::_DATA_VERSION;

		return $data_schema;
	}

	# endregion
	##########################################################
}