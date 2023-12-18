<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для данных активной компании
 */
class Struct_Domain_Company_ActivityData {

	/**
	 * Struct_Domain_Company_ActivityData constructor.
	 */
	public function __construct(
		public Struct_Domain_Company_CommonActivityData|null $common_activity_data,
		public Struct_Domain_Company_OwnerActivityData|null  $owner_activity_data,
	) {
	}
}