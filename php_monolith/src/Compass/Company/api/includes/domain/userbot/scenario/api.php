<?php

namespace Compass\Company;

/**
 * сценарии пользовательского бота для API
 */
class Domain_Userbot_Scenario_Api {

	/**
	 * сценарий создания бота
	 *
	 * @param int          $creator_role
	 * @param int          $creator_permissions
	 * @param string       $userbot_name
	 * @param int          $avatar_color_id
	 * @param string|false $short_description
	 * @param int          $is_react_command
	 * @param string|false $webhook
	 *
	 * @return array
	 * @throws Domain_Userbot_Exception_CreateLimit
	 * @throws Domain_Userbot_Exception_EmptyWebhook
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \cs_InvalidProfileName
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function create(int       $creator_role, int $creator_permissions, string $userbot_name,
						int|false $avatar_color_id, string|false $avatar_file_key, string|false $short_description,
						int       $is_react_command, string|false $webhook,
						int       $is_smart_app, string|false $smart_app_name, string|false $smart_app_url,
						int|false $is_smart_app_sip, int|false $is_smart_app_mail,
						int|false $smart_app_default_width, int|false $smart_app_default_height):array {

		// проверяем имя и описание бота на корректность
		$userbot_name = Domain_Userbot_Entity_Sanitizer::sanitizeName($userbot_name);
		\Entity_Validator::assertValidProfileName($userbot_name);
		$short_description = Domain_Member_Entity_Sanitizer::sanitizeDescription($short_description);

		// если не передали - берем случайный из доступных
		if ($avatar_color_id === false) {
			$avatar_color_id = Domain_Userbot_Entity_Userbot::getRandomAvatarColorId();
		}

		Domain_Userbot_Entity_Validator::assertCorrectFlagReactCommand($is_react_command);
		Domain_Userbot_Entity_Validator::assertCorrectFlagSmartApp($is_smart_app);
		Domain_Userbot_Entity_Validator::assertCorrectFlagSmartApp($is_smart_app_sip);
		Domain_Userbot_Entity_Validator::assertCorrectFlagSmartApp($is_smart_app_mail);
		Domain_Userbot_Entity_Validator::assertCorrectSmartAppSideResolution($smart_app_default_width);
		Domain_Userbot_Entity_Validator::assertCorrectSmartAppSideResolution($smart_app_default_height);
		Domain_Userbot_Entity_Validator::assertCorrectAvatarColorId($avatar_color_id);
		Domain_Userbot_Entity_Validator::assertCorrectAvatarFileKey($avatar_file_key);

		// если включен флаг реагирования на команды но вебхук не передан
		if ($is_react_command == 1 && ($webhook === false || isEmptyString($webhook))) {
			throw new Domain_Userbot_Exception_EmptyWebhook("webhook is empty");
		}

		// если передан вебхук
		if ($is_react_command == 1 && $webhook !== false) {

			$webhook = Domain_Userbot_Entity_Sanitizer::sanitizeWebhookUrl($webhook);
			Domain_Userbot_Entity_Validator::assertCorrectWebhook($webhook);
		}

		// если включен флаг smart_app, но smart_app_name не передан
		if ($is_smart_app == 1 && ($smart_app_name === false || isEmptyString($smart_app_name))) {
			throw new Domain_Userbot_Exception_EmptySmartAppName("smart_app_name is empty");
		}

		// если включен флаг smart_app, но smart_app_url не передан
		if ($is_smart_app == 1 && ($smart_app_url === false || isEmptyString($smart_app_url))) {
			throw new Domain_Userbot_Exception_EmptySmartAppUrl("smart_app_url is empty");
		}

		// если передан smart_app_name
		if ($is_smart_app == 1 && $smart_app_name !== false) {

			$smart_app_name = Domain_Userbot_Entity_Sanitizer::sanitizeSmartAppName($smart_app_name);
			Domain_Userbot_Entity_Validator::assertCorrectSmartAppName($smart_app_name);
		}

		// если передан smart_app_url
		if ($is_smart_app == 1 && $smart_app_url !== false) {

			$smart_app_url = Domain_Userbot_Entity_Sanitizer::sanitizeSmartAppUrl($smart_app_url);
			Domain_Userbot_Entity_Validator::assertCorrectSmartAppUrl($smart_app_url);
		}

		// проверяем, что пользователь имеет права программиста бота
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($creator_role, $creator_permissions);

		// проверяем что другого такого имени в команде нет
		if ($smart_app_name !== false) {
			Domain_Userbot_Entity_Validator::assertUniqSmartAppName($smart_app_name);
		}

		// создаём бота
		[$userbot, $sensitive_data] = Domain_Userbot_Action_Create::do(
			$userbot_name, $short_description, $avatar_color_id, $avatar_file_key, $is_react_command, $webhook,
			$is_smart_app, $smart_app_name, $smart_app_url, $is_smart_app_sip, $is_smart_app_mail, $smart_app_default_width, $smart_app_default_height
		);

		return [$userbot, $sensitive_data];
	}

	/**
	 * получаем список ботов
	 *
	 * @param int $developer_role
	 * @param int $developer_permissions
	 * @param int $filter_active
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 */
	public static function getList(int $developer_role, int $developer_permissions, int $filter_active):array {

		// проверяем, что пользователь имеет права программиста бота
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// получаем ботов
		$userbot_list = Gateway_Db_CompanyData_UserbotList::getAll();

		$list = [];
		foreach ($userbot_list as $userbot) {

			// если нужны только активные боты и текущий бот не включён, то пропускаем
			if ($filter_active == 1 && $userbot->status_alias != Domain_Userbot_Entity_Userbot::STATUS_ENABLE) {
				continue;
			}

			$list[] = [
				"userbot_id" => $userbot->userbot_id,
				"user_id"    => $userbot->user_id,
				"status"     => $userbot->status_alias,
			];
		}

		return $list;
	}

