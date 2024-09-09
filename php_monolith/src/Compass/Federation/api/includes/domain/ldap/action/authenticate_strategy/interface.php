<?php

namespace Compass\Federation;

/** интерфейс, описывающий поведение любой стратегии аутентификации */
interface Domain_Ldap_Action_AuthenticateStrategy_Interface {

	/**
	 * актуальна ли стратегия на текущем окружении
	 *
	 * @return bool
	 */
	public function isActual():bool;

	/**
	 * аутентифицируемся в учетную запись
	 *
	 * @return array информация об учетной записи, в которую успешно аутентифицировались
	 */
	public function authenticate(string $username, string $password):array;
}