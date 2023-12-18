<?php

// переводят название клиенты по системному типу сообщению, поэтому только русский
$CONFIG["LOCALE_TEXT"] = [
	"ru-RU" => [
		"hiring_request"    => [
			"system_message_text_on_create_from_link"          => "Поделился ссылкой на приглашение в компанию",
			"system_message_text_on_candidate_accept_link"     => "Заявка на вступление в компанию",
			"system_message_text_on_confirm"                   => "Одобрил заявку",
			"system_message_text_on_reject"                    => "Отклонил заявку",
			"system_message_text_on_revoke_self"               => "Заявка отозвана",
			"system_message_text_on_dismiss"                   => "Увольнение одобрено",
			"system_message_text_on_candidate_entered_company" => "Вступил в компанию",
			"system_message_text_on_left_company"              => "Покинул компанию",
		],
		"dismissal_request" => [
			"system_message_text_on_create"       => "Создана заявка на увольнение",
			"system_message_text_on_create_self"  => "Создана заявка на самоувольнение",
			"system_message_text_on_approve"      => "Увольнение одобрено",
			"system_message_text_on_reject"       => "Заявка отклонена",
			"system_message_text_on_left_company" => "Покинул компанию",
		],
		"user_card"         => [
			"user_received_respect"      => "Новая благодарность",
			"user_received_exactingness" => "Новая требовательность",
			"user_added_exactingness"    => "Требовательность за неделю: {week_count}, за месяц: {month_count}.",
			"user_received_achievement"  => "Новое достижение",
		],
		"follow_thread"     => [
			"user_followed_thread" => "Подписался на комментарии",
		],
	],
];

return $CONFIG;
