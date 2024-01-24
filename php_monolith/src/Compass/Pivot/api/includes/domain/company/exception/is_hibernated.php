<?php

namespace Compass\Pivot;

/**
 * Запрос в компанию невозможен — компания в режиме гибернации.
 */
class Domain_Company_Exception_IsHibernated extends \BaseFrame\Exception\DomainException {

	public const HTTP_CODE = 491;
}