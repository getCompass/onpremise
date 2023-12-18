<?php

namespace Compass\Pivot;

/**
 * Стуктура для кандидата на найм
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Socket_Candidate {

	public string $name;
	public string $phone;
	public int    $invite_id;
	public bool   $is_added_on_company_create;

	/**
	 * Struct_Socket_Candidate constructor.
	 *
	 */
	public function __construct(string $name, string $phone, int $invite_id = 0, bool $is_added_on_company_create = false) {

		$this->name                       = $name;
		$this->phone                      = $phone;
		$this->invite_id                  = $invite_id;
		$this->is_added_on_company_create = $is_added_on_company_create;
	}
}