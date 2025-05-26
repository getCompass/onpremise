<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Класс для работы с rating
 */
class Gateway_Bus_Company_Rating extends Gateway_Bus_Company_Main {

	public const USER_RATING_NOT_BLOCKED = 0;
	public const USER_RATING_BLOCKED     = 1;
	public const USER_RATING_NOT_EXIST   = 2;

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * инкремент статистики для определенного ивента
	 *
	 * @param string $event
	 * @param int    $user_id
	 * @param int    $inc_value
	 *
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function inc(string $event, int $user_id, int $inc_value = 1):void {

		$request = new \CompanyGrpc\RatingIncRequestStruct([
			"event"      => $event,
			"user_id"    => $user_id,
			"inc"        => $inc_value,
			"company_id" => COMPANY_ID,
		]);

		[, $status] = self::_doCallGrpc("RatingInc", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * инкрементим определенный день в году для пользователя
	 *
	 * @param string $event
	 * @param int    $user_id
	 * @param int    $year
	 * @param int    $day
	 * @param int    $inc_value
	 *
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function incDayForUser(string $event, int $user_id, int $year, int $day, int $inc_value = 1):void {

		$request = new \CompanyGrpc\RatingIncDayRatingEventCountForUserRequestStruct([
			"user_id"    => $user_id,
			"year"       => $year,
			"day"        => $day,
			"event"      => $event,
			"inc"        => $inc_value,
			"company_id" => COMPANY_ID,

		]);

		[, $status] = self::_doCallGrpc("RatingIncDayRatingEventCountForUser", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * декремент статистики после удаления сущности
	 *
	 * @param string $event
	 * @param int    $user_id
	 * @param int    $entity_created_at
	 * @param int    $value optional
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function decAfterDelete(string $event, int $user_id, int $entity_created_at, int $value = 1):void {

		// если ивент не позволяет его декрементить
		if (!in_array($event, Domain_Rating_Entity_Rating::getAllowEventsForDecrement())) {
			throw new ParseFatalException("event type not allowed for decrement");
		}

		// проверяем пришедшее значение value
		if ($value < 1) {
			return;
		}

		// если с момента создания прошло много времени, чтобы менять рейтинг
		if (time() - $entity_created_at > Domain_Rating_Entity_Rating::getAllowDecrementTimeLimit()) {
			return;
		}

		// формируем массив для запроса
		$ar_post = [
			"method"     => "rating.dec",
			"user_id"    => $user_id,
			"event"      => $event,
			"created_at" => $entity_created_at,
			"dec"        => $value,
			"company_id" => COMPANY_ID,
		];

		// отправляем rзадачу в очередь
		Gateway_Bus_Rabbit::sendMessage(self::_QUEUE_NAME, $ar_post);
	}

	/**
	 * Получить общий рейтинг
	 *
	 * @throws ParamException
	 * @throws BusFatalException
	 */
	public static function get(string $event, int $from_date_at, int $to_date_at, int $top_list_offset, int $top_list_count):Struct_Bus_Rating_General {

		$request = new \CompanyGrpc\RatingGetRequestStruct([
			"from_date_at"    => $from_date_at,
			"to_date_at"      => $to_date_at,
			"event"           => $event,
			"top_list_offset" => $top_list_offset,
			"top_list_count"  => $top_list_count,
			"company_id"      => COMPANY_ID,
		]);

		[$response, $status] = self::_doCallGrpc("RatingGet", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			self::_throwIfGrpcReturnNotOk($status);
		}

		return self::_doFormatGet($response);
	}

	/**
	 * получить общий рейтинг
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public static function getByMonth(string $event, int $month_start_at, int $month_end_at, int $top_list_offset, int $top_list_count):Struct_Bus_Rating_General {

		$request = new \CompanyGrpc\RatingGetRequestStruct([
			"from_date_at"    => $month_start_at,
			"to_date_at"      => $month_end_at,
			"event"           => $event,
			"top_list_offset" => $top_list_offset,
			"top_list_count"  => $top_list_count,
			"company_id"      => COMPANY_ID,
		]);

		[$response, $status] = self::_doCallGrpc("RatingGet", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			self::_throwIfGrpcReturnNotOk($status);
		}

		return self::_doFormatGetByMonth($response);
	}

	/**
	 * получить рейтинг пользователя
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public static function getByUserId(int $user_id, int $year, array $week_arr, array $from_date_at_arr, array $to_date_at_arr, array $is_from_cache_arr):array {

		$request = new \CompanyGrpc\RatingGetByUserIdRequestStruct([
			"user_id"            => $user_id,
			"year"               => $year,
			"from_date_at_list"  => $from_date_at_arr,
			"to_date_at_list"    => $to_date_at_arr,
			"is_from_cache_list" => $is_from_cache_arr,
			"company_id"         => COMPANY_ID,
		]);

		[$response, $status] = self::_doCallGrpc("RatingGetByUserId", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			self::_throwIfGrpcReturnNotOk($status);
		}
		return self::_doFormatGetByUserId($response, $year, $week_arr);
	}

	/**
	 * получить рейтинг за период по типу
	 *
	 * @return Struct_Bus_Rating_EventCount[]
	 *
	 * @throws \busException
	 * @throws \parseException|paramException
	 */
	public static function getEventCountByInterval(int $year, int $from_date_at, int $to_date_at, string $event):array {

		$request = new \CompanyGrpc\RatingGetEventCountByIntervalRequestStruct([
			"year"         => $year,
			"from_date_at" => $from_date_at,
			"to_date_at"   => $to_date_at,
			"event"        => $event,
			"company_id"   => COMPANY_ID,
		]);

		[$response, $status] = self::_doCallGrpc("RatingGetEventCountByInterval", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			self::_throwIfGrpcReturnNotOk($status);
		}

		return self::_doFormatGetEventCountByInterval($response);
	}

	/**
	 * получить общий рейтинг
	 *
	 * @return Struct_Bus_Rating_EventCount[]
	 *
	 * @throws \busException
	 * @throws \parseException
	 * @throws paramException
	 */
	public static function getGeneralEventCountByInterval(int $year, int $from_date_at, int $to_date_at):array {

		$request = new \CompanyGrpc\RatingGetGeneralEventCountByIntervalRequestStruct([
			"year"         => $year,
			"from_date_at" => $from_date_at,
			"to_date_at"   => $to_date_at,
			"company_id"   => COMPANY_ID,
		]);

		[$response, $status] = self::_doCallGrpc("RatingGetGeneralEventCountByInterval", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			self::_throwIfGrpcReturnNotOk($status);
		}

		return self::_doFormatGetGeneralEventCountByInterval($response);
	}

	/**
	 * функция для сохранения текущего состояния кеша в базу
	 */
	public static function forceSaveCache():void {

		$request       = new \CompanyGrpc\RatingForceSaveCacheRequestStruct([
			"company_id" => COMPANY_ID,
		]);
		$response_data = self::_doCallGrpc("RatingForceSaveCache", $request);
		$status        = $response_data[1];

		if ($status->code !== \Grpc\STATUS_OK) {
			self::_throwIfGrpcReturnNotOk($status);
		}
	}

	/**
	 * функция для получения статистики по всем пользователям компании за день
	 *
	 * @return Struct_Bus_Rating_UserDayStats[]
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 */
	public static function getListByDay(int $year, int $day_num):array {

		$request = new \CompanyGrpc\RatingGetListByDayRequestStruct([
			"year"       => $year,
			"day"        => $day_num,
			"company_id" => COMPANY_ID,
		]);
		[$response, $status] = self::_doCallGrpc("RatingGetListByDay", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			self::_throwIfGrpcReturnNotOk($status);
		}

		return self::_doFormatGetListByDay($response);
	}

	/**
	 * функция для форматирования ответа от rating.getListByDay
	 *
	 * @param \CompanyGrpc\RatingGetListByDayResponseStruct $response
	 *
	 * @return Struct_Bus_Rating_UserDayStats[]
	 */
	protected static function _doFormatGetListByDay(\CompanyGrpc\RatingGetListByDayResponseStruct $response):array {

		$output = [];

		foreach ($response->getUserDayStatsList() as $v) {

			$data = [];
			foreach ($v->getData() as $event_name => $count) {
				$data[$event_name] = $count;
			}

			$output[] = new Struct_Bus_Rating_UserDayStats(
				$v->getUserId(),
				$data
			);
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED FUNCTIONS
	// -------------------------------------------------------

	/**
	 * функция для форматирования ответа от rating.get
	 *
	 * @param \CompanyGrpc\RatingGetResponseStruct $response
	 *
	 * @return Struct_Bus_Rating_General
	 */
	protected static function _doFormatGet(\CompanyGrpc\RatingGetResponseStruct $response):Struct_Bus_Rating_General {

		$general_rating             = new Struct_Bus_Rating_General();
		$general_rating->count      = $response->getCount();
		$general_rating->updated_at = $response->getUpdatedAt();
		$general_rating->top_list   = [];
		$general_rating->has_next   = $response->getHasNext();

		if (count($response->getTopList()) > 0) {
			$general_rating->top_list = self::_makeTopListForGet($response->getTopList(), $general_rating);
		}

		return $general_rating;
	}

	/**
	 * функция для форматирования ответа от rating.getByMonth
	 *
	 * @param \CompanyGrpc\RatingGetResponseStruct $response
	 *
	 * @return Struct_Bus_Rating_General
	 */
	protected static function _doFormatGetByMonth(\CompanyGrpc\RatingGetResponseStruct $response):Struct_Bus_Rating_General {

		$general_rating = new Struct_Bus_Rating_General();

		$general_rating->count      = $response->getCount();
		$general_rating->updated_at = $response->getUpdatedAt();
		$general_rating->top_list   = [];
		$general_rating->has_next   = $response->getHasNext();

		if (count($response->getTopList()) > 0) {
			$general_rating->top_list = self::_makeTopListForGet($response->getTopList(), $general_rating);
		}

		return $general_rating;
	}

	/**
	 * Формирование top_list для рейтинга
	 */
	protected static function _makeTopListForGet(\Google\Protobuf\Internal\RepeatedField $response_top_list, Struct_Bus_Rating_General $general_rating):array {

		foreach ($response_top_list as $v) {

			$top_item              = new Struct_Bus_Rating_General_TopItem();
			$top_item->user_id     = $v->getUserId();
			$top_item->position    = $v->getPosition();
			$top_item->count       = $v->getCount();
			$top_item->is_disabled = $v->getIsDisabled();

			$general_rating->top_list[] = $top_item;
		}

		return $general_rating->top_list;
	}

	/**
	 * функция для форматирования ответа от rating.getByUserId
	 *
	 * @param \CompanyGrpc\RatingGetByUserIdListResponseStruct $response
	 * @param int                                              $year
	 * @param array                                            $week_arr
	 *
	 * @return array
	 */
	protected static function _doFormatGetByUserId(\CompanyGrpc\RatingGetByUserIdListResponseStruct $response, int $year, array $week_arr):array {

		$user_rating_list_response = $response->getUserRatingList();
		$user_rating_list          = [];
		foreach ($user_rating_list_response as $k => $user_rating_item) {

			$user_rating = new Struct_Bus_Rating_User();

			$user_rating->year             = $year;
			$user_rating->week             = $week_arr[$k];
			$user_rating->user_id          = $user_rating_item->getUserId();
			$user_rating->general_count    = $user_rating_item->getGeneralCount();
			$user_rating->general_position = $user_rating_item->getGeneralPosition();
			$user_rating->updated_at       = $user_rating_item->getUpdatedAt();

			// устанавливаем count для каждого ивента
			foreach ($user_rating_item->getEventCountList() as $event_name => $count) {
				$user_rating->event_count_list[$event_name] = $count;
			}

			array_push($user_rating_list, $user_rating);
		}

		return $user_rating_list;
	}

	/**
	 * функция для форматирования ответа от rating.getEventCountByInterval
	 *
	 * @param \CompanyGrpc\RatingGetEventCountByIntervalResponseStruct $response
	 *
	 * @return Struct_Bus_Rating_EventCount[]
	 */
	protected static function _doFormatGetEventCountByInterval(\CompanyGrpc\RatingGetEventCountByIntervalResponseStruct $response):array {

		$event_count_list = [];
		foreach ($response->getEventCountList() as $v) {

			// получаем номер недели, за который числится рейтинг
			$year = $v->getYear();
			$week = Type_Rating_Helper::getWeekNumberByDaysCount($year, $v->getDay());

			if (!isset($event_count_list[$week])) {

				$event_count_list[$week]        = new Struct_Bus_Rating_EventCount();
				$event_count_list[$week]->year  = $year;
				$event_count_list[$week]->week  = $week;
				$event_count_list[$week]->count = 0;
			}
			$event_count_list[$week]->count += $v->getCount();
		}

		return $event_count_list;
	}

	/**
	 * функция для форматирования ответа от rating.getGeneralEventCountByInterval
	 *
	 * @param \CompanyGrpc\RatingGetGeneralEventCountByIntervalResponseStruct $response
	 *
	 * @return Struct_Bus_Rating_EventCount[]
	 */
	protected static function _doFormatGetGeneralEventCountByInterval(\CompanyGrpc\RatingGetGeneralEventCountByIntervalResponseStruct $response):array {

		$event_count_list = [];
		foreach ($response->getEventCountList() as $v) {

			// получаем номер недели, за который числится рейтинг
			$year = $v->getYear();
			$week = Type_Rating_Helper::getWeekNumberByDaysCount($year, $v->getDay());

			if (!isset($event_count_list[$week])) {

				$event_count_list[$week]        = new Struct_Bus_Rating_EventCount();
				$event_count_list[$week]->year  = $v->getYear();
				$event_count_list[$week]->week  = $week;
				$event_count_list[$week]->count = 0;
			}

			$event_count_list[$week]->count += $v->getCount();
		}
		return $event_count_list;
	}

	/**
	 * выдаем exception, если grpc не вернул ok
	 *
	 * @param object $status
	 *
	 * @throws BusFatalException
	 * @throws ParamException
	 */
	protected static function _throwIfGrpcReturnNotOk(object $status):void {

		switch ($status->code) {

			case 400:

				$error_text = $status->details;
				throw new ParamException($error_text);

			default:
				throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Инкремент статистики за указанную неделю
	 *
	 * @param int    $year
	 * @param int    $week
	 * @param string $event
	 * @param int    $user_id
	 * @param int    $value
	 *
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function incPreviousWeek(int $year, int $week, string $event, int $user_id, int $value = 1):void {

		$date = new \DateTime();
		$date->setTime(12, 0);

		// устанавливаем неделю и год
		$date->setISODate($year, $week);

		// получаем день для инкремента
		$day = intval($date->format("z")) + 1;

		self::incDayForUser($event, $user_id, $year, $day, $value);
	}

	/**
	 * Чистим всю статистику
	 */
	public static function doClearAllStatistic(int $year):void {

		// формируем массив для запроса
		$ar_post = [
			"method" => "backdoor.doClearAllStatistic",
			"year"   => $year,
		];

		// отправляем задачу в очередь
		Gateway_Bus_Rabbit::sendMessageToExchange(self::_EXCHANGE_NAME, $ar_post);
	}

	/**
	 * Чистим всю статистику
	 */
	public static function doClearCache():void {

		// формируем массив для запроса
		$ar_post = [
			"method" => "system.doClearCache",
		];

		// отправляем задачу в очередь
		Gateway_Bus_Rabbit::sendMessageToExchange(self::_EXCHANGE_NAME, $ar_post);
	}

	/**
	 * функция для пометки статуса пользователя в рейтинге как активный
	 *
	 * @param int $user_id
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function enableUserInRating(int $user_id):void {

		$request = new \CompanyGrpc\RatingSetUserBlockInSystemStatusRequestStruct([
			"user_id"    => $user_id,
			"status"     => self::USER_RATING_NOT_BLOCKED,
			"company_id" => COMPANY_ID,
		]);

		[, $status] = self::_doCallGrpc("RatingSetUserBlockInSystemStatus", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * функция для пометки статуса пользователя в рейтинге как отключенный
	 *
	 * @param int $user_id
	 *
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function disableUserInRating(int $user_id):void {

		$request = new \CompanyGrpc\RatingSetUserBlockInSystemStatusRequestStruct([
			"user_id"    => $user_id,
			"status"     => self::USER_RATING_BLOCKED,
			"company_id" => COMPANY_ID,
		]);

		[, $status] = self::_doCallGrpc("RatingSetUserBlockInSystemStatus", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * заблокирован пользователь или нет
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function getUserStatus(int $user_id):bool {

		$request = new \CompanyGrpc\RatingGetUserStatusRequestStruct([
			"user_id"    => $user_id,
			"company_id" => COMPANY_ID,
		]);

		[$response, $status] = self::_doCallGrpc("RatingGetUserStatus", $request);

		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return $response->getStatus();
	}
}
