<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Скрипт для очистки чатов
 */
class Type_Script_Source_ClearConversations extends Type_Script_CompanyUpdateTemplate {

	/**
	 * Точка входа в скрипт.
	 *
	 * @param array $data
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public function exec(array $data):void {

		// парсим в нужный формат
		$data = self::_parseArrayValues($data);

		// достаем только чаты нужной компании
		$conversation_key_list = $data[COMPANY_ID];

		// проходимся по диалогам компании
		foreach ($conversation_key_list as $conversation_key) {

			if (preg_match("/^\d+:\d+$/", $conversation_key)) {

				[$user_id_1, $user_id_2] = explode(":", $conversation_key);
				$user_id_1        = (int) $user_id_1;
				$user_id_2        = (int) $user_id_2;
				$conversation_map = Type_Conversation_Single::getMapByUsers($user_id_1, $user_id_2);
				if (!$conversation_map) {

					$conversation_map = Type_Conversation_Single::getMapByUsers($user_id_1, $user_id_2);
					if (!$conversation_map) {
						$this->_log("Не смогли найти личный чат между пользователями {$user_id_1} и {$user_id_2}, пропускаем");
						continue;
					}
				}
			} else {

				try {
					$conversation_map = \CompassApp\Pack\Conversation::doDecrypt($conversation_key);
				} catch (\Exception) {

					$company_id = COMPANY_ID;
					$this->_log("Не найден чат {$conversation_key} в компании {$company_id}, пропускаем");
					continue;
				}
			}

			// проверяем что такой диалог есть, если нет - пропускаем
			try {
				$meta_row = Type_Conversation_Meta::get($conversation_map);
			} catch (ParamException) {
			}

			// обновляем время очистки диалога
			$clear_until = time();
			Type_Conversation_Meta::setConversationClearUntilForAll($conversation_map, $clear_until);

			// получаем пользователей состоящих в диалоге
			$user_id_list = [];
			foreach ($meta_row["users"] as $member_user_id => $_) {

				if (Type_Conversation_Meta_Users::isMember($member_user_id, $meta_row["users"])) {
					$user_id_list[] = $member_user_id;
				}
			}

			// обнуляем unread_count, last_message и устанавливаем clear_until в left_menu
			Type_Conversation_LeftMenu::setClearedForUserIdList($user_id_list, $conversation_map, $clear_until);

			// обновляем время очистки диалога для всех пользователей
			$dynamic = Domain_Conversation_Entity_Dynamic::setClearUntilConversationForUserIdList($conversation_map, $user_id_list, $clear_until, true);

			// делаем сокет запрос в модуль php_thread для обновление времени очистки диалога
			Gateway_Socket_Thread::clearConversationForUserIdList($conversation_map, $clear_until, $user_id_list);

			// делим на чанки по 100 пользователей и пушим событие через go_event
			$chunk_user_id_list = array_chunk($user_id_list, 100);
			foreach ($chunk_user_id_list as $user_id_list) {

				Gateway_Event_Dispatcher::dispatch(
					Type_Event_Conversation_ClearConversationForUsers::create($conversation_map, $user_id_list, $dynamic->messages_updated_version)
				);
			}

			// удаляем все данные диалога в поисковом индексе
			Domain_Search_Entity_Conversation_Task_Purge::queue($conversation_map);
		}
	}

	/**
	 * Преобразует массив со строками вида "[value1;value2]" в массив строк
	 */
	private static function _parseArrayValues(array $input):array {

		$output = [];
		foreach ($input as $key => $value) {

			// извлекаем значения между квадратными скобками
			if (preg_match("/\[(.*?)\]/", $value, $matches)) {

				// разбиваем строку по точке с запятой
				$values = explode(";", $matches[1]);

				// очищаем значения от пробелов
				$values       = array_map("trim", $values);
				$output[$key] = $values;
			}
		}

		return $output;
	}
}