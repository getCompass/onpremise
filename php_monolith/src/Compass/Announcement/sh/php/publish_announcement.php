<?php

namespace Compass\Announcement;

require_once __DIR__ . "/../../../../../start.php";

/**
 * Class Sh_Php_PublishAnnouncement
 */
class Sh_Php_PublishAnnouncement {

	protected const _HINT = [
		\Service\AnnouncementTemplate\AnnouncementType::APPLICATION_TECHNICAL_WORKS_IN_PROGRESS => [
			"description"       => "анонс технических работ",
			"extra_description" => [
				"started_at"               => "время начала, тех. работ (timestamp)",
				"app_will_be_available_at" => "когда приложение станет доступно (timestamp)",
			],
		],
		\Service\AnnouncementTemplate\AnnouncementType::APP_VERSION_OUTDATED_ANDROID => [
			"description"       => "версия приложения андроид устарела",
			"extra_description" => [
				"meta" => [
					"platform"               => "лучше не менять",
					"supported_code_version" => "поддерживаемая версия кода </>/= кодовая версия, уточнять у клиента",
				],
			],
		],
		\Service\AnnouncementTemplate\AnnouncementType::APP_VERSION_OUTDATED_ELECTRON => [
			"description"       => "версия приложения electron устарела",
			"extra_description" => [
				"meta" => [
					"platform"               => "лучше не менять",
					"supported_code_version" => "поддерживаемая версия кода </>/= кодовая версия, уточнять у клиента",
				],
			],
		],
		\Service\AnnouncementTemplate\AnnouncementType::APP_VERSION_OUTDATED_IOS => [
			"description"       => "версия приложения ios устарела",
			"extra_description" => [
				"meta" => [
					"platform"               => "лучше не менять",
					"supported_code_version" => "поддерживаемая версия кода </>/= кодовая версия, уточнять у клиента",
				],
			],
		],
	];

	/**
	 * Точка входа.
	 */
	public function exec():void {

		$template = \Service\AnnouncementTemplate\TemplateService::createOfType(static::_pickType());

		static::_showBasicInfo($template);

		$template = static::_setFields($template);
		$template = static::_setReceivers($template);
		$template = static::_setExcluded($template);

		if (count($template["receiver_user_id_list"]) === 0 && count($template["excluded_user_id_list"]) === 0) {

			if (!Type_Script_InputHelper::assertConfirm("анонс будет опубликован для всех пользователей, продолжаем (y/n)?")) {
				exit;
			}
		}

		$template = static::_setExpiresAt($template);
		$template = static::_setPriority($template);
		$template = static::_setAnnouncementExtra($template);

		console(var_export($template, true));
		static::_beforePublish($template);

		if (!Type_Script_InputHelper::assertConfirm("публикуем (y/n)?")) {
			exit;
		}

		Domain_Announcement_Action_Publish::do(new Struct_Db_AnnouncementMain_Announcement(...$template));
	}

	/**
	 * Предлагает пользователю выбрать тип анонса для создания.
	 *
	 * @return int
	 */
	protected static function _pickType():int {

		console("выбери тип анонса для публикации");
		$known = \Service\AnnouncementTemplate\AnnouncementType::getKnownStringTypes();

		foreach ($known as $string_type => $numeric_type) {

			if (isset(static::_HINT[$numeric_type])) {
				$string_type = static::_HINT[$numeric_type]["description"];
			}

			console("{$numeric_type}: $string_type");
		}

		$input = readline();

		if (!in_array($input, $known)) {
			die("указан неизвестный тип анонса");
		}

		return (int) $input;
	}

	/**
	 * Показывает базовую информацию об анонсе.
	 *
	 * @param array $template
	 */
	protected static function _showBasicInfo(array $template):void {

		$global_phrase = $template["is_global"]
			? "глобальным"
			: "компанейский";

		$behavior = in_array($template["type"], Domain_Announcement_Entity::getBlockingTypes())
			? redText("блокирующим")
			: greenText("уведомляющим");

		console("выбранный анонс является {$global_phrase}-{$behavior}");

		if (!Type_Script_InputHelper::assertConfirm("продолжаем (y/n)?")) {
			exit;
		}
	}

