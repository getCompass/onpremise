<?php

namespace Compass\Pivot;

$CONFIG["LOCALE_TEXT"] = [
	"ru-RU" => [
		"sms_confirm"        => [
			"auth"           => "{sms_code} — код для авторизации в Compass",
			"change_phone"   => "{sms_code} — код для смены номера телефона в Compass",
			"delete_company" => "{sms_code} — код для удаления команды в Compass",
			"self_dismissal" => "{sms_code} — код для выхода из команды в Compass",
			"delete_profile" => "{sms_code} — код для удаления аккаунта в Compass",
		],
		"mail_registration"  => [
			"title"   => "Код подтверждения – {confirm_code}",
			"content" => "Ваш код:<h3>{confirm_code}</h3>Используйте его, чтобы подтвердить адрес электронной почты и зарегистрировать аккаунт в корпоративном мессенджере Compass On-premise.",
		],
		"mail_authorization" => [
			"title"   => "Код подтверждения – {confirm_code}",
			"content" => "Ваш код:<h3>{confirm_code}</h3>Используйте его, чтобы подтвердить адрес электронной почты и авторизоваться в корпоративном мессенджере Compass On-premise.",
		],
		"mail_restore"       => [
			"title"   => "Код восстановления – {confirm_code}",
			"content" => "Ваш код:<h3>{confirm_code}</h3>Используйте его для сброса пароля и восстановления доступа к аккаунту в корпоративном мессенджере Compass On-premise.",
		],
	],
	"en-US" => [
		"sms_confirm"        => [
			"auth"           => "Code {sms_code} for authorization in Compass",
			"change_phone"   => "Code {sms_code} to change the phone number in Compass",
			"delete_company" => "{sms_code} — use this code to delete the team in Compass",
			"self_dismissal" => "{sms_code} — use this code to leave the team in Compass",
			"delete_profile" => "Code {sms_code} to delete your account in Compass",
		],
		"mail_confirm"       => [
			"title"   => "",
			"content" => "",
		],
		"mail_authorization" => [
			"title"   => "",
			"content" => "",
		],
		"mail_restore"       => [
			"title"   => "",
			"content" => "",
		],
	],
	"de-DE" => [
		"sms_confirm"        => [
			"auth"           => "Code {sms_code} für Autorisierung in Compass",
			"change_phone"   => "Code {sms_code} für Änderung der Telefonnummer in Compass",
			"delete_company" => "{sms_code} — Code zum Team-Löschen in Compass",
			"self_dismissal" => "{sms_code} — Code zum Verlassen des Teams in Compass",
			"delete_profile" => "Code {sms_code} für Kontolöschung in Compass",
		],
		"mail_confirm"       => [
			"title"   => "",
			"content" => "",
		],
		"mail_authorization" => [
			"title"   => "",
			"content" => "",
		],
		"mail_restore"       => [
			"title"   => "",
			"content" => "",
		],
	],
	"fr-FR" => [
		"sms_confirm"        => [
			"auth"           => "Code {sms_code} à autoriser au Compass",
			"change_phone"   => "Code {sms_code} pour changer votre numéro de téléphone au Compass",
			"delete_company" => "{sms_code} — code pour supprimer l'équipe dans Compass",
			"self_dismissal" => "{sms_code} — code pour quitter l'équipe dans Compass",
			"delete_profile" => "Code {sms_code} pour supprimer le compte au Compass",
		],
		"mail_confirm"       => [
			"title"   => "",
			"content" => "",
		],
		"mail_authorization" => [
			"title"   => "",
			"content" => "",
		],
		"mail_restore"       => [
			"title"   => "",
			"content" => "",
		],
	],
	"it-IT" => [
		"sms_confirm"        => [
			"auth"           => "Codice {sms_code} per l'autorizzazione in Compass",
			"change_phone"   => "Codice {sms_code} per la modifica del numero di telefono in Compass",
			"delete_company" => "{sms_code} — codice per eliminare il team in Compass",
			"self_dismissal" => "{sms_code} — codice per uscire dal team in Compass",
			"delete_profile" => "Codice {sms_code} per l'eliminazione dell'account in Compass",
		],
		"mail_confirm"       => [
			"title"   => "",
			"content" => "",
		],
		"mail_authorization" => [
			"title"   => "",
			"content" => "",
		],
		"mail_restore"       => [
			"title"   => "",
			"content" => "",
		],
	],
	"es-ES" => [
		"sms_confirm"        => [
			"auth"           => "Código {sms_code} para autorización en Compass",
			"change_phone"   => "Código {sms_code} para cambiar el número de teléfono en Compass",
			"delete_company" => "{sms_code} — código para eliminar el equipo en Compass",
			"self_dismissal" => "{sms_code} — código para salir del equipo en Compass",
			"delete_profile" => "Código {sms_code} para eliminar la cuenta en Compass",
		],
		"mail_confirm"       => [
			"title"   => "",
			"content" => "",
		],
		"mail_authorization" => [
			"title"   => "",
			"content" => "",
		],
		"mail_restore"       => [
			"title"   => "",
			"content" => "",
		],
	],
];

return $CONFIG;
