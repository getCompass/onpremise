<?php

namespace Compass\Pivot;

/**
 * Запрос в компанию невозможен — компания переезжает.
 */
class Domain_Company_Exception_IsRelocating extends \BaseFrame\Exception\DomainException {

	public const HTTP_CODE = 491;
}