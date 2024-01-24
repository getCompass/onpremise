<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс локации поиска «Диалог»
 */
class Domain_Search_Entity_Conversation_Location extends Domain_Search_Entity_Location {

	public const LOCATION_TYPE     = Domain_Search_Const::TYPE_CONVERSATION;
	public const API_LOCATION_TYPE = "conversation";

	/**
	 * Проверяем наличие доступа к указанной локации.
	 * @throws Domain_Search_Exception_LocationDenied
	 */
	public static function checkAccess(int $user_id, string $key, bool $is_restricted_access):void {

		// проверяем, имеет ли пользователь доступ к диалогу
		$meta_list = Domain_Search_Repository_ProxyCache_ConversationMeta::load([$key]);
		$meta      = reset($meta_list);

		if ($meta === false) {
			throw new Domain_Search_Exception_LocationDenied("passed incorrect meta");
		}

		// если пространство неоплачено и чат не имеет тип "чат поддержки", выкидываем исключение
		if ($is_restricted_access && !Type_Conversation_Meta::isGroupSupportConversationType($meta["type"])) {
			throw new Domain_Search_Exception_LocationDenied("tariff unpaid", Domain_Search_Exception_LocationDenied::REASON_MEMBER_PLAN_RESTRICTED);
		}

		// если это диалог "Личный Heroes", то разрешаем всем
		// для всех остальных диалогов история одна и та же
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta["users"])) {
			throw new Domain_Search_Exception_LocationDenied("you are not conversation member");
		}
	}

	/**
	 * Возвращает список локаций, доступных пользователю.
	 * На вход получает результат поиска в виде массива ключей локаций.
	 *
	 * @param int                                    $user_id
	 * @param Struct_Domain_Search_RawLocation[]     $raw_location_list
	 * @param Struct_Domain_Search_Dto_SearchRequest $params
	 *
	 * @return Struct_Domain_Search_Location_Conversation[]
	 * @throws ParseFatalException
	 */
	public static function loadSuitable(int $user_id, array $raw_location_list, Struct_Domain_Search_Dto_SearchRequest $params):array {

		// фильтруем локации и конвертируем их в набор map диалогов
		$raw_location_list     = array_filter($raw_location_list, static fn(Struct_Domain_Search_RawLocation $loc):bool => $loc->type === static::LOCATION_TYPE);
		$conversation_map_list = array_unique(array_column($raw_location_list, "key"));

		if (count($conversation_map_list) === 0) {
			return [];
		}

		// получаем список диалогов, доступных пользователю
		$left_menu_item_list = Domain_Search_Repository_ProxyCache_LeftMenuItem::load($user_id, $conversation_map_list);
		$dynamic_list        = Domain_Search_Repository_ProxyCache_ConversationDynamic::load($conversation_map_list);
		$meta_list           = Domain_Search_Repository_ProxyCache_ConversationMeta::load($conversation_map_list);

		foreach ($raw_location_list as $raw_location) {

			if (!isset($left_menu_item_list[$raw_location->key], $dynamic_list[$raw_location->key], $meta_list[$raw_location->key])) {
				continue;
			}

			$output[] = new Struct_Domain_Search_Location_Conversation(
				$left_menu_item_list[$raw_location->key],
				$meta_list[$raw_location->key],
				$dynamic_list[$raw_location->key],
				$raw_location->hit_count
			);
		}

		return $output ?? [];
	}

	/**
	 * Фильтрует список совпадений для локации.
	 */
	public static function filterHits(Struct_Domain_Search_Location_Conversation $location, int $user_id, array $hit_list):array {

		$suitable_list = [];

		foreach ($hit_list as $hit) {

			$is_suitable = match ($hit::class) {
				Struct_Domain_Search_Hit_ConversationMessage::class => static::_isSuitableConversationMessageHit($location, $user_id, $hit),
				Struct_Domain_Search_Hit_ThreadMessage::class => static::_isSuitableThreadMessageHit($location, $user_id, $hit),
				default => false
			};

			if ($is_suitable) {
				$suitable_list[] = $hit;
			}
		}

		return $suitable_list;
	}

	/**
	 * Проверяет, является ли сообщение из диалога подходящим для выборки.
	 */
	protected static function _isSuitableConversationMessageHit(Struct_Domain_Search_Location_Conversation $location, int $user_id, Struct_Domain_Search_Hit_ConversationMessage $hit):bool {

		return static::_isSuitableConversationMessage($location, $user_id, $hit->item);
	}

	/**
	 * Проверяет, является ли сообщение из треда подходящим для выборки.
	 */
	protected static function _isSuitableThreadMessageHit(Struct_Domain_Search_Location_Conversation $location, int $user_id, Struct_Domain_Search_Hit_ThreadMessage $hit):bool {

		// не отдаем комментария не к сообщениям
		if (!isset($hit->parent["message_map"])) {
			return false;
		}

		return static::_isSuitableConversationMessage($location, $user_id, $hit->parent);
	}

	/**
	 * Проверяет, является ли сообщение из диалога подходящим для выборки.
	 */
	protected static function _isSuitableConversationMessage(Struct_Domain_Search_Location_Conversation $location, int $user_id, array $message):bool {

		$dynamic = $location->conversation_dynamic;

		// возможно здесь нет смысла проверять это во второй раз
		// при генерации hit-объекта эта проверка выполняется на тот случай,
		// если поиск ведется глобально (пока такое решение, возможно будет пересморено)
		$conversation_cleared_till = Domain_Conversation_Entity_Dynamic::getClearUntil(
			$dynamic->user_clear_info,
			$dynamic->conversation_clear_info,
			$user_id
		);

		try {

			return \CompassApp\Pack\Message\Conversation::getConversationMap($message["message_map"]) === $dynamic->conversation_map
				&& Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) >= $conversation_cleared_till;
		} catch (\cs_UnpackHasFailed) {
			throw new ReturnFatalException("passed bad conversation message");
		}
	}

	/**
	 * @inheritDoc
	 */
	public static function loadNested(int $user_id, array $raw_hit_nested_location_list, Struct_Domain_Search_Dto_SearchRequest $params):array {

		return [];
	}

	/**
	 * Проверяет, что указанный ключ может использоваться как ключ локации.
	 * @throws Domain_Search_Exception_IncorrectLocation
	 */
	public static function fromApi(string $key):string {

		try {
			return \CompassApp\Pack\Conversation::doDecrypt($key);
		} catch (\cs_DecryptHasFailed|\cs_UnpackHasFailed) {
			throw new Domain_Search_Exception_IncorrectLocation("passed key is not conversation location");
		}
	}

	/**
	 * Конвертирует локацию в пригодный для клиента формат.
	 */
	public static function toApi(Struct_Domain_Search_Location_Conversation $location, int $user_id):array {

		return [
			"type"              => static::API_LOCATION_TYPE,
			"data"              => [
				"item"      => Type_Conversation_Utils::prepareLeftMenuForFormat($location->left_menu_item),
				"hit_count" => $location->hit_count,
				"hit_list"  => Domain_Search_Entity_HitHandler::toApi($user_id, $location->hit_list),
			],

			// сервисное поле, куда сложим все идентификаторы пользователей сущности
			// чтобы в api-контроллере вернуть action users
			"_user_action_data" => array_keys($location->conversation_meta["users"]),
		];
	}
}