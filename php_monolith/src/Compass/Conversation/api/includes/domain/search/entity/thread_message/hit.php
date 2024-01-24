<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\Locale;
use CompassApp\Pack\Message;

/**
 * Класс совпадения поиска «Сообщение-комментарий».
 * Большую часть логики этого класса нужно унести в треды, но как это сделать аккуратно пока нет идей.
 */
class Domain_Search_Entity_ThreadMessage_Hit extends Domain_Search_Entity_Hit {

	protected const _HIT_SPOT_BODY        = 1 << 0;
	protected const _HIT_SPOT_NESTED_BODY = 1 << 1;

	protected const _HIT_SPOT_PREVIEW_HEADER = 1 << 0;
	protected const _HIT_SPOT_PREVIEW_BODY   = 1 << 1;

	protected const _HIT_SPOT_FILE_NAME    = 1 << 0;
	protected const _HIT_SPOT_FILE_CONTENT = 1 << 1;

	// тип совпадения
	public const HIT_TYPE         = Domain_Search_Const::TYPE_THREAD_MESSAGE;
	public const PREVIEW_HIT_TYPE = Domain_Search_Const::TYPE_PREVIEW;
	public const FILE_HIT_TYPE    = Domain_Search_Const::TYPE_FILE;
	public const API_HIT_TYPE     = "thread_message";

	// возможные места совпадения
	public const HIT_SPOT_MESSAGE_BODY          = "message_body";
	public const HIT_SPOT_MESSAGE_NESTED_BODY   = "message_nested_text";
	public const HIT_SPOT_MESSAGE_QUOTE_BODY    = "message_nested_text";
	public const HIT_SPOT_FILE_NAME             = "file_name";
	public const HIT_SPOT_FILE_CONTENT          = "file_content";
	public const HIT_SPOT_PREVIEW_HEADER        = "preview_header";
	public const HIT_SPOT_PREVIEW_BODY          = "preview_body";
	public const HIT_SPOT_NESTED_FILE_NAME      = "nested_file_name";
	public const HIT_SPOT_NESTED_FILE_CONTENT   = "nested_file_content";
	public const HIT_SPOT_NESTED_PREVIEW_HEADER = "nested_preview_header";
	public const HIT_SPOT_NESTED_PREVIEW_BODY   = "nested_preview_body";

	/**
	 * Возвращает список всех совпадений типа «Сообщение-комментарий».
	 *
	 * @param int                                    $user_id
	 * @param Struct_Domain_Search_RawHit[]          $hit_list
	 * @param Struct_Domain_Search_Dto_SearchRequest $params
	 *
	 * @long
	 * @return Struct_Domain_Search_Hit_ThreadMessage[]
	 */
	public static function loadSuitable(int $user_id, array $hit_list, Struct_Domain_Search_Dto_SearchRequest $params):array {

		// загружаем нужные сообщения и прочие вспомогательные штуки
		/** @var \Compass\Thread\Struct_Db_CompanyThread_ThreadDynamic[] $thread_dynamic_list */
		[$msg_list, $thread_meta_list, $thread_dynamic_list, $parent_conversation_msg_list, $left_menu_item_list, $parent_conversation_dynamic] = static::_loadRequired($user_id, $hit_list);

		// подгружаем extra сущности (файлы, превью) из совпадений, результат функции не используем – он сохранился в proxy_cache
		static::_loadExtraEntities($hit_list);

		$conversation_cleared_till_list = [];
		$output                         = [];

		foreach ($hit_list as $hit) {

			// убеждаемся, что сообщение найдено
			if (!isset($msg_list[$hit->entity_rel->entity_map])) {
				continue;
			}

			$message    = $msg_list[$hit->entity_rel->entity_map];
			$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message["message_map"]);

			// по какой-то причине мета треда не найдена
			if (!isset($thread_meta_list[$thread_map])) {
				continue;
			}

			$thread_meta        = $thread_meta_list[$thread_map];
			$parent_entity_type = \Compass\Thread\Type_Thread_ParentRel::getType($thread_meta["parent_rel"]);

			// проверяем, что само сообщение доступно пользователю в выдаче
			if (!static::_isMessageVisibleForUser($user_id, $message)) {
				continue;
			}

			if ($parent_entity_type === \Compass\Thread\PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE) {

				$parent_conversation_map         = \Compass\Thread\Type_Thread_SourceParentRel::getMap($thread_meta["source_parent_rel"]);
				$parent_conversation_message_map = \Compass\Thread\Type_Thread_ParentRel::getMap($thread_meta["parent_rel"]);

				// провреяем, что родительское сообщение нашлось для сообщения-комметрия
				// и что родительски даилог доступен пользователю для чтения
				if (!isset(
					$parent_conversation_msg_list[$parent_conversation_message_map],
					$parent_conversation_dynamic[$parent_conversation_map],
					$left_menu_item_list[$parent_conversation_map])
				) {
					continue;
				}

				// считаем дату, до которой у пользователя очищен диалог
				$conversation_cleared_till_list = static::_fillConversationClearedUntil(
					$user_id, $parent_conversation_dynamic[$parent_conversation_map], $conversation_cleared_till_list
				);

				if (!static::_isParentVisibleForUser(
					$user_id,
					$conversation_cleared_till_list[$parent_conversation_map],
					$parent_conversation_msg_list[$parent_conversation_message_map])
				) {
					continue;
				}

				// считаем блок для сообщения
				$message_block_id          = \CompassApp\Pack\Message\Thread::getBlockId($message["message_map"]);
				$previous_message_block_id = max($message_block_id - 1, $thread_dynamic_list[$thread_map]->start_block_id);
				$next_message_block_id     = $message_block_id + 1;

				$output[] = new Struct_Domain_Search_Hit_ThreadMessage(
					$message, $parent_conversation_msg_list[$parent_conversation_message_map], static::_makeHitExtra($hit, $message, $params),
					$hit->updated_at, $previous_message_block_id, $next_message_block_id, []
				);
			}
		}

