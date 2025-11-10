<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Класс action для получения токена авторизации
 */
class Domain_SmartApp_Action_GetAuthorizationToken {

	/**
	 * выполняем действие
	 *
	 * @param int          $user_id
	 * @param string|false $entity
	 * @param string|false $entity_key
	 * @param int          $smart_app_id
	 * @param int          $client_width
	 * @param int          $client_height
	 *
	 * @return string
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \returnException
	 * @throws cs_PlatformNotFound
	 */
	public static function do(int $user_id, string|false $entity, string|false $entity_key, int $smart_app_id, int $client_width, int $client_height):string {

		// проверяем что есть доступ к переданной сущности
		self::_assertEntityMember($user_id, $entity, $entity_key);

		try {
			$smart_app_user_rel = Gateway_Db_CompanyData_SmartAppUserRel::getOne($smart_app_id, $user_id);
		} catch (Domain_SmartApp_Exception_SmartAppNotFound) {
			throw new ParamException("passed incorrect params");
		}

		try {
			$smart_app = Gateway_Db_CompanyData_SmartAppList::getOne($smart_app_id);
		} catch (Domain_SmartApp_Exception_SmartAppNotFound) {
			throw new ParamException("passed incorrect params");
		}

		// получаем ссылку на аватар
		$smart_app_avatar_url = self::_getAvatarUrl(Domain_SmartApp_Entity_SmartAppUserRel::getAvatarFileKey($smart_app_user_rel->extra));

		return Domain_SmartApp_Entity_AuthenticationToken::generate(
			$user_id, Domain_SmartApp_Entity_SmartAppUserRel::getTitle($smart_app_user_rel->extra),
			$smart_app->smart_app_uniq_name, $smart_app_avatar_url, Domain_SmartApp_Entity_SmartApp::getPrivateKey($smart_app->extra),
			$client_width, $client_height, Type_Api_Platform::getPlatform(),
			$entity, $entity_key,
		);
	}

	/**
	 * Проверяем есть ли доступ к переданной сущности
	 *
	 * @param int          $user_id
	 * @param string|false $entity
	 * @param string|false $entity_key
	 *
	 * @return void
	 * @throws ParamException
	 * @throws ReturnFatalException
	 */
	protected static function _assertEntityMember(int $user_id, string|false $entity, string|false $entity_key):void {

		if ($entity === false) {
			return;
		}

		$is_entity_member = false;

		// если передали диалог
		if ($entity === Domain_SmartApp_Entity_SmartApp::ENTITY_CONVERSATION && $entity_key !== false) {
			$is_entity_member = Gateway_Socket_Conversation::checkIsUserMember($user_id, $entity_key);
		}

		// если передали тред
		if ($entity === Domain_SmartApp_Entity_SmartApp::ENTITY_THREAD && $entity_key !== false) {
			$is_entity_member = Gateway_Socket_Thread::checkIsUserMember($user_id, $entity_key);
		}

		// если доступ есть то пропускаем дальше
		if ($is_entity_member) {
			return;
		}

		throw new ParamException("passed incorrect params");
	}

	/**
	 * Получаем ссылку на аватарку
	 *
	 * @param string $smart_app_avatar_file_key
	 *
	 * @return string
	 * @throws \returnException
	 */
	protected static function _getAvatarUrl(string $smart_app_avatar_file_key):string {

		if (mb_strlen($smart_app_avatar_file_key) < 1) {
			return "";
		}

		$file_list = Gateway_Socket_FileBalancer::getFiles([$smart_app_avatar_file_key], true);
		if (!isset($file_list[0]["url"])) {
			return "";
		}

		return $file_list[0]["url"];
	}
}