<?php

namespace Compass\Pivot;

/**
 * Класс для отправки общей аналитики
 * @package Compass\Pivot
 */
class Domain_Analytic_Action_SendGeneralAnalytics {

	/**
	 * Отправляем короткий отчет за переданный промежуток
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \GetCompass\Userbot\Exception\Request\UnexpectedResponseException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_UserNotFound
	 */
	public static function sendShortReport(int $from_date, int $to_date):void {

		$message_template = "_Пользователи:_
Reg: *{registered_users_count}*, Cnew: *{unique_space_creators_count} ({unique_space_creators_conversion}%)*, Cadd: *{unique_space_joined_users_count} ({unique_space_joined_users_conversion}%)*
RegAtr: *{attr_app_registered_user_count} ({attr_app_registered_user_conversion}%)*, CaddAtr: *{attr_app_total_enter_space_count} ({attr_app_total_enter_space_conversion}%)*

_Пространства:_
New: *{created_spaces_count}*, Add: *{space_joining_count}*, Rev: *{revenue_sum}*

_Смс провайдеры:_
Sms-agent: *{sms_agent_balance}*, Vonage: *{vonage_balance}*, Twilio: *{twilio_balance}*";

		$report_metrics = self::countReportMetricsByInterval($from_date, $to_date);
		$text           = format($message_template, $report_metrics->toArray());

		// отправляем сообщение
		self::_sendMessageToBusiness($text);
	}

	/**
	 * Отправляем большой отчет за отчетный день ($reporting_day_start)/неделю отчетного дня/месяц отчетного дня
	 *
	 * @param int $reporting_day_start Время начала дня для которого отправляется отчет. Неделя и месяц высчитываются относительно отчетного дня
	 *
	 * @long
	 */
	public static function sendBigReport(int $reporting_day_start):void {

		$message_template = "_Пользователи:_
Reg: *{day_registered_users_count}*, *{week_registered_users_count}*, *{month_registered_users_count}*
Cnew: *{day_unique_space_creators_count} ({day_unique_space_creators_conversion}%)*, *{week_unique_space_creators_count} ({week_unique_space_creators_conversion}%)*, *{month_unique_space_creators_count} ({month_unique_space_creators_conversion}%)*
Cadd: *{day_unique_space_joined_users_count} ({day_unique_space_joined_users_conversion}%)*, *{week_unique_space_joined_users_count} ({week_unique_space_joined_users_conversion}%)*, *{month_unique_space_joined_users_count} ({month_unique_space_joined_users_conversion}%)*
RegAtr: *{day_attr_app_registered_user_count} ({day_attr_app_registered_user_conversion}%)*, *{week_attr_app_registered_user_count} ({week_attr_app_registered_user_conversion}%)*, *{month_attr_app_registered_user_count} ({month_attr_app_registered_user_conversion}%)*
CaddAtr: *{day_attr_app_total_enter_space_count} ({day_attr_app_total_enter_space_conversion}%)*, *{week_attr_app_total_enter_space_count} ({week_attr_app_total_enter_space_conversion}%)*, *{month_attr_app_total_enter_space_count} ({month_attr_app_total_enter_space_conversion}%)*

_Пространства:_
New: *{day_created_spaces_count}*, *{week_created_spaces_count}*, *{month_created_spaces_count}*
Add: *{day_space_joining_count}*, *{week_space_joining_count}*, *{month_space_joining_count}*
Rev: *{day_revenue_sum}*, *{week_revenue_sum}*, *{month_revenue_sum}*

_Смс провайдеры:_
Sms-agent: *{day_sms_agent_balance}*, Vonage: *{day_vonage_balance}*, Twilio: *{day_twilio_balance}*";

		// сюда сложим все реплейсменты, которые должны подставиться в шаблон выше
		$metrics_format_replacement = [];

		// интервалы за которые необходимо собрать метрики
		$interval_list = [
			["prefix" => "day_", "from_date" => $reporting_day_start, "to_date" => $reporting_day_start + DAY1,],
			["prefix" => "week_", "from_date" => weekStart($reporting_day_start), "to_date" => weekStart($reporting_day_start) + DAY1 * 7,],
			["prefix" => "month_", "from_date" => monthStart($reporting_day_start), "to_date" => monthEnd($reporting_day_start),],
		];

		// собираем метрики за интервалы
		foreach ($interval_list as $interval) {

			$metrics_array = self::countReportMetricsByInterval($interval["from_date"], $interval["to_date"])->toArray();
			foreach ($metrics_array as $key => $value) {
				$metrics_format_replacement[$interval["prefix"] . $key] = $value;
			}
		}

		$text = format($message_template, $metrics_format_replacement);

		// отправляем сообщение
		self::_sendMessageToBusiness($text);
	}

