<?php

namespace AnalyticUtils\Domain\Event\Struct\User;

/**
 * Класс-структура для информации по клиенту
 */
class ClientData {

	/**
	 * Конструктор класса
	 *
	 * @param string $user_agent
	 * @param string $platform
	 * @param string $app_version
	 */
	public function __construct(
		public string $user_agent,
		public string $platform,
		public string $app_version,
	) {}

}