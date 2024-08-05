<?php

namespace Compass\Federation;

/**
 * интерфейс описывающий поведение классов для работы с LDAP
 */
interface Domain_Ldap_Entity_Client_Interface {

	public function bind(string $dn, string $password):bool;

	public function unbind():void;

	public function searchEntries(string $base, string $filter, array $attribute_list = []):array;
}