	/**
	 * Устанавливает время истечения анонса.
	 *
	 * @param array $template
	 *
	 * @return array
	 */
	protected static function _setExpiresAt(array $template):array {

		if ($template["expires_at"] == 0) {
			console("укажи время жизни анонса в секундах, (по умолчанию 0 — никогда не истекает)");
		} else {
			console("укажи время жизни анонса в секундах, (по умолчанию " . $template["expires_at"] - time() . ")");
		}

		$input = readline();

		if ($input !== "" && !is_numeric($input)) {
			die("передано время в неизвестном формате");
		}

		if ($input == "0") {
			$expires_at = 0;
		} else {
			$expires_at = $input !== "" ? time() + intval($input) : $template["expires_at"];
		}

		// убеждаемся, что пользователь подтверждает создание блокирующего анонса
		if ($expires_at == 0 && !Type_Script_InputHelper::assertConfirm("анонс будет висеть до тех пор, пока его не отключает, продолжаем (y/n)?")) {
			exit;
		}

		$template["expires_at"] = (int) $expires_at;
		return $template;
	}

	/**
	 * Устанавливает приоритет для анонса.
	 *
	 * @param array $template
	 *
	 * @return array
	 */
	protected static function _setPriority(array $template):array {

		console("укажи приоритет, (default {$template["priority"]})");

		$input = readline();

		if ($input !== "" && !is_numeric($input)) {
			die("введено некорректное значение для приоритета");
		}

		$priority = $input !== ""
			? intval($input)
			: $template["priority"];

		$template["priority"] = $priority;
		return $template;
	}

	/**
	 * Устанавливаем данные экстры анонса.
	 *
	 * @param array $template
	 *
	 * @return array
	 */
	protected static function _setAnnouncementExtra(array $template):array {

		$template["extra"]["extra"] = self::_modifyArray($template["extra"]["extra"], "[extra]", static::_HINT[$template["type"]]["extra_description"] ?? []);
		return $template;
	}

	/**
	 * Обновляет массив через ввод из консоли.
	 *
	 * @param array  $data
	 * @param string $prefix
	 *
	 * @return array
	 */
	protected static function _modifyArray(array $data, string $prefix, array $hint = []):array {

		foreach ($data as $k => $v) {

			if (is_array($v)) {

				$data[$k] = static::_modifyArray($v, "{$prefix}[{$k}]", $hint[$k] ?? []);
				continue;
			}

			if (isset($hint[$k])) {
				$field = "{$k} — $hint[$k]";
			} else {
				$field = $k;
			}

			console("укажи значение для поля {$field} {$prefix} (по умолчанию: {$v})");
			$input = readline();

			$data[$k] = $input !== "" ? $input : $v;
		}

		return $data;
	}

	/**
	 * Устанавливаем данные экстры анонса.
	 *
	 * @param array $template
	 *
	 * @return array
	 */
	protected static function _setFields(array $template):array {

		if (!$template["is_global"]) {

			console("введи company_id для анонса");
			$input = readline();

			if ($input === "" || !is_numeric($input)) {
				die("указано некорректный идентификатор компании");
			}

			$template["company_id"] = (int) $input;
		}

		$template["created_at"]      = time();
		$template["updated_at"]      = time();
		$template["announcement_id"] = 0;

		return $template;
	}

	/**
	 * Устанавливаем получателей.
	 *
	 * @param array $template
	 *
	 * @return array
	 */
	protected static function _setReceivers(array $template):array {

		console("через запятую укажи всех получателей анонса, ничего не вводи, чтобы отправить все пользователям");
		$input = readline();

		$receivers = $input !== ""
			? arrayValuesInt(array_unique(explode(",", $input)))
			: [];

		$template["receiver_user_id_list"] = $receivers;
		return $template;
	}

	/**
	 * Устанавливаем исключенных пользователей.
	 *
	 * @param array $template
	 *
	 * @return array
	 */
	protected static function _setExcluded(array $template):array {

		console("через запятую укажи пользователей, которые не должны получить анонс, оставь пустым, если таких пользователей нет");
		$input = readline();

		$receivers = $input !== ""
			? arrayValuesInt(array_unique(explode(",", $input)))
			: [];

		$template["excluded_user_id_list"] = $receivers;
		return $template;
	}

	/**
	 * Выводит информацию перед публикацией
	 *
	 * @param array $template
	 */
	protected static function _beforePublish(array $template):void {

		console("данные для публикации");

		if ($template["expires_at"] == 0) {
			console(yellowText("без срока действия"));
		} else {
			console("анонс существует до " .  yellowText(date("d/m/y H:i", $template["expires_at"])));
		}

		$receiver_str = "получают анонс ";

		if (count($template["receiver_user_id_list"]) === 0) {
			$receiver_str .= "все пользователи";
		} else {
			$receiver_str .= "пользователи" . implode(", ", $template["receiver_user_id_list"]);
		}

		if (count($template["excluded_user_id_list"]) > 0) {
			$receiver_str .= " кроме "  . implode(", ", $template["excluded_user_id_list"]);
		}

		console($receiver_str);
	}
}

(new Sh_Php_PublishAnnouncement())->exec();