<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Базовый класс для добавления достижения в карточку сотрудника компании
 */
class Domain_EmployeeCard_Action_Achievement_Add {

	/**
	 * выполняем действие добавления достижения в карточку сотрудника компании
	 *
	 * @param int    $sender_user_id
	 * @param int    $receiver_user_id
	 * @param string $header
	 * @param string $description
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public static function do(int $sender_user_id, int $receiver_user_id, string $header, string $description):array {

		// добавляем достижение
		$achievement = Type_User_Card_Achievement::add($receiver_user_id, $sender_user_id, Type_User_Card_Achievement::ACHIEVEMENT_TYPE_DEFAULT, $header, $description);

		// добавляем достижение в группу Достижения
		$message_map = "";
		try {

			$message_map = Gateway_Socket_Conversation::addAchievementToConversation(
				$sender_user_id,
				$receiver_user_id,
				$achievement->achievement_id,
				$achievement->header_text,
				$achievement->description_text
			);
		} catch (\Exception $e) {

			// если вернулся returnException, то отмечаем достижение удаленным
			if ($e instanceof ReturnFatalException) {

				Type_User_Card_Achievement::delete($receiver_user_id, $achievement->achievement_id);
				throw new ReturnFatalException("Dont created message-achievement in" . __METHOD__);
			}
		}

		$achievement->data = Type_User_Card_Achievement::setMessageMap($achievement->data, $message_map);

		// сохраняем message_map добавленного сообщения
		$set["data"] = $achievement->data;
		Type_User_Card_Achievement::set($receiver_user_id, $achievement->achievement_id, $set);

		// отправляем ивент о том, что пользователь получил достижение
		if (!isEmptyString($message_map)) {
			Gateway_Event_Dispatcher::dispatch(Type_Event_Member_OnUserReceivedEmployeeCardEntity::create(
				"achievement", $message_map, $sender_user_id, $receiver_user_id, 0, 0), true);
		}

		// парсим ссылки в тексте
		Gateway_Socket_Conversation::getLinkListFromText($description, $receiver_user_id, $sender_user_id,
			EMPLOYEE_CARD_ENTITY_TYPE_ACHIEVEMENT, $achievement->achievement_id);

		// инкрементим значение достижений
		$card_dynamic_obj  = Type_User_Card_Achievement::incInDynamicData($receiver_user_id);
		$achievement_count = Type_User_Card_DynamicData::getAchievementCount($card_dynamic_obj->data);

		return [$achievement, $achievement_count];
	}
}
