<?php

namespace Compass\Pivot;

/**
 * Шаблон API ключа
 */
class Struct_User_ApikeyTemplate
{
	/**
	 * Struct_User_ApikeyTemplate constructor.
	 */
	public function __construct(
		public string $template_id,
		public int $order,
		public string $title,
		public string $uniq_name,
		public string $description,
		public array $scope_list
	) {

	}
}
