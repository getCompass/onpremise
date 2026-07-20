<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Создать новый API ключ
 *
 * скрипт без dry-run, запускается сразу, безопасен для повторного выполнения
 */
class Integration_Create_Api_Key
{
	/**
	 * Стартовая функция скрипта
	 */
	public function start(): void
	{

		$available_scope_names = Gateway_Bus_Auth::getAvailableScopeNames();

		// ввести id пользователя
		$user = self::_setUser();

		// ввести имя ключа
		$name = self::_setName();

		// ввести время истечения API ключа
		$expires_at = self::_setExpiresAt();

		// выбрать зоны ответственности
		$scope_list = self::_pickScopeList($available_scope_names);

		// выбираем права в зонах ответственности
		$scope_list = self::_pickScopeRights($scope_list);

		// подтвердить создание
		self::_confirm($user, $name, $expires_at, $scope_list);

		$api_key = Domain_Apikey_Action_Create::do($user->user_id, $name, $expires_at, $scope_list, 0);

		console(greenText("API ключ создан: {$api_key->api_key}"));
	}

	/**
	 * Устанавливает идентификатор пользователя
	 */
	protected static function _setUser(): Struct_Db_PivotUser_User
	{

		while (true) {

			console("Введите идентификатор пользователя");
			$input = readline();

			$user_id = intval($input);

			if ($user_id === 0) {

				console(redText("Передан неверный идентификатор пользователя"));
				continue;
			}

			try {
				$user = Gateway_Bus_PivotCache::getUserInfo($user_id);
			} catch (cs_UserNotFound) {
				console(redText("Введеный пользователь не найден"));
				continue;
			}

			return $user;
		}
	}

	/**
	 * Устанавливает имя ключа
	 */
	protected static function _setName(): string
	{

		while (true) {

			console("Введите имя ключа");
			$input = readline();
			$name  = Domain_Apikey_Entity_Sanitizer::sanitizeApikeyName($input);

			if ($name === "") {

				console(redText("Введено неверное имя для ключа"));
				continue;
			}

			return $name;
		}
	}

	/**
	 * Устанавливает время истечения API ключа
	 */
	protected static function _setExpiresAt(): int
	{

		while (true) {

			console("Укажите время истечения API ключа в формате unix timestamp, (по умолчанию 0 — никогда не истекает)");

			$input = readline();

			if ($input !== "" && !is_numeric($input)) {

				console(redText("Передано время в неизвестном формате"));
				continue;
			}

			$expires_at = $input !== "" ? intval($input) : 0;

			if ($expires_at === 0) {
				$expires_at = MAX_UNSIGNED_INT32;
			}

			if ($expires_at < time()) {

				console(redText("Передано время меньше текущего"));
				continue;
			}

			return $expires_at;
		}

	}

	/**
	 * Выбрать зоны ответственности, к которомы будет иметь доступ api ключ
	 */
	protected static function _pickScopeList(array $scope_names): array
	{

		$text = "Выберите зоны ответственности API ключа через запятую (1,5,8)";

		foreach ($scope_names as $scope_id => $scope_name) {
			$text .= "\n{$scope_id}) {$scope_name}";
		}

		console($text);

		while (true) {

			$picked              = readline();
			$picked_scope_list   = explode(",", $picked);
			$prepared_scope_list = [];
			$has_error           = false;
			foreach ($picked_scope_list as $scope) {

				if ($scope === "") {
					continue;
				}

				$scope = (int) $scope;
				if (isset($scope_names[$scope])) {

					$prepared_scope_list[] = $scope_names[$scope];
					continue;
				}

				$has_error = true;
				break;

			}

			if (!$has_error) {

				console("Выбраны следующие зоны ответственности:");
				console(yellowText(implode("\n", $prepared_scope_list)));
				return $prepared_scope_list;
			}

			console(redText("Выбраны некорректные зоны ответственности, попробуйте еще раз"));
			console($text);
		}
	}

	/**
	 * Выбрать права в зонах ответственности
	 */
	protected static function _pickScopeRights(array $prepared_scope_list): array
	{

		$ready_scope_list = [];
		$text             = "Для каждой зоны ответственности выберите, какие права будет иметь ключ - чтение или запись:";
		$rights_text      = "1) Чтение\n2) Запись";

		console($text);

		foreach ($prepared_scope_list as $scope) {

			console(yellowText("Зона {$scope}"));

			while (true) {

				console($rights_text);
				$input = (int) readline();

				$scope_right = match($input) {
					1       => "read",
					2       => "write",
					default => null,
				};

				if (is_null($scope_right)) {

					console(redText("Неверно выбраны права для зоны ответственности"));
					continue;
				}

				$ready_scope_list[$scope] = $scope_right;
				break;
			}
		}

		return $ready_scope_list;
	}

	/**
	 * Подтвердить создание ключа
	 */
	protected static function _confirm(Struct_Db_PivotUser_User $user, string $name, int $expires_at, array $scope_list): void
	{

		$scope_list_text = "";

		foreach ($scope_list as $scope => $right) {
			$scope_list_text .= "{$scope} => {$right}\n";
		}

		console("Выбранные параметры:
		\nПользователь:{$user->user_id}) {$user->full_name}
		\nНазвание ключа: {$name}
		\nВремя истечения: {$expires_at}
		\nВыбранные права:\n {$scope_list_text}");

		if (!Type_Script_InputHelper::assertConfirm("Выполняем создание ключа [y/n]?")) {

			console(redText("Создание ключа отменено"));
			exit(1);
		}
	}
}

(new Integration_Create_Api_Key())->start();