	/**
	 * редактируем бота
	 *
	 * @param int          $developer_user_id
	 * @param int          $developer_role
	 * @param int          $developer_permissions
	 * @param string       $userbot_id
	 * @param string|false $userbot_name
	 * @param string|false $short_description
	 * @param int|false    $avatar_color_id
	 * @param int|false    $is_react_command
	 * @param string|false $webhook
	 * @param int|false    $is_smart_app
	 * @param string|false $smart_app_url
	 *
	 * @return int
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_DisabledStatus
	 * @throws Domain_Userbot_Exception_EmptyParam
	 * @throws Domain_Userbot_Exception_EmptyWebhook
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \blockException
	 * @throws \busException
	 * @throws \cs_InvalidProfileName
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function edit(int          $developer_user_id, int $developer_role, int $developer_permissions, string $userbot_id,
					    string|false $userbot_name, string|false $short_description, int|false $avatar_color_id, string|false $avatar_file_key,
					    int|false    $is_react_command, string|false $webhook,
					    int|false    $is_smart_app, string|false $smart_app_name, string|false $smart_app_url,
					    int|false    $is_smart_app_sip, int|false $is_smart_app_mail,
					    int|false    $smart_app_default_width, int|false $smart_app_default_height):int {

		// валидируем параметры для бота
		[$userbot_name, $short_description, $webhook, $smart_app_name, $smart_app_url] = Domain_Userbot_Action_ValidateParamsOnEdit::do(
			$userbot_name, $short_description, $avatar_color_id, $avatar_file_key, $is_react_command, $webhook,
			$is_smart_app, $smart_app_name, $smart_app_url, $is_smart_app_sip, $is_smart_app_mail, $smart_app_default_width, $smart_app_default_height
		);

		// проверяем, что пользователь администратор ботов
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// получаем информацию по боту
		[$userbot, $_] = Domain_Userbot_Action_Get::do($userbot_id, $developer_user_id);

		// если бот отключён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DISABLE) {
			throw new Domain_Userbot_Exception_DisabledStatus("userbot is not enabled");
		}

		// если бот удалён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DELETE) {
			throw new Domain_Userbot_Exception_DeletedStatus("userbot is not enabled");
		}

		// проверяем что другого такого имени в команде нет
		if ($smart_app_name !== false) {
			Domain_Userbot_Entity_Validator::assertUniqSmartAppName($smart_app_name);
		}

		// редактируем бота
		Domain_Userbot_Action_Edit::do($userbot, $userbot_name, $short_description, $avatar_color_id, $avatar_file_key, $is_react_command, $webhook,
			$is_smart_app, $smart_app_name, $smart_app_url, $is_smart_app_sip, $is_smart_app_mail, $smart_app_default_width, $smart_app_default_height
		);

		return $userbot->user_id;
	}

	/**
	 * Получаем данные для карточки бота
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \returnException
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 */
	public static function getCard(int $user_id, string $userbot_id):array {

		// получаем информацию по боту
		return Domain_Userbot_Action_Get::do($userbot_id, $user_id);
	}

