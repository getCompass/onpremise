<?php

namespace BaseFrame\Domain\User;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс по работе с аватарами пользователей
 */
class Avatar {

	protected const _PEACH_COLOR    = 1;
	protected const _ROSE_COLOR     = 2;
	protected const _GRASS_COLOR    = 3;
	protected const _LIME_COLOR     = 4;
	protected const _LAVENDER_COLOR = 5;
	protected const _BERRY_COLOR    = 6;
	protected const _AZURE_COLOR    = 7;

	// доступные цвета
	protected const _ALLOWED_COLOR_LIST = [
		self::_PEACH_COLOR,
		self::_ROSE_COLOR,
		self::_GRASS_COLOR,
		self::_LIME_COLOR,
		self::_LAVENDER_COLOR,
		self::_BERRY_COLOR,
		self::_AZURE_COLOR,
	];

	// цвета, из которых можно выбрать рандомный для пользователя
	protected const _PICK_COLOR_LIST = [
		self::_PEACH_COLOR,
		self::_ROSE_COLOR,
		self::_LIME_COLOR,
		self::_LAVENDER_COLOR,
		self::_BERRY_COLOR,
		self::_AZURE_COLOR,
	];

	protected const _COLOR_OUTPUT_SCHEMA = [
		self::_PEACH_COLOR    => "peach",
		self::_ROSE_COLOR     => "rose",
		self::_GRASS_COLOR    => "grass",
		self::_LIME_COLOR     => "lime",
		self::_LAVENDER_COLOR => "lavender",
		self::_BERRY_COLOR    => "berry",
		self::_AZURE_COLOR    => "azure",
	];

	// цвет аватара создателя пространства
	protected const _SPACE_CREATOR_COLOR = self::_GRASS_COLOR;

	// неизвестный цвет аватара
	protected const _UNKNOWN_COLOR_OUTPUT = "unknown";

	/**
	 * Получить id цвета пользователя
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public static function getColorByUserId(int $user_id):int {

		return self::_PICK_COLOR_LIST[$user_id % count(self::_PICK_COLOR_LIST)];
	}

	/**
	 * Разрешенный ли для установки цвет
	 *
	 * @param int $color
	 *
	 * @return bool
	 */
	public static function isAllowedColor(int $color):bool {

		return in_array($color, self::_ALLOWED_COLOR_LIST);
	}

	/**
	 * Выбросить исключение, если неразрешенный ли цвет для установки
	 *
	 * @param int $color
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	public static function assertAllowedColor(int $color):void {

		if (!self::isAllowedColor($color)) {
			throw new ParseFatalException("not allowed color");
		}
	}

	/**
	 * Получить id цвета аватара для создателя пространства
	 *
	 * @return int
	 */
	public static function getSpaceCreatorColor():int {

		return self::_SPACE_CREATOR_COLOR;
	}

	/**
	 * Вернуть название цвета клиентам
	 *
	 * @param int $color_id
	 *
	 * @return string
	 */
	public static function getColorOutput(int $color_id):string {

		return self::_COLOR_OUTPUT_SCHEMA[$color_id] ?? self::_UNKNOWN_COLOR_OUTPUT;
	}
}