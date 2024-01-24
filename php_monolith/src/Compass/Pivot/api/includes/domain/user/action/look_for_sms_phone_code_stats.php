<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * класс собирает и отправляет статистику по успешности доставки смс на коды номеров телефонов за период (час, день)
 */
class Domain_User_Action_LookForSmsPhoneCodeStats {

	// лимит кол-ва записей получаемых за раз из истории
	protected const _LIMIT = 2000;

	/**
	 * запускаем
	 *
	 * @param int $date_end
	 * @param int $interval
	 *
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function run(int $date_end, int $interval):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		self::_throwIfHugeInterval($interval);

		//
		$date_start = $date_end - $interval;

		// получаем историю отправленных смс по авторизациям
		$auth_phone_list = Gateway_Db_PivotAuth_AuthPhoneList::getListByPeriod($date_start, $date_end, self::_LIMIT);

		// получаем историю отправленных смс по смене номера телефона
		$phone_change_via_sms_list = Gateway_Db_PivotPhone_PhoneChangeViaSmsStory::getListByPeriod($date_start, $date_end, self::_LIMIT);

		// получаем историю отправленных смс по 2fa
		$two_fa_phone_list = Gateway_Db_PivotAuth_TwoFaPhoneList::getListByPeriod($date_start, $date_end, self::_LIMIT);

		// считаем по кодам номеров телефонов, куда отправляли смс
		$phone_code_summary_list = self::_calc($auth_phone_list, $phone_change_via_sms_list, $two_fa_phone_list);

		// если пустой массив, то ничего не делаем
		if (count($phone_code_summary_list) < 1) {
			return;
		}

		// формируем текст сообщения
		$message = self::_prepareMessageText($phone_code_summary_list, $interval);

		// отправляем текст сообщения
		Domain_User_Entity_Alert::send(COMPASS_NOTICE_BOT_TOKEN, COMPASS_NOTICE_BOT_PROJECT, SMS_ALERT_COMPASS_NOTICE_GROUP_ID, $message);
	}

	/**
	 * выбрасываем исключение, если передали слишком большой интервал
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _throwIfHugeInterval(int $interval):void {

		// если интервал больше 1 дня, то ругаемся
		if ($interval > DAY1) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("huge interval");
		}
	}

	/**
	 * считаем статисику для каждого кода номера телефона (например для +79 ..., +77 ... и тд)
	 *
	 * @param array $auth_phone_list
	 * @param array $phone_change_via_sms_list
	 * @param array $two_fa_phone_list
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 */
	protected static function _calc(array $auth_phone_list, array $phone_change_via_sms_list, array $two_fa_phone_list):array {

		//
		$output = [];

		// для попыток авторизации
		foreach ($auth_phone_list as $auth_phone) {

			$phone_number_obj   = new \BaseFrame\System\PhoneNumber($auth_phone->phone_number);
			$phone_country_data = \BaseFrame\Conf\Country::get($phone_number_obj->countryCode());
			$output             = self::_calcSingleItem($output, $phone_number_obj->countryPrefix(), $phone_country_data, $auth_phone->resend_count,
				$auth_phone->is_success === 1);
		}

		// для попыток смены номера телефона
		foreach ($phone_change_via_sms_list as $phone_change) {

			$phone_number_obj   = new \BaseFrame\System\PhoneNumber($phone_change->phone_number);
			$phone_country_data = \BaseFrame\Conf\Country::get($phone_number_obj->countryCode());
			$output             = self::_calcSingleItem(
				$output,
				$phone_number_obj->countryPrefix(),
				$phone_country_data,
				$phone_change->resend_count,
				$phone_change->status === Domain_User_Entity_ChangePhone_SmsStory::STATUS_SUCCESS
			);
		}

		// для попыток 2fa
		foreach ($two_fa_phone_list as $two_fa_phone) {

			$phone_number_obj   = new \BaseFrame\System\PhoneNumber($two_fa_phone->phone_number);
			$phone_country_data = \BaseFrame\Conf\Country::get($phone_number_obj->countryCode());
			$output             = self::_calcSingleItem($output, $phone_number_obj->countryPrefix(), $phone_country_data, $two_fa_phone->resend_count, $two_fa_phone->is_success);
		}

		return $output;
	}

	/**
	 * пересчитываем ответ по попытке
	 *
	 * @param array                                $output
	 * @param string                               $phone_country_code
	 * @param \BaseFrame\Struct\Country\ConfigItem $phone_country_data
	 * @param int                                  $resend_count
	 * @param bool                                 $is_success
	 *
	 * @return array
	 */
	protected static function _calcSingleItem(array $output, string $phone_country_code, \BaseFrame\Struct\Country\ConfigItem $phone_country_data, int $resend_count, bool $is_success):array {

		// если ранее не было в ответе статистики для этого кода телефона
		if (!isset($output[$phone_country_code])) {

			$output[$phone_country_code] = [
				"flag_emoji_short_name" => $phone_country_data->flag_emoji_short_name,
				"country_name"          => $phone_country_data->country_name_dictionary["ru"] ?? $phone_country_data->name,
				"total"                 => 0,
				"success"               => 0,
			];
		}

		// считаем общее кол-во попыток
		++$output[$phone_country_code]["total"];

		// сверху приплюсовываем кол-во переотправок
		$output[$phone_country_code]["total"] += $resend_count;

		// если успешная попытка, то считаем кол-во успешных попыток
		if ($is_success) {
			++$output[$phone_country_code]["success"];
		}

		return $output;
	}

	/**
	 * подготавливаем сообщение для отправки
	 *
	 * @return string
	 */
	protected static function _prepareMessageText(array $phone_code_summary_list, int $interval):string {

		$message = "Статистика ввода смс по кодам номеров телефонов";
		$message .= match ($interval) {
			HOUR1   => " за час:\n\n",
			DAY1    => " за день:\n\n",
			default => " за $interval секунд:\n\n",
		};

		foreach ($phone_code_summary_list as $phone_code => $phone_code_summary) {

			//
			$success_percent = floor(100 * ($phone_code_summary["success"] / $phone_code_summary["total"]));

			// добавляем к сообщению
			$message .= format("{flag_emoji_short_name} *{phone_code}* отправили *{total}*, успешно введены *{success}* – процент успеха *{success_percent}%*\n", [
				"flag_emoji_short_name" => $phone_code_summary["flag_emoji_short_name"],
				"phone_code"            => $phone_code,
				"total"                 => $phone_code_summary["total"],
				"success"               => $phone_code_summary["success"],
				"success_percent"       => $success_percent,
			]);
		}

		return $message;
	}
}