	/**
	 * получаем секьюрные данные бота
	 *
	 * @param int    $developer_user_id
	 * @param int    $developer_role
	 * @param int    $developer_permissions
	 * @param string $userbot_id
	 *
	 * @return Struct_Domain_Userbot_SensitiveData
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \blockException
	 * @throws \cs_DecryptHasFailed
	 * @long
	 */
	public static function getSensitiveData(int $developer_user_id, int $developer_role,
							    int $developer_permissions, string $userbot_id):Struct_Domain_Userbot_SensitiveData {

		// проверяем права пользователя
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// получаем данные бота
		[$userbot, $_] = Domain_Userbot_Action_Get::do($userbot_id, $developer_user_id);

		// если бот уже удалён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DELETE) {
			throw new Domain_Userbot_Exception_DeletedStatus("userbot is deleted");
		}

		$userbot_joined_at             = [];
		$conversation_map_list         = [];
		$userbot_conversation_rel_list = Gateway_Db_CompanyData_UserbotConversationRel::getByUserbotId($userbot_id);
		foreach ($userbot_conversation_rel_list as $userbot_conversation_rel) {

			$conversation_map_list[]              = $userbot_conversation_rel->conversation_map;
			$conversation_key                     = Type_Pack_Conversation::doEncrypt($userbot_conversation_rel->conversation_map);
			$userbot_joined_at[$conversation_key] = $userbot_conversation_rel->created_at;
		}

		// получаем данные по группам для бота
		$group_info_list = Gateway_Socket_Conversation::getUserbotGroupInfoList($conversation_map_list);

		foreach ($group_info_list as $index => $group_info) {
			$group_info_list[$index]["joined_at"] = $userbot_joined_at[$group_info["conversation_key"]];
		}

		$token                = Domain_Userbot_Entity_Userbot::getToken($userbot->extra);
		$secret_key           = Domain_Userbot_Entity_Userbot::getSecretKey($userbot->extra);
		$is_react_command     = Domain_Userbot_Entity_Userbot::getFlagReactCommand($userbot->extra);
		$webhook              = Domain_Userbot_Entity_Userbot::getWebhook($userbot->extra);
		$avatar_color_id      = Domain_Userbot_Entity_Userbot::getAvatarColorId($userbot->extra);
		$avatar_file_key      = Domain_Userbot_Entity_Userbot::getAvatarFileKey($userbot->extra);
		$smart_app_public_key = Domain_Userbot_Entity_Userbot::getSmartAppPublicKey($userbot->extra);

		// миграция для заливки, чтобы не было пустых ключей
		// сработает 1 раз для каждого бота
		if ($smart_app_public_key === "") {

			// генерируем новые ключи
			[$smart_app_public_key, $smart_app_private_key] = Domain_SmartApp_Action_GenerateSmartAppKeys::do();

			// обновляем в таблице с ботами
			$userbot->extra = Domain_Userbot_Entity_Userbot::setSmartAppPublicKey($userbot->extra, $smart_app_public_key);
			$userbot->extra = Domain_Userbot_Entity_Userbot::setSmartAppPrivateKey($userbot->extra, $smart_app_private_key);
			$set            = [
				"updated_at" => time(),
				"extra"      => $userbot->extra,
			];
			Gateway_Db_CompanyData_UserbotList::set($userbot_id, $set);
		}

