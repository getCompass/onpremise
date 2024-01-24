<?php

namespace Compass\Thread;

/*
 * -------------------------------------------------------
 * класс для работы с полем last_sender_data в thread_meta
 * -------------------------------------------------------
 *
 * структура last_sender_data
 * array
 * (
 *     [$thread_message_index] => $sender_user_id
 * )
 *
*/

class Type_Thread_Meta_LastSenderData {

	// лимит последних сообщений в last_sender_data
	public const MAX_LAST_SENDER_DATA_COUNT = 200;

	// добавляем новый элемент в last_sender_data
	public static function addNewItem(array $last_sender_data, int $thread_message_index, int $user_id):array {

		// добавляем элемент в массив
		$last_sender_data[$thread_message_index] = $user_id;

		// если оказалось элементов > разрешенного лимита - подчищаем
		if (count($last_sender_data) > self::MAX_LAST_SENDER_DATA_COUNT) {

			// берем ключи массива, сортируем по возрастанию
			$keys = array_keys($last_sender_data);
			sort($keys);

			// оставляем те ключи, которые надо удалить
			$keys = array_slice($keys, 0, -self::MAX_LAST_SENDER_DATA_COUNT);

			// удаляем
			foreach ($keys as $v) {
				unset($last_sender_data[$v]);
			}
		}
		return $last_sender_data;
	}

	// удаляем одно сообщение из last_sender_data
	public static function removeItem(array $last_sender_data, int $thread_message_index):array {

		if (!isset($last_sender_data[$thread_message_index])) {
			return $last_sender_data;
		}

		unset($last_sender_data[$thread_message_index]);
		return $last_sender_data;
	}

	// получаем список последних отправителей с индексами сообщений
	public static function prepareForFormat(array $last_sender_data):array {

		$output = [];
		foreach ($last_sender_data as $k => $v) {

			$output[] = [
				"thread_message_index" => $k,
				"user_id"              => $v,
			];
		}

		return $output;
	}

	// получаем список юзеров для action users
	public static function getActionUsersList(array $last_sender_data):array {

		return array_values($last_sender_data);
	}
}