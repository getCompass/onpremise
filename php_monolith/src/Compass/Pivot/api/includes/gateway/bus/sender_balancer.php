<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Class Gateway_Bus_SenderBalancer
 */
class Gateway_Bus_SenderBalancer {

	protected const _TOKEN_EXPIRE_TIME = 1 * 60;   // время за которое нужно успеть авторизоваться по полученному токену

	/**
	 * делаем токен для подключения к ws по user_id
	 *
	 * @param int    $user_id
	 * @param string $device_id
	 * @param string $platform
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 */
	public static function getConnection(int $user_id, string $device_id = "", string $platform = Type_Api_Platform::PLATFORM_OTHER):array {

		// генерируем token
		$token = self::_generateToken();

		// формируем массив для отправки
		$request = new \SenderBalancerGrpc\SenderBalancerSetTokenRequestStruct([
			"user_id"   => $user_id,
			"token"     => $token,
			"platform"  => $platform,
			"device_id" => $device_id,
			"expire"    => time() + self::_TOKEN_EXPIRE_TIME,
		]);

		// получаем из конфига где находится микросервис
		[$response, $status] = self::_doCallGrpc("SenderBalancerSetToken", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// формируем ссылку для установления wss соединения
		$config = getConfig("SHARDING_GO");
		$url    = $config["sender_balancer"]["url"] . $response->getNode();

		return [$token, $url];
	}

	/**
	 * Тестовое событие
	 *
	 * @param int $user_id
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function testEvent(int $user_id):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_Test_V1::makeEvent(),
		], [$user_id]);
	}

	/**
	 * Отправка события об изменении данных профиля
	 *
	 * @param int              $user_id
	 * @param Struct_User_Info $user_info
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function profileEdited(int $user_id, Struct_User_Info $user_info):void {

		$formatted_user = Apiv1_Pivot_Format::user($user_info);
		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_ProfileEdited_V1::makeEvent($formatted_user),
		], [$user_id]);
	}

	/**
	 * Событие о чистке аватара
	 *
	 * @param int $user_id
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function avatarCleared(int $user_id):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_AvatarCleared_V1::makeEvent(),
		], [$user_id]);
	}

	/**
	 * Событие изменения статуса компании
	 *
	 * @param int   $user_id
	 * @param array $company
	 * @param array $push_data
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function companyStatusChanged(int $user_id, array $company, array $push_data = []):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_CompanyStatusChanged_V1::makeEvent($company),
		], [$user_id], [], $push_data, count($push_data) > 0 ? 1 : 0);
	}

	/**
	 * Событие при отклонении компании собственником
	 *
	 * @param int              $user_id
	 * @param Struct_User_Info $user_info
	 * @param int              $company_id
	 * @param string           $company_name
	 * @param array            $push_data
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function companyStatusRejected(int $user_id, Struct_User_Info $user_info, int $company_id, string $company_name, array $push_data):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_CompanyStatusRejected_V1::makeEvent(
				$company_id,
				$company_name,
				$user_info->user_id,
				$user_info->full_name,
				isEmptyString($user_info->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($user_info->avatar_file_map),
				\BaseFrame\Domain\User\Avatar::getColorOutput($user_info->avatar_color_id),
			),
		], [$user_id], [], $push_data, 1);
	}

	/**
	 * Уведомления отключены
	 *
	 * @param int $user_id
	 * @param int $snoozed_until
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function snoozedTimerChanged(int $user_id, int $snoozed_until):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_SnoozedTimerChanged_V1::makeEvent($snoozed_until),
		], [$user_id]);
	}

	/**
	 * Уведомления отключены для конкретного типа
	 *
	 * @param int $user_id
	 * @param int $event_type
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function notificationsDisabledForEvent(int $user_id, int $event_type):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_NotificationsDisabledForEvent_V1::makeEvent($event_type),
		], [$user_id]);
	}

	/**
	 * Уведомления включены для конкретного типа
	 *
	 * @param int $user_id
	 * @param int $event_type
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function notificationsEnabledForEvent(int $user_id, int $event_type):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_NotificationsEnabledForEvent_V1::makeEvent($event_type),
		], [$user_id]);
	}

	/**
	 * Определенный тип уведомлений временно отключен
	 *
	 * @param int $user_id
	 * @param int $event_type
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function notificationsEventSnoozed(int $user_id, int $event_type):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_NotificationsEventSnoozed_V1::makeEvent($event_type),
		], [$user_id]);
	}

	/**
	 * Определенный тип уведомлений с таймером включен
	 *
	 * @param int $user_id
	 * @param int $event_type
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function notificationsEventUnsnoozed(int $user_id, int $event_type):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_NotificationsEventUnsnoozed_V1::makeEvent($event_type),
		], [$user_id]);
	}

	/**
	 * Создана компания
	 *
	 * @param int   $user_id
	 * @param array $company_info
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function companyCreated(int $user_id, array $company_info):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_CompanyCreated_V1::makeEvent($company_info["company"]),
		], [$user_id]);
	}

	/**
	 * Обновлен онбординг
	 *
	 * @param int                    $user_id
	 * @param Struct_User_Onboarding $onboarding
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function onboardingUpdated(int $user_id, array $onboarding):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_OnboardingUpdated_V1::makeEvent($onboarding),
		], [$user_id]);
	}

	/**
	 * Сменился список компаний
	 *
	 * @param int   $user_id
	 * @param array $company_order_list
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function companyListOrder(int $user_id, array $company_order_list):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_CompanyListOrder_V1::makeEvent($company_order_list),
		], [$user_id]);
	}

	/**
	 * Событие о том, что пользователь был уволен
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $reason
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function userFired(int $user_id, int $company_id, string $reason):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_UserFiredFromCompany_V1::makeEvent($company_id, $reason),
		], [$user_id]);
	}

	/**
	 * Компания удалена из списка дэшборда
	 *
	 * @param int $user_id
	 * @param int $company_id
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function companyRemovedFromList(int $user_id, int $company_id):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_CompanyRemovedFromList_V1::makeEvent($company_id),
		], [$user_id]);
	}

	/**
	 * Компания удалена
	 *
	 * @param array $user_id_list
	 * @param int   $company_id
	 * @param int   $deleted_at
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function companyDeleted(array $user_id_list, int $company_id, int $deleted_at):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_CompanyDeleted_V1::makeEvent($company_id, $deleted_at),
		], $user_id_list);
	}

	/**
	 * аккаунт пользователя удален
	 *
	 * @param int   $deleted_user_id
	 * @param array $user_id_list
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function profileDeleted(int $deleted_user_id, array $user_id_list):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_ProfileDeleted_V1::makeEvent($deleted_user_id),
		], $user_id_list);
	}

	/**
	 * Компания проснулась
	 *
	 * @param int   $company_id
	 * @param array $user_id_list
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function companyAwoke(int $company_id, array $user_id_list):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_CompanyAwoke_V1::makeEvent($company_id),
		], $user_id_list);
	}

	/**
	 * Компания переехала
	 *
	 * @param int   $company_id
	 * @param array $user_id_list
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function companyRelocated(int $company_id, array $user_id_list):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_CompanyRelocated_V1::makeEvent($company_id),
		], $user_id_list);
	}

	/**
	 * изменились данные премиума для пользователя
	 *
	 * @param int $user_id
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function premiumUpdated(int $user_id):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_PremiumUpdated_V1::makeEvent(),
		], [$user_id]);
	}

	/**
	 * Перешли по ссылке приглашению
	 *
	 * @param int $user_id
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function inviteLinkAccepted(int $user_id):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_InviteLinkAccepted_V1::makeEvent(),
		], [$user_id]);
	}

	/**
	 * Добавили почту
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function mailAdded(int $user_id, string $mail):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_MailAdded_V1::makeEvent($mail),
		], [$user_id]);
	}

	/**
	 * Добавили номер телефона
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function phoneAdded(int $user_id, string $phone_number):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_PhoneAdded_V1::makeEvent($phone_number),
		], [$user_id]);
	}

	/**
	 * Поменяли почту
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function mailChanged(int $user_id, string $mail):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_MailChanged_V1::makeEvent($mail),
		], [$user_id]);
	}

	/**
	 * Поменяли номер телефона
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function phoneChanged(int $user_id, string $phone_number):void {

		self::_sendEvent([
			Gateway_Bus_SenderBalancer_Event_PhoneChanged_V1::makeEvent($phone_number),
		], [$user_id]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * отправляем событие
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 * @param array                         $user_id_list
	 * @param array                         $ws_users
	 * @param array                         $push_data
	 * @param int                           $is_need_push
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	protected static function _sendEvent(array $event_version_list, array $user_id_list, array $ws_users = [], array $push_data = [], int $is_need_push = 0):void {

		// проверяем что прислали корректные параметры
		self::_assertSendEventParameters($event_version_list);

		// если прислали пустой массив получателей
		if (count($user_id_list) < 1) {

			// ничего не делаем
			return;
		}

		// получаем название событие
		$event_name = $event_version_list[0]->event;

		// конвертируем event_version_list из структуру в массив
		$converted_event_version_list = [];
		foreach ($event_version_list as $event) {

			$converted_event_version_list[] = [
				"version" => (int) $event->version,
				"data"    => (object) $event->ws_data,
			];
		}

		self::_sendEventRequest($user_id_list, $event_name, $converted_event_version_list, $ws_users, $push_data, $is_need_push);
	}

	/**
	 * проверяем параметры
	 *
	 * @param Struct_SenderBalancer_Event[] $event_version_list
	 *
	 * @throws ParseFatalException
	 */
	protected static function _assertSendEventParameters(array $event_version_list):void {

		// если прислали пустой массив версий метода
		if (count($event_version_list) < 1) {
			throw new ParseFatalException("incorrect array event version list");
		}

		// проверяем, что все версии события описывают один и тот же метод
		$ws_method_name = $event_version_list[0]->event;
		foreach ($event_version_list as $event) {

			if ($event->event !== $ws_method_name) {
				throw new ParseFatalException("different ws event names");
			}
		}
	}

	/**
	 * Отправка события в go_sender_balancer
	 *
	 * @param array  $user_id_list
	 * @param string $event
	 * @param array  $event_version_list
	 * @param array  $ws_users
	 * @param array  $push_data
	 * @param int    $is_need_push
	 *
	 * @throws \parseException
	 */
	protected static function _sendEventRequest(array $user_id_list, string $event, array $event_version_list, array $ws_users = [], array $push_data = [], int $is_need_push = 0):void {

		// формируем массив для отправки
		$user_list = [];
		foreach ($user_id_list as $user_id) {
			$user_list[] = self::makeTalkingUserItem($user_id);
		}

		// формируем параметры задачи для rabbitMq
		$params = [
			"method"             => (string) "talking.sendEvent",
			"user_list"          => (array) $user_list,
			"event"              => (string) $event,
			"event_version_list" => (array) $event_version_list,
			"push_data"          => (object) $push_data,
			"is_need_push"       => (int) $is_need_push,
			"uuid"               => (string) generateUUID(),
			"ws_users"           => (array) $ws_users,
		];

		// подготавливаем event_data (шифруем map -> key)
		$params = Type_Pack_Main::replaceMapWithKeys($params);

		// проводим тест безопасности, что в ответе нет map
		Type_Pack_Main::doSecurityTest($params);

		// отправляем задачу в rabbitMq
		ShardingGateway::rabbit()->sendMessage(GO_SENDER_BALANCER_QUEUE, $params);
	}

	/**
	 * Формируем объект talking_user_item
	 *
	 * @param int $user_id
	 *
	 * @return int[]
	 */
	public static function makeTalkingUserItem(int $user_id):array {

		return [
			"user_id" => $user_id,
		];
	}

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @param string                            $method_name
	 * @param \Google\Protobuf\Internal\Message $request
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("sender_balancer", \SenderBalancerGrpc\go_sender_balancerClient::class);

		return $connection->callGrpc($method_name, $request);
	}

	/**
	 * сгенерировать токен
	 *
	 */
	protected static function _generateToken():string {

		return sha1(uniqid() . time());
	}
}