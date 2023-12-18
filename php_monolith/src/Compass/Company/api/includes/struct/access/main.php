<?php

namespace Compass\Company;

/**
 * Структура данных доступа к компании
 */
class Struct_Access_Main {

	/**
	 * Constructor
	 */
	public function __construct(
		public string $status,
		public string $reason
	) {
	}
}
