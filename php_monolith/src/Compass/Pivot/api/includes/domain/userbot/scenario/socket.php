<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;
use busException;
use cs_DecryptHasFailed;
use cs_RowIsEmpty;
use cs_SocketRequestIsFailed;
use parseException;
use queryException;
use returnException;

/**
 * сценарии для сокет методов домена бота
 */
class Domain_Userbot_Scenario_Socket {

	/**
	 * создаём бота
	 *
	 * @param int    $company_id
	 * @param string $userbot_name
	 * @param int    $avatar_color_id
	 * @param int    $is_react_command
	 * @param string $webhook
	 *
	 * @return array
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_DamagedActionException
	 * @throws cs_DecryptHasFailed
	 * @throws parseException
	 * @throws queryException
	 * @throws returnException
	 */
	public static function create(int $company_id, string $userbot_name, int $avatar_color_id, int $is_react_command, string $webhook, int $role, int $permissions):array {

		// получаем дефолт-аватарку бота
		try {

			$avatar_file_key = Domain_Userbot_Entity_Userbot::getDefaultUserbotAvatar($avatar_color_id);
			$avatar_file_map = Type_Pack_File::doDecrypt($avatar_file_key);
		} catch (cs_RowIsEmpty|cs_DecryptHasFailed) {
			throw new Domain_Userbot_Exception_IncorrectParam("incorrect avatar_color_id = {$avatar_color_id}");
		}

		// создаём бота как пользователя приложения
		$create_user_bot = Domain_User_Action_Create_Userbot::do("", "", "", "", getIp(), $userbot_name, $avatar_file_map, []);

		// далее создаём сущность бота
		return self::_createUserbot(
			$create_user_bot->user_id, $create_user_bot->npc_type, $avatar_file_key, $avatar_color_id, $company_id, $is_react_command, $webhook, $role, $permissions
		);
	}

	/**
	 * создаём бота
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_DecryptHasFailed
	 * @throws parseException
	 * @throws queryException
	 * @throws returnException
	 */
	protected static function _createUserbot(int $user_id, int $npc_type, string $avatar_file_key, int $avatar_color_id, int $company_id, int $is_react_command, string $webhook, int $role, int $permissions):array {

		$created_at = time();

		// генерируем userbot_id для бота
		$userbot_id = Domain_Userbot_Entity_Userbot::generateUserbotId();

		// создаём бота
		try {
			Domain_Userbot_Entity_Userbot::create($userbot_id, $user_id, $company_id, $avatar_color_id, $created_at);
		} catch (cs_RowDuplication) {
			return self::_createUserbot($user_id, $npc_type, $avatar_file_key, $avatar_color_id, $company_id, $is_react_command, $webhook, $role, $permissions);
		}

		// создаём токен и ключ подписи для бота
		[$token, $secret_key] = self::_createToken($userbot_id, $is_react_command, $webhook, $created_at);

		// добавляем пользователя в компанию на пивоте
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

		Domain_Company_Entity_User_Member::add($user_id, $role, $permissions, $user_info->created_at, $company_id, 1, $npc_type, $token);

		return [$userbot_id, $user_id, $token, $secret_key, $avatar_file_key, $npc_type];
	}

	/**
	 * создаём данные токена
	 *
	 * @throws queryException
	 */
	protected static function _createToken(string $userbot_id, int $is_react_command, string $webhook, int $created_at):array {

		$token      = Domain_Userbot_Entity_Token::generateToken();
		$secret_key = Domain_Userbot_Entity_Token::generateSecretKey();

		try {
			Domain_Userbot_Entity_Token::create($userbot_id, $token, $secret_key, $is_react_command, $webhook, $created_at);
		} catch (cs_RowDuplication) {
			return self::_createToken($userbot_id, $is_react_command, $webhook, $created_at);
		}

		return [$token, $secret_key];
	}

