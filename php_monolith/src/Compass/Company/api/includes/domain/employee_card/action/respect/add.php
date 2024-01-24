<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Базовый класс для добавления респекта в карточку сотрудника компании
 */
class Domain_EmployeeCard_Action_Respect_Add {

	/**
	 * выполняем действие добавления респекта в карточку сотрудника компании
	 *
	 * @param int      $user_id
	 * @param int      $receiver_user_id
	 * @param string   $respect_text
	 * @param int|null $created_at
	 *
	 * @return Struct_Domain_Usercard_Respect
	 * @throws Domain_EmployeeCard_Exception_Respect_NotConversationMember
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public static function do(int $user_id, int $receiver_user_id, string $respect_text, int $created_at = null):Struct_Domain_Usercard_Respect {

		if (is_null($created_at)) {
			$created_at = time();
		}

		// добавляем запись о новом респекте в таблицу с респектами
		$respect = Type_User_Card_Respect::add($receiver_user_id, $user_id, Type_User_Card_Respect::RESPECT_TYPE_DEFAULT, $respect_text, $created_at);

		// респект в чат
		$message_map = "";
		try {
			$message_map = Gateway_Socket_Conversation::addRespectToConversation($user_id, $receiver_user_id, $respect->respect_id, $respect_text);
		} catch (Domain_EmployeeCard_Exception_Respect_NotConversationMember $e) {

			// откатываем изменения и ругаемся
			Type_User_Card_Respect::delete($receiver_user_id, $respect->respect_id);
			throw $e;
		} catch (\Exception $e) {

			// если вернулся returnException, то откатываем изменения и ругаемся, в остальных случаях - пропускаем дальше
			if ($e instanceof ReturnFatalException) {

				Type_User_Card_Respect::delete($receiver_user_id, $respect->respect_id);
				throw new ReturnFatalException("Dont created message-respect in" . __METHOD__);
			}
		}

		$respect->data = Type_User_Card_Respect::setMessageMap($respect->data, $message_map);

		// сохраняем message_map добавленного сообщения
		$set["data"] = $respect->data;
		Type_User_Card_Respect::set($receiver_user_id, $respect->respect_id, $set);

		// инкрементим количество отправленных благодарностей в рейтинге
		Gateway_Bus_Company_Rating::inc(Domain_Rating_Entity_Rating::RESPECT, $user_id);

		// обновляем количество добавленных респектов
		$month_start_at = monthStart($created_at);
		Type_User_Card_MonthPlan::incUserValue($user_id, Type_User_Card_MonthPlan::MONTH_PLAN_RESPECT_TYPE, $month_start_at);

		// инкрементим количество полученных пользователем респектов
		Type_User_Card_Respect::incInDynamicData($receiver_user_id);

		// отправляем ивент о том, что пользователь получил благодарность
		if (!isEmptyString($message_map)) {
			Gateway_Event_Dispatcher::dispatch(Type_Event_Member_OnUserReceivedEmployeeCardEntity::create(
				"respect", $message_map, $user_id, $receiver_user_id, 0, 0), true);
		}

		// парсим ссылки с тексте
		if (mb_strlen($respect_text) > 0) {

			Gateway_Socket_Conversation::getLinkListFromText($respect_text, $receiver_user_id, $user_id,
				EMPLOYEE_CARD_ENTITY_TYPE_RESPECT, $respect->respect_id);
		}

		return $respect;
	}
}
