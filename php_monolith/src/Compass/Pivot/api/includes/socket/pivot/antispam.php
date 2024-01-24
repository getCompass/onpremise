<?php

namespace Compass\Pivot;

/**
 * Методы для работы с антиспамом
 */
class Socket_Pivot_Antispam extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"getCurrentBlockLevel",
		"setBlockLevel",
	];

	/**
	 * Получаем текущий уровень блокировки
	 *
	 * @throws \returnException
	 */
	public function getCurrentBlockLevel():array {

		$block_level = Domain_Antispam_Scenario_Socket::getCurrentBlockLevel();

		return $this->ok([
			"block_level" => (string) $block_level,
		]);
	}

	/**
	 * устанавливаем новый уровень блокировки
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function setBlockLevel():array {

		$block_level = $this->post(\Formatter::TYPE_STRING, "block_level");

		Domain_Antispam_Scenario_Socket::setBlockLevel($block_level);

		return $this->ok();
	}
}