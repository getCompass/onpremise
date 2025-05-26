<?php

namespace Compass\Federation;

/** интерфейс, описывающий контракт парсера значений атрибутка из учетной записи SSO (oidc, ldap, etc ...) */
interface Domain_Sso_Entity_CompassMapping_ParserInterface {

	public static function parseField(mixed $data, string $attribute):string;

	public static function parseAssignment(mixed $data, string $assignment):array;
}