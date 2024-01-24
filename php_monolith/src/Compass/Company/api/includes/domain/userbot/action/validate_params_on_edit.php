<?php

namespace Compass\Company;

/**
 * Класс action для валидации параметров при редактировании бота
 */
class Domain_Userbot_Action_ValidateParamsOnEdit {

	/**
	 * выполняем действие
	 *
	 * @throws Domain_Userbot_Exception_EmptyParam
	 * @throws Domain_Userbot_Exception_EmptyWebhook
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 * @throws \cs_InvalidProfileName
	 * @long
	 */
	public static function do(string|false $userbot_name, string|false $short_description,
					  int|false    $avatar_color_id,
					  int|false    $is_react_command, string|false $webhook):array {

		// если никакой из параметров для редактирования не передан
		if ($userbot_name === false && $avatar_color_id === false && $short_description === false && $is_react_command === false && $webhook === false) {
			throw new Domain_Userbot_Exception_EmptyParam("not have param for edit");
		}

		// если передано имя
		if ($userbot_name !== false) {

			$userbot_name = \Entity_Sanitizer::sanitizeProfileName($userbot_name);
			\Entity_Validator::assertValidProfileName($userbot_name);
		}

		// если передано короткое описание
		if ($short_description !== false) {
			$short_description = Domain_Member_Entity_Sanitizer::sanitizeDescription($short_description);
		}

		// если передан вебхук
		if ($webhook !== false) {
			$webhook = Domain_Userbot_Entity_Sanitizer::sanitizeWebhookUrl($webhook);
		}

		// если передан флаг реагировать на команды
		if ($is_react_command !== false) {
			Domain_Userbot_Entity_Validator::assertCorrectFlagReactCommand($is_react_command);
		}

		// если включён флаг, но не передали вебхук бота
		if ($is_react_command == 1 && ($webhook === false || isEmptyString($webhook))) {
			throw new Domain_Userbot_Exception_EmptyWebhook("webhook is empty");
		}

		// если передали аватарку
		if ($avatar_color_id !== false) {
			Domain_Userbot_Entity_Validator::assertCorrectAvatarColorId($avatar_color_id);
		}

		return [$userbot_name, $short_description, $webhook];
	}
}