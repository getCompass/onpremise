<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Структура события удаления профиля пользователя
 */
class Struct_Event_Premise_UserProfileDeleted extends Struct_Event_Default {

	/** @var int $user_id id пользователя */
	public int $user_id;

	/**
	 * Статический конструктор.
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function build(int $user_id, string $unique_key = ""):static {

		return new static([
			"unique_key" => $unique_key,
			"user_id"    => $user_id,
		]);
	}
}