	/**
	 * включаем бота
	 *
	 * @throws busException
	 * @throws cs_DecryptHasFailed
	 * @throws cs_RowIsEmpty
	 * @throws parseException
	 * @throws queryException
	 * @throws returnException
	 */
	public static function enable(string $userbot_id, string $token, string $client_launch_uuid):void {

		// достаём бота
		$userbot = Gateway_Db_PivotUserbot_UserbotList::get($userbot_id);

		// включаем бота
		Domain_Userbot_Entity_Userbot::changeStatus($userbot_id, Domain_Userbot_Entity_Userbot::STATUS_ENABLE);

		// возвращаем боту его прошлую аватарку, исходя из avatar_color_id
		$avatar_color_id = Domain_Userbot_Entity_Userbot::getAvatarColorId($userbot->extra);
		try {

			$avatar_file_key = Domain_Userbot_Entity_Userbot::getDefaultUserbotAvatar($avatar_color_id);
			$avatar_file_map = Type_Pack_File::doDecrypt($avatar_file_key);

			Domain_User_Action_UpdateProfile::do($userbot->user_id, false, $avatar_file_map, $client_launch_uuid);
		} catch (cs_RowIsEmpty|cs_FileIsNotImage) {
			// ничего не делаем, чтобы не сломать
		}

		// чистим данные по боту в кэше
		Gateway_Socket_UserbotCache::clearUserbotCache($token);
	}

	/**
	 * отключаем бота
	 *
	 * @throws busException
	 * @throws cs_DecryptHasFailed
	 * @throws cs_RowIsEmpty
	 * @throws parseException
	 * @throws queryException
	 * @throws returnException
	 */
	public static function disable(string $userbot_id, string $token, string $client_launch_uuid):void {

		// достаём бота
		$userbot = Gateway_Db_PivotUserbot_UserbotList::get($userbot_id);

		// выключаем бота
		Domain_Userbot_Entity_Userbot::changeStatus($userbot_id, Domain_Userbot_Entity_Userbot::STATUS_DISABLE);

		// меняем аватарку бота на неактивную
		try {

			$avatar_file_key = Domain_Userbot_Entity_Userbot::getInactiveUserbotAvatar();
			$avatar_file_map = Type_Pack_File::doDecrypt($avatar_file_key);

			Domain_User_Action_UpdateProfile::do($userbot->user_id, false, $avatar_file_map, $client_launch_uuid);
		} catch (cs_RowIsEmpty|cs_FileIsNotImage) {
			// ничего не делаем, чтобы не сломать
		}

		// чистим данные по боту в кэше
		Gateway_Socket_UserbotCache::clearUserbotCache($token);
	}

	/**
	 * удаляем бота
	 *
	 * @throws busException
	 * @throws cs_DecryptHasFailed
	 * @throws cs_RowIsEmpty
	 * @throws parseException
	 * @throws queryException
	 * @throws returnException
	 */
	public static function delete(string $userbot_id, string $token, string $client_launch_uuid):void {

		// достаём бота
		$userbot = Gateway_Db_PivotUserbot_UserbotList::get($userbot_id);

		// помечаем бота удалённым
		Domain_Userbot_Entity_Userbot::changeStatus($userbot_id, Domain_Userbot_Entity_Userbot::STATUS_DELETE);

		// меняем аватарку бота на неактивную
		try {

			$avatar_file_key = Domain_Userbot_Entity_Userbot::getInactiveUserbotAvatar();
			$avatar_file_map = Type_Pack_File::doDecrypt($avatar_file_key);

			Domain_User_Action_UpdateProfile::do($userbot->user_id, false, $avatar_file_map, $client_launch_uuid);
		} catch (cs_RowIsEmpty|cs_FileIsNotImage) {
			// ничего не делаем, чтобы не сломать
		}

		// чистим данные по боту в кэше
		Gateway_Socket_UserbotCache::clearUserbotCache($token);
	}

	/**
	 * обновляем ключ шифрования
	 *
	 * @throws busException
	 * @throws cs_RowIsEmpty
	 */
	public static function refreshSecretKey(string $token):string {

		// генерим новый ключ шифрования
		$secret_key = Domain_Userbot_Entity_Token::generateSecretKey();

		// получаем сущность токена
		$token_obj = Gateway_Db_PivotUserbot_TokenList::get($token);

		// устанавливаем новый ключ
		$extra = Domain_Userbot_Entity_Token::setSecretKey($token_obj->extra, $secret_key);

		// обновляем
		$set = [
			"extra"      => $extra,
			"updated_at" => time(),
		];
		Gateway_Db_PivotUserbot_TokenList::set($token, $set);

		// чистим данные по боту в кэше
		Gateway_Socket_UserbotCache::clearUserbotCache($token);

		return $secret_key;
	}

