<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с задачей на увольнение
 */
class Domain_User_Entity_TaskExitStep {

	public const CONVERSATION_DIALOGS_STEP       = 1; // модуль conversation закончил очистку диалогов
	public const CONVERSATION_INVITES_STEP       = 2; // модуль conversation закончил очистку инвайтов
	public const THREAD_THREADS_STEP             = 3; // модуль thread закончил отписку от тредов
	public const COMPANY_MEMBER_STEP             = 4; // модуль company закончил отписку участника компании
	public const CONVERSATION_DIALOGS_CHECK_STEP = 101; // шаг проверки что диалогов больше нет
	public const CONVERSATION_INVITES_CHECK_STEP = 102; // шаг проверки что приглашений больше нет
	public const THREAD_THREADS_CHECK_STEP       = 104; // шаг проверки что тредов больше нет
	public const COMPANY_MEMBER_CHECK_STEP       = 105; // шаг проверки, что пользователь как участник компании почищен
	public const EXIT_FINISH_STEP                = 200; // шаг что проверка завершена

	public const FIRST_CLEAR_STEP = self::CONVERSATION_DIALOGS_STEP;
	public const LAST_CLEAR_STEP  = self::COMPANY_MEMBER_STEP;
	public const FIRST_CHECK_STEP = self::CONVERSATION_DIALOGS_CHECK_STEP;
	public const LAST_CHECK_STEP  = self::COMPANY_MEMBER_CHECK_STEP;

	// список всех шагов увольнения
	public const FULL_EXIT_STEP_LIST = [
		"company"      => [
			self::COMPANY_MEMBER_STEP => self::STEPS_COMPANY,
		],
		"conversation" => [
			self::CONVERSATION_DIALOGS_STEP => self::STEPS_CONVERSATION_DIALOGS,
			self::CONVERSATION_INVITES_STEP => self::STEPS_CONVERSATION_INVITES,
		],
		"thread"       => [
			self::THREAD_THREADS_STEP => self::STEPS_THREAD,
		],
	];

	// методы которые нужно вызвать в модуле conversation для очистки диалогов
	public const STEPS_CONVERSATION_DIALOGS = [
		"conversations.clearConversationsForUser" => [
			"user_id"               => 0,
			"limit"                 => 75,
			"offset"                => 0,
			"iteration_is_possible" => 0,
		],
	];

	// методы которые нужно вызвать в модуле conversation для очистки инвайтов
	public const STEPS_CONVERSATION_INVITES = [
		"invites.clearInvitesForUser" => [
			"user_id"               => 0,
			"limit"                 => 75,
			"offset"                => 0,
			"iteration_is_possible" => 0,
		],
	];

	// методы которые нужно вызвать в модуль thread
	public const STEPS_THREAD = [
		"threads.clearThreadsForUser" => [
			"user_id"               => 0,
			"limit"                 => 75,
			"offset"                => 0,
			"iteration_is_possible" => 0,
		],
	];

	// методы, которые нужно вызвать в модуле company
	public const STEPS_COMPANY = [
		"clearMember" => [
			"iteration_is_possible" => 0,
		],
	];

	public const STEPS_CHECKER = [
		"conversation" => [
			self::CONVERSATION_DIALOGS_CHECK_STEP => [
				"conversations.checkClearConversationsForUser" => [
					"user_id"               => 0,
					"limit"                 => 75,
					"offset"                => 0,
					"iteration_is_possible" => 0,
				],
			],
			self::CONVERSATION_INVITES_CHECK_STEP => [
				"invites.checkClearInvitesForUser" => [
					"user_id"               => 0,
					"limit"                 => 75,
					"offset"                => 0,
					"iteration_is_possible" => 0,
				],
			],
		],
		"thread"       => [
			self::THREAD_THREADS_CHECK_STEP => [
				"threads.checkClearThreadsForUser" => [
					"user_id"               => 0,
					"limit"                 => 75,
					"offset"                => 0,
					"iteration_is_possible" => 0,
				],
			],
		],
		"company"      => [
			self::COMPANY_MEMBER_CHECK_STEP => [
				"checkClearMember" => [
					"iteration_is_possible" => 0,
				],
			],
		],
	];
}
