<?php

namespace Compass\Conversation;

/**
 * Класс для работы c ключами скриптов обновления компаний.
 * Просто обертка над InputParser.
 */
class Type_Script_InputHelper {

	/**
	 * Определяет, является ли вызов dry.
	 * Dry используется для вызова скриптов без каких-либо изменений.
	 *
	 * @param bool $is_required
	 *
	 * @return bool
	 */
	public static function isDry(bool $is_required = true):bool {

		// старый вариант dry вызова
		if (isDryRun()) {

			$command_text = greenText("--dry");
			console(yellowText("deprecated dry-run call, use {$command_text} instead"));

			return true;
		}

		// получаем значение
		$is_dry = Type_Script_InputParser::getArgumentValue("--dry", Type_Script_InputParser::TYPE_INT);

		if ($is_required && ($is_dry === false)) {
			throw new \InvalidArgumentException("dry flag is required, usage: --dry=1/0");
		}

		return $is_dry === 1;
	}

	/**
	 * Проверяем корректность переданных ключей.
	 *
	 * @param array $allowed_key_list
	 * @param array $required_key_list
	 *
	 * @throws \Exception
	 */
	public static function assertKeys(array $allowed_key_list, array $required_key_list):void {

		$passed_key_list = Type_Script_InputParser::getPassedKeys();

		foreach ($required_key_list as $required_key => $message) {

			if (!in_array($required_key, $passed_key_list)) {
				throw new \Exception($message);
			}
		}

		foreach ($passed_key_list as $passed_key) {

			if (!in_array($passed_key, $allowed_key_list)) {
				throw new \Exception("passed key {$passed_key} in not allowed");
			}
		}
	}

	/**
	 * Ожидает подтверждение со стороны пользователя.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public static function assertConfirm(string $message):bool {

		console($message);

		$input = readline();
		return mb_strtolower($input) === "y";
	}

	/**
	 * Нужно ли показать окно с информацией помощи.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public static function needShowUsage():bool {

		return Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false);
	}
}