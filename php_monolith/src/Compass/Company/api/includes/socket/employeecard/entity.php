<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * группа socket-методов для работы c данными карточки пользователя
 */
class Socket_EmployeeCard_Entity extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"addExactingness",
		"setMessageMapListForExactingnessList",
		"deleteCardEntityList",
		"attachLinkListToEntity",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	// добавляем Требовательность
	public function addExactingness():array {

		$user_id_list = $this->post("?a", "user_id_list");
		$created_at   = $this->post("?i", "created_at", time());

		// если пришло что-то некорректное
		if (count($user_id_list) < 1 || $created_at < 1) {
			throw new ParamException("incorrect params");
		}

		[$exactingness_id_list_by_user_id, $week_count, $month_count] = Domain_EmployeeCard_Action_Exactingness_Add::do($this->user_id, $user_id_list, $created_at);

		return $this->ok([
			"exactingness_id_list_by_user_id" => (array) $exactingness_id_list_by_user_id,
			"week_count"                      => (int) $week_count,
			"month_count"                     => (int) $month_count,
		]);
	}

	// устанавливаем message_map для списка требовательностей
	public function setMessageMapListForExactingnessList():array {

		$exactingness_data_list_by_user_id = $this->post("?a", "exactingness_data_list_by_user_id");

		// если пришли некорректные параметры
		if (count($exactingness_data_list_by_user_id) < 1) {
			throw new ParamException("incorrect params");
		}

		// собираем список id требовательностей, сгруппированный по user_id
		$exactingness_id_list_grouping_by_user_id = [];
		foreach ($exactingness_data_list_by_user_id as $user_id => $exactingness_data_list) {

			foreach ($exactingness_data_list as $exactingness_id => $message_map) {
				$exactingness_id_list_grouping_by_user_id[$user_id][] = $exactingness_id;
			}
		}

		// работаем с полученным списком
		foreach ($exactingness_id_list_grouping_by_user_id as $user_id => $exactingness_id_list) {

			// получаем список всех требовательностей для каждого из пользоателей
			$exactingness_list = Type_User_Card_Exactingness::getListByIdList($exactingness_id_list);

			// для каждой требовательности сохраняем message_map сообщения, за которым она закреплена
			foreach ($exactingness_list as $v) {

				// достаем message_map из полученных в параметре данных
				$message_map = $exactingness_data_list_by_user_id[$user_id][$v->exactingness_id];

				// сохраняем message_map
				$v->data["message_map"] = $message_map;
				$set                    = ["data" => $v->data];
				Type_User_Card_Exactingness::set($user_id, $v->exactingness_id, $set);
			}
		}

		return $this->ok();
	}

	// удаляем сущности карточки
	public function deleteCardEntityList():array {

		$entity_type                   = $this->post(\Formatter::TYPE_STRING, "entity_type");
		$entity_id_list_by_receiver_id = $this->post(\Formatter::TYPE_ARRAY, "entity_id_list_by_receiver_id");

		// в зависимости от типа сущности
		switch ($entity_type) {

			case "respect": // респект
				$this->_doActionForRespects($entity_id_list_by_receiver_id);
				break;

			case "exactingness": // требовательность
				$this->_doActionForExactingness($entity_id_list_by_receiver_id);
				break;

			case "achievement": // достижение
				$this->_doActionForAchievement($entity_id_list_by_receiver_id);
				break;

			default:
				throw new ParseFatalException("unknown entity type");
		}

		return $this->ok();
	}

	// действия для списка респектов
	protected function _doActionForRespects(array $entity_id_list_by_receiver_id):void {

		foreach ($entity_id_list_by_receiver_id as $_ => $respect_id_list) {

			// получаем список всех респектов, полученных пользователем
			$respect_list = Type_User_Card_Respect::getListByIdList($respect_id_list);

			// обрабатываем полученные респекты
			foreach ($respect_list as $respect) {

				// если респект уже помечен удаленным
				if ($respect->is_deleted == 1) {
					continue;
				}

				// помечаем респект удаленным
				Type_User_Card_Respect::delete($respect->user_id, $respect->respect_id);

				// декрементим количество набранных за месяц
				$month_start_at = monthStart($respect->created_at);
				Type_User_Card_MonthPlan::decUserValue($respect->creator_user_id, Type_User_Card_MonthPlan::MONTH_PLAN_RESPECT_TYPE, $month_start_at);

				// декрементим значение полученных респектов
				Type_User_Card_Respect::decInDynamicData($respect->user_id);

				// декрементим рейтинг после удаления респекта
				Gateway_Bus_Company_Rating::decAfterDelete(Domain_Rating_Entity_Rating::RESPECT, $respect->creator_user_id, $respect->created_at);
			}
		}
	}

	// действия для списка требовательностей
	protected function _doActionForExactingness(array $entity_id_list_by_receiver_id):void {

		foreach ($entity_id_list_by_receiver_id as $receiver_user_id => $exactingness_id_list) {

			// получаем список всех требовательностей, полученных пользователем
			$exactingness_list = Type_User_Card_Exactingness::getListByIdList($exactingness_id_list);

			// достаем id полученных из базы требовательностей
			$exactingness_id_list = array_column($exactingness_list, "exactingness_id");

			// помечаем требовательности удаленными
			$set = ["is_deleted" => 1];
			Type_User_Card_Exactingness::setByIdList($receiver_user_id, $exactingness_id_list, $set);

			// декрементим количество выданных требовательностей у создателей требовательностей
			foreach ($exactingness_list as $exactingness) {

				$month_start_at = monthStart($exactingness->created_at);
				Type_User_Card_MonthPlan::decUserValue(
					$exactingness->creator_user_id, Type_User_Card_MonthPlan::MONTH_PLAN_EXACTINGNESS_TYPE, $month_start_at
				);

				// декрементим рейтинг для типа требовательности
				Gateway_Bus_Company_Rating::decAfterDelete(Domain_Rating_Entity_Rating::EXACTINGNESS, $exactingness->creator_user_id, $exactingness->created_at);
			}
		}
	}

	// действия для списка достижений
	protected function _doActionForAchievement(array $entity_id_list_by_receiver_id):void {

		foreach ($entity_id_list_by_receiver_id as $receiver_user_id => $achievement_id_list) {

			// получаем список достижений по их id
			$achievement_list = Type_User_Card_Achievement::getListByIdList($achievement_id_list);

			// достаем id полученных из базы достижений
			$achievement_id_list = array_column($achievement_list, "achievement_id");

			// помечаем достижения удаленными
			$set = ["is_deleted" => 1];
			Type_User_Card_Achievement::setByIdList($receiver_user_id, $achievement_id_list, $set);

			// декрементим значение полученных достижений для каждого получателя
			foreach ($achievement_list as $achievement) {
				Type_User_Card_Achievement::decInDynamicData($achievement->user_id);
			}
		}
	}

	// крепим link_list к нужной сущности
	// @long - switch
	public function attachLinkListToEntity():array {

		$link_list        = $this->post(\Formatter::TYPE_ARRAY, "link_list");
		$opposite_user_id = $this->post(\Formatter::TYPE_INT, "opposite_user_id");
		$user_list        = $this->post(\Formatter::TYPE_ARRAY, "user_list");
		$entity_type      = $this->post(\Formatter::TYPE_INT, "entity_type");
		$entity_id        = $this->post(\Formatter::TYPE_INT, "entity_id");

		// пишем в нужную баз в зависимости от типа сущности
		switch ($entity_type) {

			case EMPLOYEE_CARD_ENTITY_TYPE_ACHIEVEMENT:

				$achievement = Type_User_Card_Achievement::get($opposite_user_id, $entity_id);
				$data        = Type_User_Card_Achievement::setLinkList($achievement->data, $link_list);
				$set         = ["data" => $data];
				Type_User_Card_Achievement::set($opposite_user_id, $entity_id, $set);
				break;

			case EMPLOYEE_CARD_ENTITY_TYPE_RESPECT:

				$respect = Type_User_Card_Respect::get($opposite_user_id, $entity_id);

				$respect->data = Type_User_Card_Respect::setLinkList($respect->data, $link_list);
				$set           = [
					"data" => toJson($respect->data),
				];
				Type_User_Card_Respect::set($opposite_user_id, $entity_id, $set);
				break;

			case EMPLOYEE_CARD_ENTITY_TYPE_SPRINT:

				$sprint = Type_User_Card_Sprint::get($opposite_user_id, $entity_id);

				$sprint->data = Type_User_Card_Sprint::setLinkList($sprint->data, $link_list);
				$set          = [
					"data" => toJson($sprint->data),
				];

				Type_User_Card_Sprint::set($opposite_user_id, $entity_id, $set);
				break;

			case EMPLOYEE_CARD_ENTITY_TYPE_LOYALTY:

				Type_User_Card_Loyalty::updateLinkList($opposite_user_id, $entity_id, $link_list);
				break;

			default:
				throw new ParseFatalException("Unknown employee card entity type");
		}

		// шлем эвент о смене ссылки
		Gateway_Bus_Sender::employeeCardLinkDataChanged($user_list, $link_list, $entity_id, $entity_type);
		return $this->ok();
	}
}
