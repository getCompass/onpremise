<?php

/** @noinspection PhpUnused */

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;
use CompassApp\Domain\Member\Entity\Member;
use cs_RowIsEmpty;
use parseException;

/**
 * Класс обработки сценариев событий premise.
 */
class Domain_Premise_Scenario_Event {

	/**
	 * Событие удаления профиля пользователя.
	 *
	 * @param Struct_Event_Premise_UserProfileDeleted $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws parseException
	 * @throws ParseFatalException
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Premise_UserProfileDeleted::EVENT_TYPE, Struct_Event_Premise_UserProfileDeleted::class)]
	public static function userProfileDeleted(Struct_Event_Premise_UserProfileDeleted $event_data):Type_Task_Struct_Response {

		// удаляет записи пространств для пользователя
		Gateway_Db_PremiseUser_SpaceList::deleteAllByUserId($event_data->user_id);

		// удаляем запись пользователя
		Gateway_Db_PremiseUser_UserList::delete($event_data->user_id);

		// пересчитываем актуальное количество пользователей
		Domain_Premise_Action_RecountSpaceCounters::do();

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}

	/**
	 * Событие вступления участника в команду.
	 *
	 * @param Struct_Event_Premise_SpaceNewMember $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws ParseFatalException
	 * @throws \queryException
	 * @throws parseException
	 * @long
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Premise_SpaceNewMember::EVENT_TYPE, Struct_Event_Premise_SpaceNewMember::class)]
	public static function spaceNewMember(Struct_Event_Premise_SpaceNewMember $event_data):Type_Task_Struct_Response {

		// пробуем получить связи пользователя с другими пространствами
		// получаем заранее до инсёрта связи пользователя во вступившее пространство
		$before_space_list = Gateway_Db_PremiseUser_SpaceList::getByUser($event_data->user_id);

		// создаём связь между участником и пространством, в которое тот вступил
		$user_space = new Struct_Db_PremiseUser_Space(
			$event_data->user_id, $event_data->space_id, $event_data->role, $event_data->permissions, time(), 0, []
		);
		Gateway_Db_PremiseUser_SpaceList::insert($user_space);

		// если вступил бот, и ранее не имел связей с командами
		/** @noinspection PhpFullyQualifiedNameUsageInspection */
		if (count($before_space_list) == 0 && \CompassApp\Domain\User\Main::isBot($event_data->npc_type)) {

			// обновляем статус на "уникальный бот"
			Domain_Premise_Action_UpdateSpaceStatus::do($event_data->user_id, Domain_Premise_Entity_Space::UNIQUE_BOT_SPACE_STATUS);
		}

		// если вступил человек
		/** @noinspection PhpFullyQualifiedNameUsageInspection */
		if (\CompassApp\Domain\User\Main::isHuman($event_data->npc_type)) {

			// если у пользователя совсем отсутствуют связи с какой-либо компанией
			if (count($before_space_list) == 0) {

				// обновляем статус на "уникальный участник" или "уникальный гость" в зависимости от роли пользователя
				$space_status = match ($event_data->role) {
					Member::ROLE_GUEST => Domain_Premise_Entity_Space::UNIQUE_GUEST_SPACE_STATUS,
					default => Domain_Premise_Entity_Space::UNIQUE_MEMBER_SPACE_STATUS,
				};
				Domain_Premise_Action_UpdateSpaceStatus::do($event_data->user_id, $space_status);
			} else {

				// пробуем получить связи пользователя с компаниями, где тот имеет роль "Участник"
				$space_member_list = array_filter(
					$before_space_list, static fn(Struct_Db_PremiseUser_Space $space) => $space->role_alias == Member::ROLE_MEMBER
				);

				// если ранее нигде не числился участником и теперь имеет роль "Участник", то обновляем space_status у пользователя на "уникальный участник"
				// в остальном тот числится как "уникальный гость" - апдейт не требуется
				if (count($space_member_list) == 0 && $event_data->role == Member::ROLE_MEMBER) {
					Domain_Premise_Action_UpdateSpaceStatus::do($event_data->user_id, Domain_Premise_Entity_Space::UNIQUE_MEMBER_SPACE_STATUS);
				}
			}

			// пересчитываем актуальное количество пользователей
			Domain_Premise_Action_RecountSpaceCounters::do();
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}

