<?php

namespace Compass\Pivot;

/**
 * Класс для работы с отправкой алертов по пользователям
 */
class Domain_User_Entity_Alert {

	/**
	 * Выполняет отправку сообщения.
	 */
	public static function send(string $token, string $project, string $group_id, string $text):void {

		Type_System_Admin::log("user_alert", $text);

		// проверяем, что параметры корректные
		if ($token === "" || $project === "" || $group_id === "") {
			return;
		}

		Type_Notice_Compass::sendGroup(
			$project,
			$token,
			$group_id,
			$text
		);
	}

	/**
	 * Отправляет уведомление о том, что пользователь повторно запросил смс-сообщение.
	 */
	public static function onSmsResent(int $user_id, string $phone_number, int $remain_attempt_count, string $action, string|null $country_name, string|null $sms_id):void {

		$action = match ($action) {
			"2fa"          => "двухфакторная авторизация",
			"auth"         => "аутентификация в приложение",
			"change_phone" => "смена номера телефона",
			default        => "неопределенное действие",
		};

		$message = $user_id !== 0
			? "Пользователь $user_id запросил повторную отправку смс-сообщения на номер $phone_number.\n"
			: "Пользователь запросил повторную отправку смс-сообщения на номер $phone_number.\n";

		$message .= "Действие: $action.\n";
		$message .= "Оставшееся число попыток: $remain_attempt_count.\n";

		if (!is_null($country_name)) {
			$message .= "Страна получателя: $country_name.\n";
		}

		if (!is_null($sms_id)) {
			$message .= self::_appendInfoAboutSendingAttempts($sms_id);
		}

		static::send(COMPASS_NOTICE_BOT_TOKEN, COMPASS_NOTICE_BOT_PROJECT, SMS_ALERT_COMPASS_NOTICE_GROUP_ID, $message);
	}

	/**
	 * добавляем информацию о попытках отправки
	 *
	 * @return string
	 */
	protected static function _appendInfoAboutSendingAttempts(string $sms_id):string {

		// получаем историю по sms_id
		$sms_history_list = Gateway_Db_PivotHistoryLogs_SendHistory::getBySmsId($sms_id);

		// если записей в истории не оказалось
		if (count($sms_history_list) < 1) {
			return "Информация по прошлым попыткам отправки не найдена\n";
		}

		// сортируем по created_at
		usort($sms_history_list, function(Struct_PivotHistoryLogs_SendHistory $a, Struct_PivotHistoryLogs_SendHistory $b) {

			return $b->created_at <=> $a->created_at;
		});

		// формируем сообщение
		$provider_attempt_list = [];
		foreach ($sms_history_list as $sms_history) {

			$result_text             = $sms_history->is_success == 1 ? "успешно" : "безуспешно";
			$provider_attempt_list[] = "{$sms_history->provider_id} ($result_text)";
		}

		return "Прошлая попытка отправки производилась провайдерами: " . implode(", ", $provider_attempt_list) . ".\n";
	}

	/**
	 * Отправляет уведомление неуспешных использованиях смс за последний час
	 */
	public static function onUnusedSmsReportDay(int $failed_percent):void {

		$message = "За последние 24 часа $failed_percent% действий, требующих подтверждения по смс, не были завершены.";
		static::send(COMPASS_NOTICE_BOT_TOKEN, COMPASS_NOTICE_BOT_PROJECT, SMS_ALERT_COMPASS_NOTICE_GROUP_ID, $message);
	}

	/**
	 * Отправляет уведомление неуспешных использованиях смс за последний час
	 */
	public static function onUnusedSmsReportHour(int $failed_percent):void {

		$message = "За последний час $failed_percent% действий, требующих подтверждения по смс, не были завершены.";
		static::send(COMPASS_NOTICE_BOT_TOKEN, COMPASS_NOTICE_BOT_PROJECT, SMS_ALERT_COMPASS_NOTICE_GROUP_ID, $message);
	}

	/**
	 * Отправляет уведомление на ввод некорретной пригласительной ссылки после регистрации
	 */
	public static function onTryValidateIncorrectLink(int $user_id, string $link):void {

		$message = $user_id !== 0
			? "Пользователь $user_id ввел некорректную пригласительную ссылку после регистрации: $link"
			: "Пользователь ввел некорректную пригласительную ссылку после регистрации: $link";
		static::send(COMPASS_NOTICE_BOT_TOKEN, COMPASS_NOTICE_BOT_PROJECT, SMS_ALERT_COMPASS_NOTICE_GROUP_ID, $message);
	}
}