<?php

namespace Compass\Pivot;

/**
 * контроллер для методов типа личности
 */
class Apiv1_Pivot_Mbti extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getInfo",
		"setColorSelectionList",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод получает инфу о типе личности
	 *
	 * @throws \paramException
	 * @throws \parseException|\cs_RowIsEmpty
	 */
	public function getInfo():array {

		$mbti_type = $this->post(\Formatter::TYPE_STRING, "mbti_type");

		$mbti_info = Domain_User_Scenario_Api::getMBTIInfo($this->user_id, $mbti_type);

		return $this->ok($mbti_info);
	}

	/**
	 * Метод устанавливает выделение цветом
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function setColorSelectionList():array {

		$mbti_type            = $this->post(\Formatter::TYPE_STRING, "mbti_type");
		$text_type            = $this->post(\Formatter::TYPE_STRING, "text_type");
		$color_selection_list = $this->post(\Formatter::TYPE_JSON, "color_selection_list");

		try {
			Domain_User_Scenario_Api::setColorSelectionList($this->user_id, $mbti_type, $text_type, $color_selection_list);
		} catch (cs_ExceededColorSelectionList) {
			return $this->error(539, "Exceeded the maximum number of color_selection_list");
		}

		return $this->ok();
	}
}