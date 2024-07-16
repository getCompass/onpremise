<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с conference_id
 */
class Domain_Jitsi_Entity_Conference_Id {

	/** @var int длинна уникальной части conference_id по-умолчанию */
	protected const _LENGTH_UNIQUE_PART = 8;

	/**
	 * получаем conference_id
	 *
	 * @return string
	 */
	public static function getConferenceId(int $creator_user_id, string $unique_part, string $password):string {

		// ВНИМАНИЕ! обязательно используем нижний регистр, так как использование верхнего регистра приводит к фантомным багам со стороны jitsi
		return mb_strtolower(sprintf("%d_%s_%s", $creator_user_id, $unique_part, $password));
	}

	/**
	 * разбиваем conference_id и получаем ее части
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function explodeConferenceId(string $conference_id):array {

		$tt = explode("_", $conference_id);
		if (count($tt) !== 3) {
			throw new ParseFatalException("unexpected conference_id");
		}

		$creator_user_id = $tt[0];
		$unique_part     = $tt[1];
		$password        = $tt[2];

		return [$creator_user_id, $unique_part, $password];
	}

	/**
	 * генерируем случайную уникальную часть ID
	 *
	 * @return string
	 */
	public static function generateRandomUniquePart():string {

		// ВНИМАНИЕ! обязательно используем нижний регистр, так как использование верхнего регистра приводит к фантомным багам со стороны jitsi
		return mb_strtolower(generateRandomString(self::_LENGTH_UNIQUE_PART));
	}
}