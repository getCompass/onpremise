<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Класс action для получения токена авторизации
 */
class Domain_SmartApp_Action_GetAuthorizationToken {

	/**
	 * выполняем действие
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws ParamException
	 */
	public static function do(int $user_id, string|false $entity, string|false $entity_key, string $smart_app_name, int $client_width, int $client_height):string {

		// проверяем что есть доступ к переданной сущности
		self::_assertEntityMember($user_id, $entity, $entity_key);

		try {
			$userbot = Gateway_Db_CompanyData_UserbotList::getBySmartAppName($smart_app_name);
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new ParamException("passed incorrect params");
		}

		// получаем информацию о пользователе
		try {
			$user_info = Gateway_Bus_CompanyCache::getMember($userbot->user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("passed incorrect params");
		}

		// получаем ссылку на аватар
		$smart_app_avatar_url = self::_getAvatarUrl(Domain_Userbot_Entity_Userbot::getAvatarFileKey($userbot->extra));

		return Domain_SmartApp_Entity_AuthenticationToken::generate(
			$user_id, $user_info->full_name,
			$smart_app_name, $smart_app_avatar_url, Domain_Userbot_Entity_Userbot::getSmartAppPrivateKey($userbot->extra),
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