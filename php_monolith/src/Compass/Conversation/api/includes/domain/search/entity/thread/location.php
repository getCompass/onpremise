<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use Compass\Thread\Type_Thread_Utils;

/**
 * Класс локации поиска «Тред»
 */
class Domain_Search_Entity_Thread_Location extends Domain_Search_Entity_Location {

	public const LOCATION_TYPE     = Domain_Search_Const::TYPE_THREAD;
	public const API_LOCATION_TYPE = "thread";

	/**
	 * Проверяем наличие доступа к указанной локации.
	 *
	 * @throws Domain_Search_Exception_LocationDenied
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 */
	public static function checkAccess(int $user_id, string $key, bool $is_restricted_access):void {

		$thread_meta_list = Domain_Search_Repository_ProxyCache_ThreadMeta::load([$key]);
		$thread_meta      = reset($thread_meta_list);

		if ($thread_meta === false) {
			throw new Domain_Search_Exception_LocationDenied("passed incorrect location");
		}

		// получаем тип родительской сущности
		// в текущий момент обрабатываем только треды к сообщениям в диалогах
		$parent_entity_type = \Compass\Thread\Type_Thread_ParentRel::getType($thread_meta["parent_rel"]);

		if ($parent_entity_type !== \Compass\Thread\PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE) {
			throw new Domain_Search_Exception_LocationDenied("passed unsupported location");
		}

		// получаем данные диалога
		$conversation_map = \Compass\Thread\Type_Thread_SourceParentRel::getMap($thread_meta["source_parent_rel"]);

		// проверяем, имеет ли пользователь доступ к диалогу
		$conversation_meta_list = Domain_Search_Repository_ProxyCache_ConversationMeta::load([$conversation_map]);
		$conversation_meta      = reset($conversation_meta_list);

		if ($conversation_meta === false) {
			throw new ReturnFatalException("thread meta not found");
		}

		// если пространство неоплачено и чат не имеет тип "чат поддержки", выкидываем исключение
		self::_throwIfRestrictedAccess($is_restricted_access, $conversation_meta);

		// проверяем что пользователь – участник
		self::_throwIfUserNotMember($user_id, $conversation_meta);

		// проверяем, что сообщение диалога, к которому закреплен тред, не удалено
		self::_throwIfConversationMessageOfThreadWasDeleted($thread_meta);

		// проверяем, что сообщение диалога, к которому закреплен тред, не очищено
		$conversation_dynamic_list = Domain_Search_Repository_ProxyCache_ConversationDynamic::load([$conversation_map]);
		$conversation_dynamic      = reset($conversation_dynamic_list);
		self::_throwIfConversationMessageOfTHreadWasCleared($user_id, $thread_meta, $conversation_dynamic);
	}

	/**
	 * выбрасываем исключение, если пространство не оплачено
	 *
	 * @throws Domain_Search_Exception_LocationDenied
	 */
	protected static function _throwIfRestrictedAccess(bool $is_restricted_access, array $conversation_meta):void {

		if ($is_restricted_access && !Type_Conversation_Meta::isGroupSupportConversationType($conversation_meta["type"])) {
			throw new Domain_Search_Exception_LocationDenied("tariff unpaid", Domain_Search_Exception_LocationDenied::REASON_MEMBER_PLAN_RESTRICTED);
		}
	}

	/**
	 * выбрасываем исключение, если пользователь не участник диалога
	 *
	 * @throws Domain_Search_Exception_LocationDenied
	 */
	protected static function _throwIfUserNotMember(int $user_id, array $conversation_meta):void {

		if (!Type_Conversation_Meta_Users::isMember($user_id, $conversation_meta["users"])) {
			throw new Domain_Search_Exception_LocationDenied("you are not conversation member");
		}
	}

	/**
	 * выбрасываем исключение, если сообщение диалога, к которому написан тред, было удалено
	 */
	protected static function _throwIfConversationMessageOfThreadWasDeleted(array $thread_meta):void {

		if (\Compass\Thread\Type_Thread_ParentRel::getIsDeleted($thread_meta["parent_rel"])) {
			throw new Domain_Search_Exception_LocationDenied("thread access restricted");
		}
	}