		return array_filter($output);
	}

	/**
	 * Загружает необходимые сущности.
	 *
	 * @param int                           $user_id
	 * @param Struct_Domain_Search_RawHit[] $raw_hit_list
	 *
	 * @return array
	 */
	protected static function _loadRequired(int $user_id, array $raw_hit_list):array {

		// фильтруем совпадения и конвертируем их в набор map сообщений
		$raw_hit_list = array_filter($raw_hit_list, static fn(Struct_Domain_Search_RawHit $hit):bool => $hit->entity_rel->entity_type === static::HIT_TYPE);

		// вытаскиваем связи из совпадений и получаем список идентификаторов сообщегний
		$search_entity_rel_list = array_column($raw_hit_list, "entity_rel");
		$message_map_list       = array_column($search_entity_rel_list, "entity_map");

		// загружаем все необходимые сообщения
		// и удаляем из списка сообщения, недоступность которых можно определить сейчас
		$message_list = Domain_Search_Repository_ProxyCache_ThreadMessage::load($message_map_list);

		// загружаем все связанные меты тредов
		$thread_map_list     = array_unique(array_map(static fn(array $msg):string => \CompassApp\Pack\Message\Thread::getThreadMap($msg["message_map"]), $message_list));
		$thread_meta_list    = Domain_Search_Repository_ProxyCache_ThreadMeta::load($thread_map_list);
		$thread_dynamic_list = Domain_Search_Repository_ProxyCache_ThreadDynamic::load($thread_map_list);

		$required_conversation_message_map_list = [];
		$required_conversation_map_list         = [];

		foreach ($thread_meta_list as $index => $meta_row) {

			// получаем тип родительской сущности
			// в текущий момент обрабатываем только треды к сообщениям в диалогах
			$parent_entity_type = \Compass\Thread\Type_Thread_ParentRel::getType($meta_row["parent_rel"]);

			if ($parent_entity_type === \Compass\Thread\PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE) {

				$required_conversation_map_list[]         = \Compass\Thread\Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
				$required_conversation_message_map_list[] = \Compass\Thread\Type_Thread_ParentRel::getMap($meta_row["parent_rel"]);

				continue;
			}

			// если нет попаданий по родительским сущностям,
			// то заносим из в список на открепление (они недоступны в поиске)
			unset($thread_meta_list[$index]);
		}

		// загружаем родительские сообщения и диалоги этих сообщений
		$parent_conversation_message_list = Domain_Search_Repository_ProxyCache_ConversationMessage::load($required_conversation_message_map_list);
		$left_menu_item_list              = Domain_Search_Repository_ProxyCache_LeftMenuItem::load($user_id, $required_conversation_map_list);
		$parent_conversation_dynamic      = Domain_Search_Repository_ProxyCache_ConversationDynamic::load($required_conversation_map_list);

		return [$message_list, $thread_meta_list, $thread_dynamic_list, $parent_conversation_message_list, $left_menu_item_list, $parent_conversation_dynamic];
	}

	/**
	 * Добавляет в массив дату очистки диалога для пользователя.
	 */
	protected static function _fillConversationClearedUntil(int $user_id, Struct_Db_CompanyConversation_ConversationDynamic $dynamic, array $conversation_cleared_till_list):array {

		// считаем дату, до которой у пользователя очищен диалог
		if (!isset($conversation_cleared_till_list[$dynamic->conversation_map])) {

			$conversation_cleared_till_list[$dynamic->conversation_map] = Domain_Conversation_Entity_Dynamic::getClearUntil(
				$dynamic->user_clear_info,
				$dynamic->conversation_clear_info,
				$user_id
			);
		}

		return $conversation_cleared_till_list;
	}

	/**
	 * Проверяет, доступно ли родительское сообщение для поиска.
	 */
	protected static function _isParentVisibleForUser(int $user_id, int $conversation_cleared_till, array $message):bool {

		$message_handler = Type_Conversation_Message_Main::getHandler($message);

		// если дата создания сообщения раньше, чем отметка до которой пользователь очистил диалог
		if ($message_handler::getCreatedAt($message) < $conversation_cleared_till) {
			return false;
		}

		if ($message_handler::isMessageHiddenForUser($message, $user_id)) {
			return false;
		}

		if ($message_handler::isMessageDeleted($message)) {
			return false;
		}

		if ($message_handler::isMessageDeletedBySystem($message)) {
			return false;
		}

		return true;
	}

	/**
	 * Проверяет, видно ли найденное сообщение.
	 */
	protected static function _isMessageVisibleForUser(int $user_id, array $message):bool {

		$message_handler = \Compass\Thread\Type_Thread_Message_Main::getHandler($message);

		if ($message_handler::isMessageHiddenForUser($message, $user_id)) {
			return false;
		}

		if ($message_handler::isMessageDeleted($message)) {
			return false;
		}

		if ($message_handler::isMessageDeletedBySystem($message)) {
			return false;
		}

		return true;
	}

	/**
	 * Формирует экстра данные для совпадения.
	 * @noinspection DuplicatedCode
	 */
	protected static function _makeHitExtra(Struct_Domain_Search_RawHit $hit, array $message, Struct_Domain_Search_Dto_SearchRequest $query_param):Struct_Domain_Search_HitExtra {

		$spot_detail_list = [];

		[$cleared_source_text, $replacement, $highlighted_list] = Domain_Search_Helper_Highlight::highlight(
			\Compass\Thread\Type_Thread_Message_Main::getHandler($message)::getText($message), $query_param->raw_query, [$query_param->user_locale, Locale::LOCALE_ENGLISH]
		);

		// если есть совпадение в теле сообщения
		if ($hit->field_mask & static::_HIT_SPOT_BODY) {

			$spot_detail_list[] = new Struct_Domain_Search_SpotDetail(
				static::HIT_SPOT_MESSAGE_BODY,
				null,
				new Struct_Domain_Search_MessageHighlight($cleared_source_text, $replacement, $highlighted_list),
			);
		}

		// если есть совпадение в теле вложенных сообщений
		if ($hit->field_mask & static::_HIT_SPOT_NESTED_BODY) {
			array_push($spot_detail_list, ...static::_makeNestedMessageTextSpotDetails($message, $query_param));
		}

		// если есть совпадения в дочерних сущностях (файлы, превью)
		foreach ($hit->extra as $extended_hit) {

			$extra_spot_detail_list = match ($extended_hit->entity_rel->entity_type) {
				static::PREVIEW_HIT_TYPE => static::_makeExtraPreviewSpotDetails($message, $extended_hit, $query_param),
				static::FILE_HIT_TYPE => static::_makeExtraFileSpotDetails($message, $extended_hit, $query_param),
				default => throw new ParseFatalException("unexpected behaviour" . var_export($extended_hit, true)),
			};

			array_push($spot_detail_list, ...$extra_spot_detail_list);
		}

		$message_text_replacement_list = static::_makeMessageTextReplacementList($message, $cleared_source_text, $query_param);

		return new Struct_Domain_Search_HitExtra(array_unique(array_column($spot_detail_list, "field")), $spot_detail_list, $message_text_replacement_list);
	}

	/**
	 * Проходит по всем вложенным сообщения и возвращает мап тех, в которых есть частичное совпадение.
	 *
	 * Поскольку вложенные сообщения проиндексированы большим куском текста,
	 * то вычислять, какие именно нужно подсветить нужно руками.
	 *
	 * @return Struct_Domain_Search_SpotDetail[]
	 */
	protected static function _makeNestedMessageTextSpotDetails(array $message, Struct_Domain_Search_Dto_SearchRequest $query_param):array {

		// получаем все сообщения
		$nested_message_list = Type_Conversation_Message_Main::getHandler($message)::getFlatNestedMessageList($message);
		$output              = [];

		foreach ($nested_message_list as $index => $nested_message) {

			$cleared_text = Domain_Search_Helper_FormattingCleaner::clear(Type_Conversation_Message_Main::getHandler($nested_message)::getText($nested_message));
			[$cleared_source_text, $highlighted_text, $highlighed_list] = Domain_Search_Helper_Highlight::highlight($cleared_text, $query_param->raw_query, [$query_param->user_locale, Locale::LOCALE_ENGLISH]);

			// если ничего не подсветилось, предполагаем, что совпадения нет
			// важно — сравнивать исходный и подсвеченный текст нельзя — там есть подмена символа «_»
			if (count($highlighed_list) === 0) {
				continue;
			}

			$output[] = new Struct_Domain_Search_SpotDetail(
				static::HIT_SPOT_MESSAGE_NESTED_BODY,
				[
					"nested_message_index" => $index,
					"message_map"          => $nested_message["message_map"],
				],
				new Struct_Domain_Search_MessageHighlight($cleared_source_text, $highlighted_text, $highlighed_list),
			);
		}

		return $output;
	}

	/**
	 * Формирует spot_detail_list при совпадении в файле.
	 */
	protected static function _makeExtraFileSpotDetails(array $message, Struct_Domain_Search_RawHit $file_extra_hit, Struct_Domain_Search_Dto_SearchRequest $query_param):array {

		$loaded = Domain_Search_Repository_ProxyCache_File::load([$file_extra_hit->entity_rel->entity_map]);
		$file   = $loaded[$file_extra_hit->entity_rel->entity_map];

		if ($file === false) {
			return [];
		}

		// пытаемся сматчить файл с родительским сообщение
		// возможно нет смысла дальше пытаться матчить, кажется,
		// что при нахождении в родительском, во вложенных файла не может быть
		$is_file_belongs_to_parent_message = Type_Conversation_Message_Main::getHandler($message)::isFileBelongsToMessage($file, $message);

		// получаем все сообщения
		$nested_message_list         = Type_Thread_Message_Main::getHandler($message)::getFlatNestedMessageList($message);
		$matched_nested_message_list = [];

		foreach ($nested_message_list as $nested_message) {

			if (Type_Thread_Message_Main::getHandler($message)::isFileBelongsToMessage($file, $nested_message)) {
				$matched_nested_message_list[] = $nested_message;
			}
		}

		$output = [];

		// делаем данные подсветки для родительского
		if ($is_file_belongs_to_parent_message) {

			$spot_name_list = static::_makeExtraFileHitSpotNameList(static::HIT_SPOT_FILE_NAME, static::HIT_SPOT_FILE_CONTENT);
			$output         = static::_makeExtraFileHitSpotDetails($file, $file_extra_hit, $query_param, $spot_name_list);
		}

		// делаем данные подсветки для вложенных
		foreach ($matched_nested_message_list as $matched_nested_message) {

			$spot_name_list = static::_makeExtraFileHitSpotNameList(static::HIT_SPOT_NESTED_FILE_NAME, static::HIT_SPOT_NESTED_FILE_CONTENT);
			array_push($output, ...static::_makeExtraFileHitSpotDetails($file, $file_extra_hit, $query_param, $spot_name_list, [
				"message_map" => $matched_nested_message["message_map"],
			]));
		}

		return $output;
	}

	/**
	 * Формирует spot_detail_list при совпадении в файле.
	 */
	protected static function _makeExtraPreviewSpotDetails(array $message, Struct_Domain_Search_RawHit $preview_extra_hit, Struct_Domain_Search_Dto_SearchRequest $query_param):array {

		$loaded  = Domain_Search_Repository_ProxyCache_Preview::load([$preview_extra_hit->entity_rel->entity_map]);
		$preview = $loaded[$preview_extra_hit->entity_rel->entity_map];

		if ($preview === false) {
			return [];
		}

		// пытаемся сматчить превью с родительским сообщение
		$is_preview_belongs_to_parent_message = Type_Conversation_Message_Main::getHandler($message)::isPreviewBelongsToMessage($preview, $message);

		// получаем все сообщения
		$nested_message_list         = Type_Conversation_Message_Main::getHandler($message)::getFlatNestedMessageList($message);
		$matched_nested_message_list = [];

		foreach ($nested_message_list as $nested_message) {

			if (Type_Conversation_Message_Main::getHandler($message)::isPreviewBelongsToMessage($preview, $nested_message)) {
				$matched_nested_message_list[] = $nested_message;
			}
		}

		$output = [];

		// делаем данные подсветки для родительского
		if ($is_preview_belongs_to_parent_message) {

			$spot_name_list = static::_makeExtraPreviewHitSpotNameList(static::HIT_SPOT_PREVIEW_HEADER, static::HIT_SPOT_PREVIEW_BODY);
			$output         = static::_makeExtraPreviewHitSpotDetails($preview, $preview_extra_hit, $query_param, $spot_name_list);
		}

		// делаем данные подсветки для вложенных
		foreach ($matched_nested_message_list as $matched_nested_message) {

			$spot_name_list = static::_makeExtraPreviewHitSpotNameList(static::HIT_SPOT_NESTED_PREVIEW_HEADER, static::HIT_SPOT_NESTED_PREVIEW_BODY);
			array_push($output, ...static::_makeExtraPreviewHitSpotDetails($preview, $preview_extra_hit, $query_param, $spot_name_list, [
				"message_map" => $matched_nested_message["message_map"],
			]));
		}

		return $output;
	}

	/**
	 * Проходит по всем сообщениям и возвращает структуру данных без форматирования.
	 */
	public static function _makeMessageTextReplacementList(array $message, string $cleared_source_text, Struct_Domain_Search_Dto_SearchRequest $query_param):array {

		$message_text_replacement_list[] = new Struct_Domain_Search_MessageTextReplacement(
			static::HIT_SPOT_MESSAGE_BODY,
			new Struct_Domain_Search_MessageTextReplacementData(
				$cleared_source_text,
				Message::doEncrypt(Type_Thread_Message_Main::getHandler($message)::getMessageMap($message)),
			),
		);

		// получаем все сообщения
		$nested_message_list = Type_Thread_Message_Main::getHandler($message)::getFlatNestedMessageList($message);

		foreach ($nested_message_list as $index => $nested_message) {

			$cleared_text = Domain_Search_Helper_FormattingCleaner::clear(Type_Thread_Message_Main::getHandler($nested_message)::getText($nested_message));
			[$cleared_source_text] = Domain_Search_Helper_Highlight::highlight($cleared_text, $query_param->raw_query, [$query_param->user_locale, Locale::LOCALE_ENGLISH]);

			$message_text_replacement_list[] = new Struct_Domain_Search_MessageTextReplacement(
				static::HIT_SPOT_MESSAGE_NESTED_BODY,
				new Struct_Domain_Search_MessageTextReplacementData(
					$cleared_source_text,
					Message::doEncrypt(Type_Thread_Message_Main::getHandler($nested_message)::getMessageMap($nested_message)),
				),
			);
		}

		return $message_text_replacement_list;
	}

	/**
	 * Конвертирует совпадение в пригодный для клиента формат.
	 */
	public static function toApi(Struct_Domain_Search_Hit_ThreadMessage $hit, int $user_id):array {

		return [
			"type"                 => static::API_HIT_TYPE,
			"data"                 => [
				"item"                      => \Compass\Thread\Type_Thread_Message_Main::getHandler($hit->item)::prepareForFormat($hit->item),
				"parent"                    => [
					"type" => Domain_Search_Entity_ConversationMessage_Hit::HIT_TYPE,
					"data" => [
						"item" => Type_Conversation_Message_Main::getHandler($hit->parent)::prepareForFormat($hit->parent),
					],
				],
				"extra"                     => [
					"spot_list"        => $hit->extra->spot_list,
					"spot_detail_list" => array_map(
						static fn(Struct_Domain_Search_SpotDetail $el) => [
							"spot" => $el->field,
							"data" => [
								"highlight" => $el->highlight_info,
								"extra"     => $el->field_extra,
							],
						],
						$hit->extra->spot_detail_list
					),
					"message_text_replacement_list" => $hit->extra->message_text_replacement_list,
				],
				"previous_message_block_id" => $hit->previous_message_block,
				"next_message_block_id"     => $hit->next_message_block,
			],
			"nested_location_list" => Domain_Search_Entity_LocationHandler::toApi($user_id, $hit->nested_location_list),

			// сервисное поле, куда сложим все идентификаторы пользователей сущности
			// чтобы в api-контроллере вернуть action users
			"_user_action_data"    => \Compass\Thread\Type_Thread_Message_Main::getHandler($hit->item)::getUsers($hit->item),
		];
	}
}