	/**
	 * обновляем токен бота
	 *
	 * @throws parseException
	 * @throws queryException
	 * @throws returnException
	 * @throws cs_RowIsEmpty
	 * @throws cs_SocketRequestIsFailed
	 */
	public static function refreshToken(string $old_token):string {

		// получаем запись бота по старому токену
		$old_token_obj = Gateway_Db_PivotUserbot_TokenList::get($old_token);

		// получаем новый токен
		$new_token = self::_refreshToken($old_token_obj->userbot_id, $old_token_obj->created_at, $old_token_obj->extra);

		// удаляем неактуальную запись по старому токену
		Gateway_Db_PivotUserbot_TokenList::delete($old_token_obj->token);

		// чистим данные бота в кэше по старому токену
		Gateway_Socket_UserbotCache::clearUserbotCache($old_token_obj->token);

		return $new_token;
	}

	/**
	 * обновляем токен бота
	 *
	 * @throws queryException
	 */
	protected static function _refreshToken(string $userbot_id, int $created_at, array $extra):string {

		// генерим новый токен
		$new_token = Domain_Userbot_Entity_Token::generateToken();

		// добавляем новый токен
		try {
			Gateway_Db_PivotUserbot_TokenList::insert($new_token, $userbot_id, $created_at, $extra);
		} catch (cs_RowDuplication) {
			return self::_refreshToken($userbot_id, $created_at, $extra);
		}

		return $new_token;
	}

	/**
	 * Изменяем бота
	 *
	 * @throws busException
	 * @throws cs_DecryptHasFailed
	 * @throws cs_FileIsNotImage
	 * @throws cs_RowIsEmpty
	 * @throws parseException
	 * @throws queryException
	 * @throws returnException
	 */
	public static function edit(string $userbot_id, string $token, string|false $userbot_name, string|false $webhook, int|false $is_react_command, int|false $avatar_color_id, string $client_launch_uuid):void {

		// если переданы данные по токену бота
		if ($is_react_command !== false || $webhook !== false) {

			// редактируем данные токена для бота
			$token_obj = Gateway_Db_PivotUserbot_TokenList::get($token);
			self::_editUserbotTokenInfo($token_obj, $is_react_command, $webhook);

			// чистим данные по боту в кэше
			Gateway_Socket_UserbotCache::clearUserbotCache($token);
		}

		// если изменился бот
		if ($avatar_color_id !== false || $userbot_name !== false) {

			// редактируем данные бота
			$userbot = Gateway_Db_PivotUserbot_UserbotList::get($userbot_id);
			self::_editUserbotInfo($userbot, $avatar_color_id);

			// редактируем данные бота как пользователя
			self::_editUserInfo($userbot->user_id, $userbot_name, $avatar_color_id, $client_launch_uuid);
		}
	}

	/**
	 * редактируем данные бота по токенам
	 */
	protected static function _editUserbotTokenInfo(Struct_Db_PivotUserbot_Token $token, int|false $is_react_command, string|false $webhook):void {

		$extra = $token->extra;

		if ($webhook !== false) {
			$token->extra = Domain_Userbot_Entity_Token::setWebhook($token->extra, $webhook);
		}

		if ($is_react_command !== false) {
			$token->extra = Domain_Userbot_Entity_Token::setFlagReactCommand($token->extra, $is_react_command);
		}

		// если данные не изменились
		if ($extra == $token->extra) {
			return;
		}

		Gateway_Db_PivotUserbot_TokenList::set($token->token, [
			"extra"      => $token->extra,
			"updated_at" => time(),
		]);
	}

