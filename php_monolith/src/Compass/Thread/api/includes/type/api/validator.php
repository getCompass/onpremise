<?php

namespace Compass\Thread;

// класс для фильтрации одинаковых сущностей внутри модулей общий для всех
class Type_Api_Validator {

	// обязательные параметры для client_message_list
	protected const _REQUIRED_CLIENT_MESSAGE_LIST_PARAMS = [
		"text"              => "string",
		"client_message_id" => "string",
		"order"             => "integer",
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// проверяем параметр client_message_list на корректность
	public static function isCorrectClientMessageList(array $client_message_list):bool {

		// если параметр не является массивом
		if (is_array($client_message_list) === false) {
			return false;
		}

		// если массив пуст
		if (count($client_message_list) < 1) {
			return false;
		}

		// проверяем что каждый итем имеет обязательные поля
		foreach ($client_message_list as $item) {

			foreach (self::_REQUIRED_CLIENT_MESSAGE_LIST_PARAMS as $field => $_) {

				if (!isset($item[$field])) {
					return false;
				}
			}
		}

		return true;
	}

	// проверяем, корректны ли типы параметров client_message_item
	public static function isCorrectTypeClientMessageItem(array $client_message_item):bool {

		// проверяем что каждый итем имеет обязательные поля
		foreach (self::_REQUIRED_CLIENT_MESSAGE_LIST_PARAMS as $field => $_) {

			if (!isset($client_message_item[$field])) {
				return false;
			}
		}

		foreach ($client_message_item as $k => $v) {

			// если такого поля нет в обязательных - пропускаем
			if (!isset(self::_REQUIRED_CLIENT_MESSAGE_LIST_PARAMS[$k])) {
				continue;
			}

			// получаем тип, который должен был у обязательного поля
			$field_type = self::_REQUIRED_CLIENT_MESSAGE_LIST_PARAMS[$k];

			if ($field_type === "string" && is_string($v) === false) {
				return false;
			}
			if ($field_type === "integer" && is_numeric($v) === false) {
				return false;
			}
		}

		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------
}