	/**
	 * Подсчитываем метрики для отчета для переданного интервала
	 *
	 * @return Struct_Analytic_BusinessReportMetrics
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_UserNotFound
	 * @long
	 */
	public static function countReportMetricsByInterval(int $from_date, int $to_date):Struct_Analytic_BusinessReportMetrics {

		// получаем количество зарегистрированных пользователей
		$registered_users_count = Domain_Analytic_Entity_General::countRegisteredUsersByInterval($from_date, $to_date);

		// получаем статистику по созданным пространствам
		[$unique_space_creators_count, $created_spaces_count] = Domain_Analytic_Entity_General::countSpaceCreationMetricsByInterval($from_date, $to_date);

		// получаем статистику по вступлениям в пространства
		$unique_space_joined_users_count = Domain_Analytic_Entity_General::countUniqueSpaceJoinedUsersByInterval($from_date, $to_date);
		$space_joining_count             = Domain_Analytic_Entity_General::countSpaceJoiningByInterval($from_date, $to_date);

		// получаем статистику по атрибуции
		[$attr_app_registered_user_count, $attr_app_total_enter_space_count] = Gateway_Socket_CollectorServer::getJoinSpaceMetricsByInterval($from_date, $to_date);

		// получаем выручку
		$revenue_sum = Domain_Analytic_Entity_General::getRevenueSumText($from_date, $to_date);

		// получаем балансы провайдеров
		try {
			$sms_agent_balance = floor(Gateway_Sms_Provider_SmsAgent::getBalance()->body) . " руб";
		} catch (\Exception|\Error) {
			$sms_agent_balance = "[\"@\"|160744|\"Не смогли получить\"]";
		}
		try {
			$vonage_balance = floor(Gateway_Sms_Provider_Vonage::getBalance()->body["value"]) . "€";
		} catch (\Exception|\Error) {
			$vonage_balance = "[\"@\"|160744|\"Не смогли получить\"]";
		}
		try {
			$twilio_balance = floor(fromJson((Gateway_Sms_Provider_Twilio::getBalance()->body))["balance"]) . "$";
		} catch (\Exception|\Error) {
			$twilio_balance = "[\"@\"|160744|\"Не смогли получить\"]";
		}

		// получаем статистику по конференциям
		[$temporary_conference_row_data, $single_conference_row_data, $permanent_conference_row_data] = Domain_Analytic_Entity_General
			::getConferenceMetrics($from_date, $to_date);

		return new Struct_Analytic_BusinessReportMetrics(
			$registered_users_count,
			$unique_space_creators_count,
			self::_caclConversion($registered_users_count, $unique_space_creators_count),
			$unique_space_joined_users_count,
			self::_caclConversion($registered_users_count, $unique_space_joined_users_count),
			$attr_app_registered_user_count,
			self::_caclConversion($registered_users_count, $attr_app_registered_user_count),
			$attr_app_total_enter_space_count,
			self::_caclConversion($attr_app_registered_user_count, $attr_app_registered_user_count),
			$created_spaces_count,
			$space_joining_count,
			$revenue_sum,
			$sms_agent_balance,
			$vonage_balance,
			$twilio_balance,
			$temporary_conference_row_data,
			$single_conference_row_data,
			$permanent_conference_row_data
		);
	}

	/**
	 * Рассчитываем конверсию
	 *
	 * @return float
	 */
	protected static function _caclConversion(int $total_number, int $target_actions_count):float {

		if ($total_number == 0) {
			return 0;
		}

		return round(($target_actions_count / $total_number) * 100, 1);
	}

	/**
	 * Отправляем сообщение бизнес отделу
	 *
	 * @throws \GetCompass\Userbot\Exception\Request\UnexpectedResponseException
	 */
	protected static function _sendMessageToBusiness(string $text):void {

		try {
			Domain_System_Entity_Alert::sendBusinessStat($text);
		} catch (\GetCompass\Userbot\Exception\Request\UnexpectedResponseException $e) {

			// время для бекапов с 5:00 до 5:05, когда кроны не работают и сообщение отправится после
			if (date("G") == 5 && intval(date("i")) < 5) {
				return;
			}

			throw $e;
		}
	}
}