	/**
	 * выбрасываем исключение, если сообщение диалога, к которому написан тред, было очищено
	 */
	protected static function _throwIfConversationMessageOfTHreadWasCleared(int $user_id, array $thread_meta, Struct_Db_CompanyConversation_ConversationDynamic $conversation_dynamic):void {

		$parent_entity_created_at = \Compass\Thread\Type_Thread_ParentRel::getCreatedAt($thread_meta["parent_rel"]);
		if ($parent_entity_created_at < Domain_Conversation_Entity_Dynamic::getClearUntil(
				$conversation_dynamic->user_clear_info, $conversation_dynamic->conversation_clear_info, $user_id
			)) {
			throw new Domain_Search_Exception_LocationDenied("thread access restricted");
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
	 * @return Struct_Domain_Search_Location_Thread[]
	 */
	public static function loadSuitable(int $user_id, array $raw_location_list, Struct_Domain_Search_Dto_SearchRequest $params):array {

		// фильтруем локации и конвертируем их в набор map диалогов
		$raw_location_list = array_filter($raw_location_list, static fn(Struct_Domain_Search_RawLocation $loc):bool => $loc->type === static::LOCATION_TYPE);
		$thread_map_list   = array_unique(array_column($raw_location_list, "key"));

		if (count($thread_map_list) === 0) {
			return [];
		}

		$thread_meta_list = Domain_Search_Repository_ProxyCache_ThreadMeta::load($thread_map_list);

		foreach ($raw_location_list as $raw_location) {

			if (!isset($thread_meta_list[$raw_location->key])) {
				continue;
			}

			$output[] = new Struct_Domain_Search_Location_Thread(
				$thread_meta_list[$raw_location->key],
				$raw_location->hit_count,
				Domain_Search_Entity_HitHandler::load($user_id, $raw_location->hit_list, $params)
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

		$raw_hit_nested_location_list = array_filter(
			$raw_hit_nested_location_list,
			static fn(Struct_Domain_Search_RawHitNestedLocation $el) => $el->location->entity_rel->entity_type === static::LOCATION_TYPE
		);

		$raw_location_list = array_map(
			static fn(Struct_Domain_Search_RawHitNestedLocation $raw_hit_nested_location) => new Struct_Domain_Search_RawLocation(
				$raw_hit_nested_location->location->entity_rel->entity_map,
				$raw_hit_nested_location->location->entity_rel->entity_type,
				$raw_hit_nested_location->hit_count,
				$raw_hit_nested_location->last_hit_at,
				$raw_hit_nested_location->nested_hit_list
			),
			$raw_hit_nested_location_list
		);

		return static::loadSuitable($user_id, $raw_location_list, $params);
	}

	/**
	 * Проверяет, что указанный ключ может использоваться как ключ локации.
	 * @throws Domain_Search_Exception_IncorrectLocation
	 */
	public static function fromApi(string $key):string {

		try {
			return \CompassApp\Pack\Thread::doDecrypt($key);
		} catch (\cs_DecryptHasFailed|\cs_UnpackHasFailed) {
			throw new Domain_Search_Exception_IncorrectLocation("passed key doesn't belong to any thread location");
		}
	}

	/**
	 * Конвертирует локацию в пригодный для клиента формат.
	 */
	public static function toApi(Struct_Domain_Search_Location_Thread $location, int $user_id):array {

		return [
			"type"              => static::API_LOCATION_TYPE,
			"data"              => [
				"item"      => Type_Thread_Utils::prepareThreadMetaForFormat($location->thread_meta, $user_id),
				"hit_count" => $location->hit_count,
				"hit_list"  => Domain_Search_Entity_HitHandler::toApi($user_id, $location->hit_list),
			],

			// сервисное поле, куда сложим все идентификаторы пользователей сущности
			// чтобы в api-контроллере вернуть action users
			"_user_action_data" => array_keys($location->thread_meta["users"]),
		];
	}
}