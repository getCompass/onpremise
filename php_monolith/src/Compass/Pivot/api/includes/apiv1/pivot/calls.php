<?php

namespace Compass\Pivot;

/**
 * контроллер для работы звонков
 */
class Apiv1_Pivot_Calls extends \BaseFrame\Controller\Api {

	// Поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"getPreferences",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод для возвращения состояния звонка
	 */
	public function getPreferences():array {

		[$constants, $active_call] = Domain_User_Scenario_Api::getCallPreferences($this->user_id);

		$formatted_active_call = [];
		if ($active_call !== false) {
			$formatted_active_call = Apiv1_Pivot_Format::getActiveCall($active_call);
		}

		return $this->ok([
			"constants"   => (array) Apiv1_Pivot_Format::getCallConstants($constants),
			"active_call" => (object) $formatted_active_call,
		]);
	}
}
