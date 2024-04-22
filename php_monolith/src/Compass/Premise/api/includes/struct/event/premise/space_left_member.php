<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Структура события, когда участник покинул команду
 */
class Struct_Event_Premise_SpaceLeftMember extends Struct_Event_Default {

	/** @var int $user_id id пользователя */
	public int $user_id;

	/** @var int $space_id id пространства */
	public int $space_id;

	/**
	 * Статический конструктор.
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function build(int $user_id, int $space_id, string $unique_key = ""):static {

		return new static([
			"unique_key" => $unique_key,
			"user_id"    => $user_id,
			"space_id"    => $space_id,
		]);
	}
}