	/**
	 * Событие покидания участником команды.
	 *
	 * @param Struct_Event_Premise_SpaceLeftMember $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws parseException
	 * @throws ParseFatalException
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Premise_SpaceLeftMember::EVENT_TYPE, Struct_Event_Premise_SpaceLeftMember::class)]
	public static function spaceLeftMember(Struct_Event_Premise_SpaceLeftMember $event_data):Type_Task_Struct_Response {

		// удаляем связь между участником и командой
		Gateway_Db_PremiseUser_SpaceList::delete($event_data->user_id, $event_data->space_id);

		// пробуем найти связь между участником и остальными командами
		$space_list = Gateway_Db_PremiseUser_SpaceList::getByUser($event_data->user_id);

		// если связей нет, то помечаем, что пользователь не присоединён ни к одной команде
		if (count($space_list) == 0) {
			Domain_Premise_Action_UpdateSpaceStatus::do($event_data->user_id, Domain_Premise_Entity_Space::NOT_EXIST_SPACE_STATUS);
		}

		// получаем из записей те, где пользователь является гостем
		$space_guest_list = array_filter(
			$space_list, static fn(Struct_Db_PremiseUser_Space $space) => $space->role_alias == Member::ROLE_GUEST
		);

		// если остались только гостевые записи
		if (count($space_list) > 0 && count($space_guest_list) == count($space_list)) {
			Domain_Premise_Action_UpdateSpaceStatus::do($event_data->user_id, Domain_Premise_Entity_Space::UNIQUE_GUEST_SPACE_STATUS);
		}

		// пересчитываем актуальное количество пользователей
		Domain_Premise_Action_RecountSpaceCounters::do();

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}

	/**
	 * Событие изменения роли/прав пользователя в команде.
	 *
	 * @param Struct_Event_Premise_SpaceChangedMember $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws parseException
	 * @throws ParseFatalException
	 * @throws cs_RowIsEmpty
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Premise_SpaceChangedMember::EVENT_TYPE, Struct_Event_Premise_SpaceChangedMember::class)]
	public static function spaceChangedMember(Struct_Event_Premise_SpaceChangedMember $event_data):Type_Task_Struct_Response {

		// получаем текущие роль пользователя в связи с пространством
		try {

			$user_space        = Gateway_Db_PremiseUser_SpaceList::getOne($event_data->user_id, $event_data->space_id);
			$before_role_alias = $user_space->role_alias;
		} catch (cs_RowIsEmpty $e) {

			// тот редкий случай, когда в ci сразу изменение и удаление пользователя из пространства
			// из-за чего можем словить, что запись будет отсутствовать
			if (!ServerProvider::isCi()) {
				throw $e;
			}

			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// меняем роль/права пользователя в связи с пространством
		$set = [
			"role_alias"        => $event_data->role,
			"permissions_alias" => $event_data->permissions,
			"updated_at"        => time(),
		];
		Gateway_Db_PremiseUser_SpaceList::set($event_data->user_id, $event_data->space_id, $set);

		// если ранее был гостем, но стал участником,
		// то помечаем его "уникальным участником" и пересчитываем количество "уникальных участников/гостей"
		if ($before_role_alias == Member::ROLE_GUEST && $event_data->role == Member::ROLE_MEMBER) {

			Domain_Premise_Action_UpdateSpaceStatus::do($event_data->user_id, Domain_Premise_Entity_Space::UNIQUE_MEMBER_SPACE_STATUS);

			// пересчитываем актуальное количество пользователей
			Domain_Premise_Action_RecountSpaceCounters::do();
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}