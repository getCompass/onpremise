<?php

namespace Compass\Conversation;

/**
 * класс для работы с прикрепленными к диалогу файлами, например при отправке сообщения с файлом
 */
class Domain_Conversation_Entity_File_Extra {

	// версия упаковщика
	protected const _EXTRA_VERSION = 1;

	// схема extra
	protected const _EXTRA_SCHEMA = [
		"hidden_by" => [], // список скрывших файл пользователей
	];

	// добавить пользователя в массив hidden_by - список идентифиакторов пользователей которые скрыли сообщение с файлом
	public static function addToHiddenBy(array $extra, int $user_id):array {

		// проверяем актуальность версии для поля extra
		$extra = self::_getExtra($extra);

		// добавляем идентификатор пользователя в массив hidden_by
		$extra["hidden_by"][] = $user_id;

		return $extra;
	}

	// проверяем что пользователь скрыл файл
	public static function isHiddenBy(array $extra, int $user_id):bool {

		// проверяем актуальность версии для поля extra
		$extra = self::_getExtra($extra);

		// записи о том что пользователи скрывали файл не существует
		if (count($extra["hidden_by"]) < 1) {
			return false;
		}

		if (in_array($user_id, $extra["hidden_by"])) {
			return true;
		}

		return false;
	}

	// получить актуальную структуру для extra
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает -> обновляем до актуальной
		if (!isset($extra["version"]) || $extra["version"] != self::_EXTRA_VERSION) {

			$extra            = array_merge(self::_EXTRA_SCHEMA, $extra);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}