<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use PhpParser\Error;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для связи пользователей из Slack c пользователями в Compass
 */
class Migration_Import_User {

	protected const  _RAW_TABLE_NAME   = "raw_user";
	protected const  _BOUND_TABLE_NAME = "bound_user";
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * стартовая функция скрипта
	 */
	public function run():void {

		if (Type_Script_InputParser::getArgumentValue("--help", Type_Script_InputParser::TYPE_NONE, false, false) === true) {

			console("Параметры:");
			console("--dry = запуск в тестовом/рабочем режиме");
			console("--company-id = id компании");
			exit(1);
		}

		// параметры
		$is_dry                      = Type_Script_InputHelper::isDry();
		$company_id                  = Type_Script_InputParser::getArgumentValue("--company-id", Type_Script_InputParser::TYPE_INT, 0, false);
		$this->_local_manticore_host = Type_Script_InputParser::getArgumentValue("--local_manticore_host", Type_Script_InputParser::TYPE_STRING, "82.148.27.130");
		$this->_local_manticore_port = Type_Script_InputParser::getArgumentValue("--local_manticore_port", Type_Script_InputParser::TYPE_INT, 9306);

		$company = Domain_Company_Entity_Company::get($company_id);

		// делаем запрос в мантикору, получаем список пользователей
		$query     = "SELECT * FROM ?t WHERE ?i=?i ORDER BY id ASC LIMIT ?i OFFSET ?i";
		$user_list = self::_manticore()->select($query, [self::_RAW_TABLE_NAME, 0, 0, 200000, 0]);

		// список пользователей которых нужно будет уволить
		$delete_user_list = [];
		$delete_bot_list  = [];

		// список пользователей
		$bound_user_list = [];

		foreach ($user_list as $user) {

			// проверяем что пользователь есть в компании
			try {

				if ($user["expected_user_id"] == 0 || $user["expected_user_id"] < 1 || $user["expected_user_id"] > 9999999) {
					throw new \cs_RowIsEmpty();
				}

				$company_user_row = Gateway_Db_PivotCompany_CompanyUserList::getOne($company_id, $user["expected_user_id"]);
				$user_id          = $company_user_row->user_id;
				$user_info        = Gateway_Bus_PivotCache::getUserInfo($user_id);
				$full_name        = $user_info->full_name;
			} catch (\cs_RowIsEmpty) {

				// dry-run
				if ($is_dry) {

					console("DRY-RUN!!! Регистрация нового пользователя, добавление в компанию и удаление c expected_user_id = " . $user["expected_user_id"] . " и uniq = " . $user["uniq"]);
					continue;
				}

				// если нет - регистрируем нового пользователя
				$user_id   = self::_createUser($user["name"]);
				$full_name = $user["name"];

				// добавляем пользователя в компанию
				Domain_Company_Action_Member_AddByRole::do(
					$user_id,
					Domain_Company_Entity_User_Member::ROLE_MEMBER,
					$company,
					\BaseFrame\System\Locale::getLocale(),
				);

				// добавляем в список для последующего удаления профиля
				if ($user["is_bot"] == 0) {
					$delete_user_list[] = $user_id;
				}

				if ($user["is_bot"] == 1) {
					$delete_bot_list[] = $user_id;
				}

				Type_System_Admin::log("slack_user", "Зарегистрировали пользователя user_id = {$user_id} для expected_user_id = " . $user["expected_user_id"] . " и uniq = " . $user["uniq"]);
				console("Зарегистрировали пользователя user_id = {$user_id} для expected_user_id = " . $user["expected_user_id"] . " и uniq = " . $user["uniq"]);
			}

			// записываем user_id
			$bound_user_list[] = [
				"uniq"      => $user["uniq"],
				"user_id"   => $user_id,
				"full_name" => $full_name,
			];

			Type_System_Admin::log("slack_user", "Подготовили для записи пользователя user_id = {$user_id} для expected_user_id = " . $user["expected_user_id"] . " и uniq = " . $user["uniq"]);
			console("Подготовили для записи пользователя user_id = {$user_id} для expected_user_id = " . $user["expected_user_id"] . " и uniq = " . $user["uniq"]);
		}

		// dry-run
		if ($is_dry) {

			console("DRY-RUN отработал!!!");
			return;
		}

		self::_manticore()->insert(self::_BOUND_TABLE_NAME, $bound_user_list);

		// если нет пользователей для увольнения, завершаем выполнение
		if ($delete_user_list == [] && $delete_bot_list == []) {
			return;
		}

		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		if (count($delete_user_list) > 0) {

			// удаляем пользователей из списка чанками
			$chunk_delete_user_list = array_chunk($delete_user_list, 30);
			foreach ($chunk_delete_user_list as $chunk_delete_user) {

				// ждем чтобы размазать нагрузку
				sleep(5);

				foreach ($chunk_delete_user as $delete_user_id) {

					// устанавливаем описание в профиль - сокет запрос
					try {

						Gateway_Socket_Company::updateMemberInfo(
							$company->domino_id, $company->company_id, $private_key,
							$delete_user_id, "Уволенный сотрудник", false, false, false,
						);
					} catch (\cs_SocketRequestIsFailed|Gateway_Socket_Exception_CompanyIsNotServed|cs_CompanyIsHibernate|Error) {

						Type_System_Admin::log("slack_user", "Не смогли поменять описание пользователю user_id = {$delete_user_id}");
						console("Не смогли поменять описание пользователю user_id = {$delete_user_id}");
					}

					self::_deleteProfile($delete_user_id);
					Type_System_Admin::log("slack_user", "Удален пользователь user_id = {$delete_user_id}");
					console("Удален пользователь user_id = {$delete_user_id}");
				}
			}
		}

		if (count($delete_bot_list) > 0) {

			// удаляем ботов из списка чанками
			$chunk_delete_bot_list = array_chunk($delete_bot_list, 30);
			foreach ($chunk_delete_bot_list as $chunk_delete_bot) {

				// ждем чтобы размазать нагрузку
				sleep(5);

				foreach ($chunk_delete_bot as $delete_user_id) {

					self::_deleteProfile($delete_user_id);
					Type_System_Admin::log("slack_user", "Удален пользователь-бот user_id = {$delete_user_id}");
					console("Удален пользователь user_id = {$delete_user_id}");
				}
			}
		}

		console("Успешное выполнение скрипта!!!");
	}

