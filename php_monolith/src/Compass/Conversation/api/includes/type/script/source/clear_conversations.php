<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\ParamException;

/**
 * Скрипт для очистки чатов
 */
class Type_Script_Source_ClearConversations extends Type_Script_CompanyUpdateTemplate
{
	protected const _CONVERSATIONS_COUNT = 1000;

	/**
	 * Точка входа в скрипт.
	 *
	 * @long
	 */
	public function exec(array $data): void
	{

		// парсим в нужный формат
		$data = $this->_parseArrayValues($data);

		// достаем данные нужной компании
		$company_data = $data[COMPANY_ID];

		// получаем мап диалогов из указанных ключей
		$conversation_map_list = $this->_getConversationsMapList($company_data["keys"] ?? $company_data);

		// если в keys были переданы диалоги, но получили пустой список мап,
		// то останавливаем выполнение, потому что указанные диалоги не были найдены
		if (isset($company_data["keys"]) && count($company_data["keys"]) > 0 && count($conversation_map_list) < 1) {

			$this->_log("Не нашли указанные чаты - останавливаем очистку.");
			return;
		}

		// если переданы в формате "keys:[],clear_by:..."
		if (isset($company_data["clear_by"])) {
			$this->_clearConversationsByClearTill($conversation_map_list, $company_data["clear_by"]);
		} else {
			$this->_clearConversations($conversation_map_list);
		}
	}

	/**
	 * Преобразует массив со строками вида "[value1;value2]" в массив строк
	 * @long
	 */
	protected function _parseArrayValues(array $input): array
	{

		$output = [];

		foreach ($input as $key => $value) {

			$value = trim($value);

			// если значение начинается с { (структура с clear_by)
			if (str_starts_with($value, "{") && str_contains($value, "}")) {

				// ищем поле keys
				if (preg_match("/keys:\s*\[(.*?)\]/", $value, $keysMatch)) {

					$keys = $keysMatch[1] ? explode(";", $keysMatch[1]) : [];
					$keys = array_map("trim", $keys);
				} else {
					$keys = [];
				}

				// ищем поле clear_by
				$clear_by = "";
				if (preg_match("/clear_by:\s*([^}]+)/", $value, $clearMatch)) {
					$clear_by = trim($clearMatch[1]);
				}

				$output[$key] = [
					"keys"     => $keys,
					"clear_by" => $clear_by
				];
			} elseif (preg_match("/^\[(.*)\]$/", $value, $matches)) { // если значение начинается с [ (простой список)

				$items        = $matches[1] ? explode(";", $matches[1]) : [];
				$items        = array_map("trim", $items);
				$output[$key] = $items;
			} else {

				$items        = explode(";", $value);
				$items        = array_map("trim", $items);
				$output[$key] = $items;
			}
		}

		return $output;
	}

	/**
	 * Получаем список мап из ключей диалогов
	 * @long
	 */
	protected function _getConversationsMapList(array $conversation_key_list): array
	{

		$conversation_map_list = [];
		foreach ($conversation_key_list as $conversation_key) {

			if (preg_match("/^\d+:\d+$/", $conversation_key)) {

				[$user_id_1, $user_id_2] = explode(":", $conversation_key);
				$user_id_1               = (int)$user_id_1;
				$user_id_2               = (int)$user_id_2;
				$conversation_map        = Type_Conversation_Single::getMapByUsers($user_id_1, $user_id_2);
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
			$conversation_map_list[] = $conversation_map;
		}

		return $conversation_map_list;
	}

	/**
	 * Очищаем диалоги по указанное время clear_by
	 * @long
	 */
	protected function _clearConversationsByClearTill(array $conversation_map_list, string $clear_by): void
	{

		$clear_by = trim($clear_by);
		if ($clear_by != "") {

			$clear_by = (mb_strpos($clear_by, "-") !== 0 ? "-" : "") . preg_replace("/^-\s+/", "-", ltrim($clear_by, "+"));

			// преобразуем формат clear_by аля "-90 day" в timestamp метку
			$clear_until = strtotime($clear_by);

			if ($clear_until > time() || $clear_until <= 0) {

				$this->_log("Получили некорректное временную метку очистки - {$clear_until}. Завершаем!");
				return;
			}
		} else {
			$clear_until = time();
		}

		$conversations_count = count($conversation_map_list);
		$this->_log("Получили временную метку очистки - {$clear_until}. Начинаем очистку сообщений для " . ($conversations_count < 1 ? "всех чатов" : "{$conversations_count} чатов"));

		// если конкретные диалоги не указаны, то очищаем все диалоги
		if ($conversations_count < 1) {

			$offset = 0;
			do {

				$conversation_dynamic_list = $this->_getDynamicConversations(self::_CONVERSATIONS_COUNT, $offset);
				$conversation_map_list     = array_column($conversation_dynamic_list, "conversation_map");

				// так как диалогов может быть много, то отправляем асинхронную очистку для диалогов
				$this->_clearAsyncConversations($conversation_map_list, $clear_until);

				$offset += self::_CONVERSATIONS_COUNT;
			} while (count($conversation_dynamic_list) == self::_CONVERSATIONS_COUNT);
		} else {
			$this->_clearAsyncConversations($conversation_map_list, $clear_until);
		}
	}

	/**
	 * Получаем dynamic данные чатов
	 *
	 * @return Struct_Db_CompanyConversation_ConversationDynamic[]
	 */
	protected function _getDynamicConversations(int $limit, int $offset): array
	{
		return Gateway_Db_CompanyConversation_ConversationDynamic::getOrdered($limit, $offset);
	}

	/**
	 * Очищаем указанные диалоги
	 * @long
	 */
	protected function _clearConversations(array $conversation_map_list): void
	{

		// проходимся по диалогам компании
		foreach ($conversation_map_list as $conversation_map) {

			// проверяем что такой диалог есть, если нет - пропускаем
			try {
				$meta_row = Type_Conversation_Meta::get($conversation_map);
			} catch (ParamException) {
				continue;
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
	 * Так как диалогов может быть много, то отправляем асинхронную очистку для диалогов
	 */
	protected function _clearAsyncConversations(array $conversation_map_list, int $clear_until): void
	{

		$conversations_count = count($conversation_map_list);
		if ($conversations_count < 1) {
			return;
		}

		// список чатов может быть большой, поэтому делим весь список на чанки
		$chunk_conversation_map_list = $conversations_count <= 300 ? [$conversation_map_list]
			: array_chunk($conversation_map_list, ceil($conversations_count / 3));

		foreach ($chunk_conversation_map_list as $conversation_map_list) {

			Gateway_Event_Dispatcher::dispatch(
				Type_Event_Conversation_AsyncSourceClearConversations::create($conversation_map_list, $clear_until)
			);
		}
	}
}
