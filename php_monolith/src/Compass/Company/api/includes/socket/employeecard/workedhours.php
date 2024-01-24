<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * группа socket-методов для работы с сущностью фиксирующей рабочее время сотрудника
 */
class Socket_Employeecard_WorkedHours extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"doCommit",
		"doAppendFixedMessageMap",
		"tryDelete",
		"getWorkedHoursById",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * пытаемся зафиксировать время
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function doCommit():array {

		$worked_hours   = $this->post(\Formatter::TYPE_FLOAT, "worked_hours");
		$day_start_at   = $this->post(\Formatter::TYPE_INT, "day_start_at", dayStart());
		$is_auto_commit = $this->post(\Formatter::TYPE_INT, "is_auto_commit", 0) == 1;

		// получаем тип устанавливаемых рабочих часов
		$worked_hours_type = $is_auto_commit ? Type_User_Card_WorkedHours::WORKED_HOURS_SYSTEM_TYPE : Type_User_Card_WorkedHours::WORKED_HOURS_USER_TYPE;

		// фиксируем за тот день что передан
		$commit_for_day_start_at = $day_start_at;

		// высчитываем разницу между текущим временем и временем начала дня, за какое фиксировать рабочие часы
		$temp = time() - $day_start_at;

		// если зафиксировали не позже 04:00 утра текущего дня
		if (HOUR4 > $temp) {

			// узнаем dayStart прошлого дня
			// вычитаю HOUR1 наверняка :grin:
			$current_day_start_at    = dayStart();
			$commit_for_day_start_at = dayStart($current_day_start_at - HOUR1);
		}

		// фиксируем время в запись
		$worked_hours_obj = Type_User_Card_WorkedHours::doCommit($this->user_id, $commit_for_day_start_at, $worked_hours, $worked_hours_type);

		// обязательно обнуляем is_deleted и устанавливаем актуальный created_at и worked_hours в том случае, если запись была ранее удалена
		// на тот случай, если пользователь проделал следующие шаги:
		// 1. зафиксировал время
		// 2. удалил полностью фиксацию
		// 3. фиксирует снова
		if ($worked_hours_obj->is_deleted == 1) {

			$worked_hours_obj->is_deleted  = 0;
			$worked_hours_obj->created_at  = time();
			$worked_hours_obj->float_value = $worked_hours;
			$set                           = [
				"is_deleted" => 0,
				"type"       => $worked_hours_type,
				"created_at" => $worked_hours_obj->created_at,
				"updated_at" => 0,
				"value_1000" => floatToInt($worked_hours),
			];
			Type_User_Card_WorkedHours::set($worked_hours_obj->worked_hour_id, $set);

			// обновляем dynamic-данные при добавлении рабочих часов
			Type_User_Card_WorkedHours::updateDynamicDataIfAdd($this->user_id, $worked_hours);
		}

		// пересчитываем среднее количество рабочих часов за последние недели
		Type_User_Card_WorkedHours::recountAvgWorkedHours($this->user_id);

		// получаем за какой день закомитили
		$commit_for_day_start_at_iso = convertToISO88601YmD($commit_for_day_start_at);

		return $this->ok([
			"worked_hours_id"         => (int) $worked_hours_obj->worked_hour_id,
			"worked_hours_created_at" => (int) $worked_hours_obj->created_at,
			"day_start_at_iso"        => (string) $commit_for_day_start_at_iso,
		]);
	}

	/**
	 * зафиксировать в объекте worked_hours fixed_message_map_list закрепленных сообщений
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function doAppendFixedMessageMap():array {

		$fixed_message_map_list = $this->post("?a", "message_map_list");
		$worked_hour_id         = $this->post("?i", "worked_hours_id");

		// фиксируем за объектом закрепленные сообщения
		Type_User_Card_WorkedHours::doAppendFixedMessageMap($worked_hour_id, $fixed_message_map_list);

		return $this->ok();
	}

	/**
	 * попытаться удалить объект с зафиксированным временем
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function tryDelete():array {

		$deleted_fixed_message_map_list = $this->post(\Formatter::TYPE_ARRAY, "message_map_list");
		$worked_hour_id                 = $this->post(\Formatter::TYPE_INT, "worked_hours_id");

		// получаем объект и делаем проверки
		try {
			$object = Type_User_Card_WorkedHours::getOne($worked_hour_id);
		} catch (\cs_RowIsEmpty) {

			return $this->ok([
				"is_deleted" => (int) 1,
			]);
		}

		if ($object->user_id != $this->user_id) {
			throw new ParseFatalException(__METHOD__ . ": unexpected behavior");
		}

		// если уже помечен удаленным
		if ($object->is_deleted == 1) {

			return $this->ok([
				"is_deleted" => (int) 1,
			]);
		}

		// попытаться удалить объект
		$object = Type_User_Card_WorkedHours::tryDelete($worked_hour_id, $deleted_fixed_message_map_list);

		// если объект удалили
		if ($object->is_deleted == 1) {

			// обновляем dynamic-данные при удалении рабочих часов
			Type_User_Card_WorkedHours::updateDynamicDataIfRemove($this->user_id, $object->float_value);
		}

		// пересчитываем среднее количество рабочих часов за последние недели
		Type_User_Card_WorkedHours::recountAvgWorkedHours($this->user_id);

		return $this->ok([
			"is_deleted" => (int) $object->is_deleted,
		]);
	}

	// получить объект worked_hours по его идентификатору
	public function getWorkedHoursById():array {

		$worked_hour_id = $this->post("?i", "worked_hours_id");

		// получаем объект
		$worked_hours = Type_User_Card_WorkedHours::getOne($worked_hour_id);
		if (is_null($worked_hours->user_id)) {
			throw new ParamException("worked hours object not found");
		}

		// получаем за какой день закомитили
		$commit_for_day_start_at_iso = convertToISO88601YmD($worked_hours->day_start_at);

		return $this->ok([
			"worked_hours_data" => (object) $worked_hours,
			"day_start_at_iso"  => (string) $commit_for_day_start_at_iso,
		]);
	}
}
