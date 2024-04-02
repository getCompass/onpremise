<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-хендлер для работы с сущностью аутентификации через SSO
 */
class Domain_User_Entity_AuthStory_MethodHandler_Sso extends Domain_User_Entity_AuthStory_MethodHandler_Default {

	/** через сколько истекает попытка аутентификации */
	public const STORY_LIFE_TIME = 0;

	protected Struct_Db_PivotAuth_AuthDefault|Struct_Db_PivotAuth_AuthSso $_auth_entity;

	public function __construct(
		Struct_Db_PivotAuth_AuthSso $auth_sso,
	) {

		$this->_auth_entity = $auth_sso;
	}

	/**
	 * подготавливаем черновик Struct_Db_PivotAuth_AuthSso
	 *
	 * @return array
	 */
	public static function prepareAuthSsoDataDraft(string $sso_auth_token):array {

		return (array) new Struct_Db_PivotAuth_AuthSso(
			auth_map: "",
			sso_auth_token: $sso_auth_token,
			created_at: time(),
		);
	}

	/**
	 * создаем сущность
	 */
	public function create():void {

		if (!Gateway_Db_PivotAuth_AuthSsoList::inTransaction(Type_Pack_Auth::getShardId($this->_auth_entity->auth_map))) {
			throw new ParseFatalException("active transaction required");
		}

		Gateway_Db_PivotAuth_AuthSsoList::insert($this->_auth_entity);
	}

	/**
	 * Обрабатываем успешное завершение аутентификации
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function handleSuccess(int $user_id, array $additional_update_field_list):static {

		// ничего не делаем для этого типа аутентификации

		return $this;
	}

	/**
	 * получаем параметр аутентификации с помощью которого была начата попытка
	 *
	 * для аутентификации через телефон – это номер
	 * для аутентификации через почту – это адрес почты
	 *
	 * @return string
	 */
	public function getAuthParameter():string {

		return $this->getSsoAuthToken();
	}

	/**
	 * получить sso_auth_token
	 *
	 * @return string
	 */
	public function getSsoAuthToken():string {

		return $this->_auth_entity->sso_auth_token;
	}

	/**
	 * получить map идентификатор аутентификации
	 *
	 * @return string
	 */
	public function getAuthMap():string {

		return $this->_auth_entity->auth_map;
	}

	/**
	 * Конвертируем сущность способа аутентификации в ассоц. массив
	 *
	 * @return array
	 */
	public function authEntityToArray():array {

		return (array) $this->_auth_entity;
	}
}