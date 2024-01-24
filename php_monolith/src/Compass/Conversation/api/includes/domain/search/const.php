<?php

namespace Compass\Conversation;

use CompassApp\Pack\Conversation;
use CompassApp\Pack\Thread;
use CompassApp\Pack\Message\Conversation as ConversationMessage;
use CompassApp\Pack\Message\Thread as ThreadMessage;
use CompassApp\Pack\Preview;
use CompassApp\Pack\File;

/**
 * Класс с общими доменными параметрами.
 */
class Domain_Search_Const {

	/** @var int минимальная длина поискового запроса */
	public const MIN_SEARCH_STR_LENGTH = 3;

	public const TYPE_SPACE                = 0;
	public const TYPE_CONVERSATION         = 1 << 0;
	public const TYPE_THREAD               = 1 << 1;
	public const TYPE_CONVERSATION_MESSAGE = 1 << 2;
	public const TYPE_THREAD_MESSAGE       = 1 << 3;
	public const TYPE_PREVIEW              = 1 << 4;
	public const TYPE_FILE                 = 1 << 6;

	/**
	 * словарь – тип сущности поиска – тип сущности мапы
	 * используется для проверки, чтобы не наломать дров при создании сущности entity_search_id_rel_{f}
	 */
	public const ENTITY_TYPE_MAP_ENTITY_REL = [
		self::TYPE_CONVERSATION         => Conversation::MAP_ENTITY_TYPE,
		self::TYPE_THREAD               => Thread::MAP_ENTITY_TYPE,
		self::TYPE_CONVERSATION_MESSAGE => ConversationMessage::MAP_ENTITY_TYPE,
		self::TYPE_THREAD_MESSAGE       => ThreadMessage::MAP_ENTITY_TYPE,
		self::TYPE_PREVIEW              => Preview::MAP_ENTITY_TYPE,
		self::TYPE_FILE                 => File::MAP_ENTITY_TYPE,
	];

	// атрибуты сущностей
	public const ATTRIBUTE_SHARED_BELONG_TO_CONVERSATION = 1 << 1;
	public const ATTRIBUTE_SHARED_BELONG_TO_THREAD       = 1 << 2;

	// задачи индексации пространства
	public const TASK_TYPE_SPACE_INIT_REINDEX          = 1;
	public const TASK_TYPE_SPACE_REINDEX_CONVERSATIONS = 2;

	// задачи индексации сообщений
	public const TASK_TYPE_CONVERSATION_MESSAGE_INDEX   = 10;
	public const TASK_TYPE_CONVERSATION_MESSAGE_REINDEX = 11;
	public const TASK_TYPE_CONVERSATION_MESSAGE_DELETE  = 12;
	public const TASK_TYPE_CONVERSATION_MESSAGE_HIDE    = 13;

	// задачи индексации комментариев
	public const TASK_TYPE_THREAD_MESSAGE_INDEX   = 20;
	public const TASK_TYPE_THREAD_MESSAGE_REINDEX = 21;
	public const TASK_TYPE_THREAD_MESSAGE_DELETE  = 22;
	public const TASK_TYPE_THREAD_MESSAGE_HIDE    = 23;

	// задачи индексации превью в диалогах
	public const TASK_TYPE_CONVERSATION_PREVIEW_INDEX   = 30;
	public const TASK_TYPE_CONVERSATION_PREVIEW_REINDEX = 31;

	// задачи индексации превью в комментариях
	public const TASK_TYPE_THREAD_PREVIEW_INDEX   = 40;
	public const TASK_TYPE_THREAD_PREVIEW_REINDEX = 41;

	// задачи индексации файла
	public const TASK_TYPE_FILE_INDEX   = 50;
	public const TASK_TYPE_FILE_REINDEX = 51;

	// задачи индексации превью в диалогах
	public const TASK_TYPE_CONVERSATION_FILE_INDEX   = 60;
	public const TASK_TYPE_CONVERSATION_FILE_REINDEX = 61;

	// задачи индексации превью в комментариях
	public const TASK_TYPE_THREAD_FILE_INDEX   = 70;
	public const TASK_TYPE_THREAD_FILE_REINDEX = 71;

	// задачи переиндексации лент сообщений
	public const TASK_TYPE_CONVERSATION_REINDEX = 80;
	public const TASK_TYPE_THREAD_REINDEX       = 90;

	// задачи очистки ленты сообщений
	public const TASK_TYPE_CONVERSATION_CLEAR = 100;
	public const TASK_TYPE_CONVERSATION_PURGE = 110;

	// разрешённые для поиска символы
	public const ALLOWED_SEARCH_QUERY_SYMBOLS = [
		"<", "/", ">", "@", "#", "$", "&", "(", ")", "-", "–", "—", "º", "ª", "£", "§", "]", "[",
		"}", "{", "₽", "ƒ", "»", "«", "!", "|", "æ", "£", "€", "¥", ":",
	];
}