	/**
	 * редактируем данные бота
	 */
	protected static function _editUserbotInfo(Struct_Db_PivotUserbot_Userbot $userbot, int|false $avatar_color_id):void {

		if ($avatar_color_id === false) {
			return;
		}

		$userbot->extra = Domain_Userbot_Entity_Userbot::setAvatarColorId($userbot->extra, $avatar_color_id);
		Gateway_Db_PivotUserbot_UserbotList::set($userbot->userbot_id, [
			"extra"      => $userbot->extra,
			"updated_at" => time(),
		]);
	}

	/**
	 * редактируем данные бота как пользователя
	 *
	 * @throws cs_DecryptHasFailed
	 * @throws cs_RowIsEmpty
	 * @throws parseException
	 * @throws queryException
	 * @throws returnException
	 * @throws cs_FileIsNotImage
	 */
	protected static function _editUserInfo(int $user_id, string|false $userbot_name, int|false $avatar_color_id, string $client_launch_uuid):bool|string {

		$avatar_file_map = false;
		if ($avatar_color_id !== false) {

			$avatar_file_key = Domain_Userbot_Entity_Userbot::getDefaultUserbotAvatar($avatar_color_id);
			$avatar_file_map = Type_Pack_File::doDecrypt($avatar_file_key);
		}
		Domain_User_Action_UpdateProfile::do($user_id, $userbot_name, $avatar_file_map, $client_launch_uuid);

		return $avatar_file_map;
	}

	/**
	 * получаем информацию по токену бота
	 *
	 * @throws cs_RowIsEmpty
	 */
	public static function getInfo(string $token):array {

		$token = Gateway_Db_PivotUserbot_TokenList::get($token);

		$userbot = Gateway_Db_PivotUserbot_UserbotList::get($token->userbot_id);

		$company                  = Domain_Company_Entity_Company::get($userbot->company_id);
		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");

		return [
			$token->userbot_id,
			$userbot->status,
			$company->url,
			$domino_entrypoint_config[$company->domino_id]["private_entrypoint"],
			$company->company_id,
			Domain_Userbot_Entity_Token::getSecretKey($token->extra),
			Domain_Userbot_Entity_Token::getFlagReactCommand($token->extra),
			$userbot->user_id,
			$userbot->extra,
		];
	}

	/**
	 * выполняем команду бота
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws paramException
	 * @throws parseException
	 * @throws returnException
	 */
	public static function doCommand(array $payload, int $company_id):void {

		// !!! если не stage и не тестовый, то выполнение запрещено
		if (!isStageServer() && !isTestServer()) {
			throw new ParamException("it does not work anywhere else stage or test server");
		}

		// достаём компанию
		$company = Domain_Company_Entity_Company::get($company_id);

		// выполняем запрос
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);
		Gateway_Socket_Company::doCommand($payload, $company_id, $company->domino_id, $private_key);
	}

	/**
	 * получаем информацию по токену бота (Только для тестов!!!)
	 */
	public static function getLastCreated():array {

		assertTestServer();

		$token = Gateway_Db_PivotUserbot_TokenList::getLastRow();

		return [
			$token->token,
			$token->userbot_id,
			Domain_Userbot_Entity_Token::getSecretKey($token->extra),
		];
	}

	/**
	 * устанавливаем версию webhook боту
	 *
	 * @throws parseException
	 * @throws returnException
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 * @throws cs_RowIsEmpty
	 * @throws cs_SocketRequestIsFailed
	 */
	public static function setWebhookVersion(string $userbot_id, string $token, int $version):void {

		// проверяем версию на корректность
		if ($version < 1 || $version > Domain_Userbot_Entity_Userbot::LAST_WEBHOOK_VERSION) {
			throw new Domain_Userbot_Exception_IncorrectParam("incorrect webhook version = {$version}");
		}

		// получаем бота из базы
		$userbot = Gateway_Db_PivotUserbot_UserbotList::get($userbot_id);

		// устанавливаем версию webhook в extra
		$extra = Domain_Userbot_Entity_Userbot::setWebhookVersion($userbot->extra, $version);

		// обновляем данные в базе
		$set = [
			"extra"      => $extra,
			"updated_at" => time(),
		];
		Gateway_Db_PivotUserbot_UserbotList::set($userbot_id, $set);

		// чистим данные по боту в кэше
		Gateway_Socket_UserbotCache::clearUserbotCache($token);
	}
}
