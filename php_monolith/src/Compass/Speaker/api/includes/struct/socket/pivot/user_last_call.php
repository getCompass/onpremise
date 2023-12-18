<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Структура для последнего звонка пользователя
 */
class Struct_Socket_Pivot_UserLastCall {

	public string $call_map;
	public int    $is_finished;
	public int    $user_id;

	/**
	 * CompassApp\Domain\Member\Struct\Main constructor.
	 *
	 */
	public function __construct(int $user_id, string $call_map, int $is_finished) {

		$this->call_map    = $call_map;
		$this->is_finished = $is_finished;
		$this->user_id     = $user_id;
	}
}