<?php

namespace Compass\Company;

/**
 * Класс action для подготовки команды через паттерн
 */
class Domain_Userbot_Action_PreparePatternCommand {

	/**
	 * паттерн для удаления аргументов из скобок команды
	 * аля "/чей заказ [id]" приводится к формату "/чей заказ []"
	 */
	protected const _COMMAND_PATTERN = "~\[\K.+?(?=\])~";

	/**
	 * выполняем действие
	 */
	public static function do(string $text):string {

		// приводим к нижнему регистру
		$command = mb_strtolower($text);

		// все аргументы в команде убираем по паттерну
		$command = preg_replace(self::_COMMAND_PATTERN, "", $command);

		// убираем первый символ слэша, если таковой был
		return ltrim($command, "/");
	}
}