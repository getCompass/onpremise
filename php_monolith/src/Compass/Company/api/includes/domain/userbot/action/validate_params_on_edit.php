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
					  int|false    $avatar_color_id, string|false $avatar_file_key,
					  int|false    $is_react_command, string|false $webhook,
					  int|false    $is_smart_app, string|false $smart_app_name, string|false $smart_app_url,
					  int|false    $is_smart_app_sip, int|false $is_smart_app_mail,
					  int|false    $smart_app_default_width, int|false $smart_app_default_height):array {

		// если никакой из параметров для редактирования не передан
		if ($userbot_name === false && $avatar_color_id === false && $avatar_file_key === false && $short_description === false
			&& $is_react_command === false && $webhook === false && $is_smart_app === false && $smart_app_name === false && $smart_app_url === false
			&& $is_smart_app_sip === false && $is_smart_app_mail === false && $smart_app_default_width === false && $smart_app_default_height === false) {

			throw new Domain_Userbot_Exception_EmptyParam("not have param for edit");
		}

		// если передано имя
		if ($userbot_name !== false) {

			$userbot_name = Domain_Userbot_Entity_Sanitizer::sanitizeName($userbot_name);
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

		// если передан smart app name
		if ($smart_app_name !== false) {
			$smart_app_name = Domain_Userbot_Entity_Sanitizer::sanitizeSmartAppName($smart_app_name);
		}

		// если передан smart app url
		if ($smart_app_url !== false) {
			$smart_app_url = Domain_Userbot_Entity_Sanitizer::sanitizeSmartAppUrl($smart_app_url);
		}

		// если передан флаг что это smart_app бот
		if ($is_smart_app !== false) {
			Domain_Userbot_Entity_Validator::assertCorrectFlagSmartApp($is_smart_app);
		}

		// если передан флаг is_smart_app_sip
		if ($is_smart_app_sip !== false) {
			Domain_Userbot_Entity_Validator::assertCorrectFlagSmartApp($is_smart_app_sip);
		}

		// если передан флаг is_smart_app_mail
		if ($is_smart_app_mail !== false) {
			Domain_Userbot_Entity_Validator::assertCorrectFlagSmartApp($is_smart_app_mail);
		}

		// если передана дефолтная ширина smart_app
		if ($smart_app_default_width !== false) {
			Domain_Userbot_Entity_Validator::assertCorrectSmartAppSideResolution($smart_app_default_width);
		}

		// если передана дефолтная высота smart_app
		if ($smart_app_default_height !== false) {
			Domain_Userbot_Entity_Validator::assertCorrectSmartAppSideResolution($smart_app_default_height);
		}

		// если включён флаг, но не передали smart_app_name
		if ($is_smart_app === 1 && ($smart_app_name === false || isEmptyString($smart_app_name))) {
			throw new Domain_Userbot_Exception_EmptySmartAppName("smart_app_name is empty");
		}

		// если включён флаг, но не передали smart_app url
		if ($is_smart_app === 1 && ($smart_app_url === false || isEmptyString($smart_app_url))) {
			throw new Domain_Userbot_Exception_EmptySmartAppUrl("smart_app_url is empty");
		}

		// если передали аватарку
		if ($avatar_color_id !== false) {
			Domain_Userbot_Entity_Validator::assertCorrectAvatarColorId($avatar_color_id);
		}

		return [$userbot_name, $short_description, $webhook, $smart_app_name, $smart_app_url];
	}
}