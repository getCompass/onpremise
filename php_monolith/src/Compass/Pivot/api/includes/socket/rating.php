<?php

namespace Compass\Pivot;

/**
 * контроллер для работы с рейтингом в приложении
 */
class Socket_Rating extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"saveScreenTime",
		"saveUserActionList",
		"saveUserAnswerTime",
	];

	/**
	 * Сохраняем экранное время пользователей
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public function saveScreenTime():array {

		$screen_time_list = $this->post(\Formatter::TYPE_ARRAY, "screen_time_list");

		// сохраняем экранное время
		Domain_Rating_Scenario_Socket::saveScreenTime($screen_time_list);

		return $this->ok();
	}

	/**
	 * Сохраняем количество действий пользователей
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function saveUserActionList():array {

		$user_list = $this->post(\Formatter::TYPE_ARRAY, "user_list");

		// сохраняем количество действий пользователей
		Domain_Rating_Scenario_Socket::saveUserActionList($user_list);

		return $this->ok();
	}

	/**
	 * Сохраняем время ответа пользователей на сообщения
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function saveUserAnswerTime():array {

		$conversation_list = $this->post(\Formatter::TYPE_ARRAY, "conversation_list");

		// сохраняем время ответа пользователей на сообщения
		Domain_Rating_Scenario_Socket::saveUserAnswerTime($conversation_list);

		return $this->ok();
	}
}