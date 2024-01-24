<?php declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Структура события «запущено обновление компании в асинхронном режиме».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_System_AsyncModuleUpdateInitialized extends Struct_Default {

	/**
	 * Struct_Event_System_AsyncCompanyUpdateInitialized constructor.
	 *
	 * @param string $module_name
	 * @param string $script_name
	 * @param array  $data
	 *
	 * @throws \parseException
	 */
	public function __construct(public string $module_name, public string $script_name, public array $data) {

		parent::__construct([
			"module_name" => $module_name,
			"script_name" => $script_name,
			"data"        => $data,
		]);
	}
}