		return new Struct_Domain_Userbot_SensitiveData(
			$token,
			$secret_key,
			$is_react_command,
			$webhook,
			$group_info_list,
			$avatar_color_id,
			$avatar_file_key,
			$smart_app_public_key
		);
	}

	/**
	 * активируем бота
	 *
	 * @param int    $developer_user_id
	 * @param int    $developer_role
	 * @param int    $developer_permissions
	 * @param string $userbot_id
	 *
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \blockException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function enable(int $developer_user_id, int $developer_role, int $developer_permissions, string $userbot_id):void {

		// проверяем права нашего пользователя
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// достаём инфу по боту, проверяем, что есть такой бот
		[$userbot, $_] = Domain_Userbot_Action_Get::do($userbot_id, $developer_user_id);

		// включаем бота
		Domain_Userbot_Action_Enable::do($userbot);
	}

	/**
	 *  деактивируем бота
	 *
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_IsNotDeveloper
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function disable(int $developer_user_id, int $developer_role, int $developer_permissions, string $userbot_id):int {

		// проверяем права нашего пользователя
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// достаём инфу по боту, проверяем, что есть такой бот
		[$userbot, $_] = Domain_Userbot_Action_Get::do($userbot_id, $developer_user_id);

		// отключаем бота
		return Domain_Userbot_Action_Disable::do($userbot);
	}

	/**
	 * обновляем ключ шифрования для бота
	 *
	 * @param int    $developer_user_id
	 * @param int    $developer_role
	 * @param int    $developer_permissions
	 * @param string $userbot_id
	 *
	 * @return string
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_DisabledStatus
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \blockException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function refreshSecretKey(int $developer_user_id, int $developer_role, int $developer_permissions, string $userbot_id):string {

		// проверяем права нашего пользователя
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// достаём инфу по боту, проверяем, что есть такой бот
		[$userbot, $_] = Domain_Userbot_Action_Get::do($userbot_id, $developer_user_id);

		// если бот отключён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DISABLE) {
			throw new Domain_Userbot_Exception_DisabledStatus("userbot is not enabled");
		}

		// если бот удалён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DELETE) {
			throw new Domain_Userbot_Exception_DeletedStatus("userbot is not enabled");
		}

		// отправляем сокет-запрос для обновления ключа шифрования
		$token      = Domain_Userbot_Entity_Userbot::getToken($userbot->extra);
		$secret_key = Gateway_Socket_Pivot::refreshSecretKey($token);

		// обновляем ключ шифрования в таблице с ботами
		$userbot->extra = Domain_Userbot_Entity_Userbot::setSecretKey($userbot->extra, $secret_key);
		$set            = [
			"extra"      => $userbot->extra,
			"updated_at" => time(),
		];
		Gateway_Db_CompanyData_UserbotList::set($userbot_id, $set);

		return $secret_key;
	}

	/**
	 * обновляем токен бота
	 *
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_DisabledStatus
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \blockException
	 * @throws \parseException
	 */
	public static function refreshToken(int $developer_user_id, int $developer_role, int $developer_permissions, string $userbot_id):string {

		// проверяем права нашего пользователя
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// достаём инфу по боту, проверяем, что есть такой бот
		[$userbot, $_] = Domain_Userbot_Action_Get::do($userbot_id, $developer_user_id);

		// если бот отключён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DISABLE) {
			throw new Domain_Userbot_Exception_DisabledStatus("userbot is not enabled");
		}

		// если бот удалён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DELETE) {
			throw new Domain_Userbot_Exception_DeletedStatus("userbot is not enabled");
		}

		// отправляем сокет-запрос для обновления токена бота
		$token = Domain_Userbot_Entity_Userbot::getToken($userbot->extra);
		$token = Gateway_Socket_Pivot::refreshToken($token);

		// обновляем токен в таблице с ботами
		$userbot->extra = Domain_Userbot_Entity_Userbot::setToken($userbot->extra, $token);
		$set            = [
			"extra"      => $userbot->extra,
			"updated_at" => time(),
		];
		Gateway_Db_CompanyData_UserbotList::set($userbot_id, $set);

		return $token;
	}

	/**
	 * обновляем приватный и публичный ключи smart app
	 *
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_DisabledStatus
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \blockException
	 * @throws \parseException
	 */
	public static function refreshSmartAppKeys(int $developer_user_id, int $developer_role, int $developer_permissions, string $userbot_id):string {

		// проверяем права нашего пользователя
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// достаём инфу по боту, проверяем, что есть такой бот
		[$userbot, $_] = Domain_Userbot_Action_Get::do($userbot_id, $developer_user_id);

		// если бот отключён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DISABLE) {
			throw new Domain_Userbot_Exception_DisabledStatus("userbot is not enabled");
		}

		// если бот удалён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DELETE) {
			throw new Domain_Userbot_Exception_DeletedStatus("userbot is not enabled");
		}

		[$smart_app_public_key, $smart_app_private_key] = Domain_SmartApp_Action_GenerateSmartAppKeys::do();

		// обновляем в таблице с ботами
		$userbot->extra = Domain_Userbot_Entity_Userbot::setSmartAppPublicKey($userbot->extra, $smart_app_public_key);
		$userbot->extra = Domain_Userbot_Entity_Userbot::setSmartAppPrivateKey($userbot->extra, $smart_app_private_key);
		$set            = [
			"extra"      => $userbot->extra,
			"updated_at" => time(),
		];
		Gateway_Db_CompanyData_UserbotList::set($userbot_id, $set);

		return $smart_app_public_key;
	}

	/**
	 * удаляем бота
	 *
	 * @param int    $developer_user_id
	 * @param int    $developer_role
	 * @param int    $developer_permissions
	 * @param string $userbot_id
	 *
	 * @return int
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_IncorrectStatus
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \blockException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function delete(int $developer_user_id, int $developer_role, int $developer_permissions, string $userbot_id):int {

		// проверяем права нашего пользователя
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// достаём инфу по боту, проверяем, что есть такой бот
		[$userbot, $_] = Domain_Userbot_Action_Get::do($userbot_id, $developer_user_id);

		// проверяем статус - если бот не выключен, то выдаём ошибку
		if ($userbot->status_alias != Domain_Userbot_Entity_Userbot::STATUS_DISABLE) {
			throw new Domain_Userbot_Exception_IncorrectStatus("userbot is not disable");
		}

		// удаляем бота
		return Domain_Userbot_Action_Delete::do($userbot);
	}

	/**
	 * получить список ботов
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \blockException
	 */
	public static function show(int $user_id, array $userbot_id_list):array {

		$userbot_list = Gateway_Db_CompanyData_UserbotList::getList($userbot_id_list);

		if (count($userbot_id_list) != count($userbot_list)) {

			// инкрементим блокировку по user_id пользователя
			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::USERBOT_NOT_FOUND);
			throw new Domain_Userbot_Exception_UserbotNotFound("userbot not found");
		}

		$list = [];
		foreach ($userbot_list as $userbot) {

			// отдаём данные только по включённым/отключённым ботам
			if (!in_array($userbot->status_alias, [Domain_Userbot_Entity_Userbot::STATUS_ENABLE, Domain_Userbot_Entity_Userbot::STATUS_DISABLE])) {
				continue;
			}

			$list[] = Apiv2_Format::userbot($userbot);
		}

		return $list;
	}

	/**
	 * добавляем ботов в группу
	 *
	 * @param int    $developer_user_id
	 * @param int    $developer_role
	 * @param int    $developer_permissions
	 * @param array  $userbot_id_list
	 * @param string $conversation_key
	 *
	 * @throws \blockException
	 * @throws Domain_Conversation_Exception_User_NotMember
	 * @throws Domain_Userbot_Exception_IncorrectStatus
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \cs_DecryptHasFailed
	 */
	public static function addToGroup(int $developer_user_id, int $developer_role, int $developer_permissions, array $userbot_id_list, string $conversation_map):void {

		if (count($userbot_id_list) < 1 || count($userbot_id_list) > 50) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect param userbot_id_list");
		}

		// проверяем права нашего пользователя
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		// достаём инфу по ботам, проверяем, что все боты существуют
		$userbot_list = Gateway_Db_CompanyData_UserbotList::getList($userbot_id_list);
		if (count($userbot_id_list) != count($userbot_list)) {

			// инкрементим блокировку по user_id пользователя
			Type_Antispam_User::throwIfBlocked($developer_user_id, Type_Antispam_User::USERBOT_NOT_FOUND);
			throw new Domain_Userbot_Exception_UserbotNotFound("userbot not found");
		}

		$conversation_key = \CompassApp\Pack\Conversation::doEncrypt($conversation_map);

		// получаем только тех ботов, которых нужно добавить в группу
		$userbot_id_list_by_user_id = Domain_Userbot_Action_GetForAddToGroup::do($userbot_list, $conversation_key);

		if (count($userbot_id_list_by_user_id) < 1) {
			return;
		}

		// получаем тех ботов, кто ранее уже был добавлен
		$check_userbot_id_list       = array_values($userbot_id_list_by_user_id);
		$history_list                = Gateway_Db_CompanyData_UserbotConversationHistory::getListByConversationMap($check_userbot_id_list, $conversation_map);
		$already_add_userbot_id_list = array_column($history_list, "userbot_id");

		// получаем впервые добавляемых в эту группу ботов
		$first_add_userbot_id_list = array_diff(array_values($userbot_id_list_by_user_id), $already_add_userbot_id_list);

		// добавляем ботов в группу в php_compass_company
		Gateway_Socket_Conversation::addUserbotToGroup($userbot_id_list_by_user_id, $first_add_userbot_id_list, $conversation_key, $developer_user_id);

		// добавляем в таблицу бота с диалогами
		Domain_Userbot_Action_AddToGroup::do(array_values($userbot_id_list_by_user_id), $conversation_map);
	}

	/**
	 * убираем бота из группы
	 *
	 * @param int    $developer_user_id
	 * @param int    $developer_role
	 * @param int    $developer_permissions
	 * @param string $userbot_id
	 * @param string $conversation_key
	 *
	 * @throws \blockException
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 */
	public static function removeFromGroup(int $developer_user_id, int $developer_role, int $developer_permissions, string $userbot_id, string $conversation_map):void {

		// проверяем права нашего пользователя
		\CompassApp\Domain\Member\Entity\Permission::assertCanManageBots($developer_role, $developer_permissions);

		[$userbot, $_] = Domain_Userbot_Action_Get::do($userbot_id, $developer_user_id);

		// проверяем статус - если бот удалён, то выдаём ошибку
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DELETE) {
			throw new Domain_Userbot_Exception_DeletedStatus("userbot is deleted");
		}

		$userbot_id_by_user_id[$userbot->user_id] = $userbot->userbot_id;

		// убираем бота из группы в php_compass_company
		Gateway_Socket_Conversation::removeFromGroup($userbot_id_by_user_id, \CompassApp\Pack\Conversation::doEncrypt($conversation_map));

		// убираем группу из таблицы бота с диалогами
		Domain_Userbot_Action_RemoveFromGroup::do($userbot_id, $conversation_map);
	}

	/**
	 * получаем связь пользователя и бота
	 *
	 * @throws cs_WrongSignature
	 */
	public static function getUserRel(array $batch_user_list):array {

		// выбрасываем ошибку, если массив пользователей некорректен
		foreach ($batch_user_list as $user_list) {

			if (!isset($user_list["user_id_list"])) {
				throw new cs_WrongSignature("not found user_id_list");
			}

			if (!is_array($user_list["user_id_list"])) {
				throw new cs_WrongSignature("user_id_list not array");
			}
		}

		// формируем массив пользователей для запроса
		$user_id_list = [];
		foreach ($batch_user_list as $v) {
			$user_id_list = array_merge($user_id_list, $v["user_id_list"]);
		}

		// идём в таблицу с ботами по поиску user_id_list
		$userbot_list = Gateway_Db_CompanyData_UserbotList::getByUserIdList($user_id_list);

		// собираем ответ, приводим к формату и отдаём
		$userbot_user_rel = [];
		foreach ($userbot_list as $userbot) {
			$userbot_user_rel[] = Apiv2_Format::userbotUserRel($userbot);
		}

		return $userbot_user_rel;
	}
}