	/**
	 * Розетка для временного подключения контейнера manticore
	 */
	protected function _manticore():\BaseFrame\Search\Manticore {

		$conf = [
			"host" => $this->_local_manticore_host,
			"port" => $this->_local_manticore_port,
		];

		// получаем конфиг с базой данных
		return \BaseFrame\Search\Provider::instance()->connect(new \BaseFrame\Search\Config\Connection(...$conf));
	}

	/**
	 * функция для создания пользователей
	 *
	 * @param string $full_name
	 * @param string $user_agent
	 *
	 * @return int
	 * @throws InvalidPhoneNumber
	 * @throws \queryException
	 * @throws cs_DamagedActionException
	 * @throws Domain_User_Exception_Mail_BelongAnotherUser
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 */
	protected function _createUser(string $full_name, string $user_agent = "Compass (5.2.0-beta.2) Electron darwin"):int {

		$user = Domain_User_Action_Create_Human::do(
			"", "", "", $user_agent, getIp(), $full_name, "", []
		);

		return $user->user_id;
	}

	/**
	 * удаляем аккаунт пользователя
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 * @throws cs_UserAlreadyBlocked
	 * @throws cs_UserNotFound
	 */
	protected function _deleteProfile(int $user_id):void {

		$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);

		// проверяем, может профиль уже заблочен и у пользователя не привязан номер телефона
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		if (Type_User_Main::isDisabledProfile($user_info->extra)) {
			throw new cs_UserAlreadyBlocked("user is already blocked");
		}

		// выполняем основные действия по удалению аккаунта
		Domain_User_Action_DeleteProfile::do($user_id, $user_security);

		// отправляем задачу на удаление профиля в intercom
		Gateway_Socket_Intercom::userProfileDeleted($user_id);
	}
}

// запускаем
(new Migration_Import_User())->run();