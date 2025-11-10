<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для секьюрных данных приложения
 */
class Struct_Domain_SmartApp_SensitiveData {

	/**
	 * Struct_Domain_SmartApp_SensitiveData constructor.
	 */
	public function __construct(
		public string $public_key,
	) {
	}
}