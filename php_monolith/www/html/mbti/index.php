<?php

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use Compass\Pivot\cs_AnswerCommand;
use Compass\Pivot\cs_PlatformNotFound;
use Compass\Pivot\Domain_User_Entity_Validator;
use Compass\Pivot\Gateway_Db_PivotUser_MbtiSelectionList;
use Compass\Pivot\Type_Api_Platform;
use Compass\Pivot\User;

require_once __DIR__ . "/../../../start.php";

try {
	$user = User::init();
} catch (cs_AnswerCommand $e) {
	throw new EndpointAccessDeniedException("User not authorized for this actions.");
}

// если не авторизован
if ($user->user_id < 1) {
	throw new EndpointAccessDeniedException("User not authorized for this actions.");
}

// получаем параметры
$mbti_type = get("mbti_type", "");
$text_type = get("text_type", "");

// получаем платформу
try {
	$platform = Type_Api_Platform::getPlatform();
} catch (cs_PlatformNotFound $e) {
	throw new ParamException(__METHOD__ . ": unsupported platform");
}

// проводим валидацию mbti_type
if (!Domain_User_Entity_Validator::isMBTIType($mbti_type)) {
	throw new ParamException("selected is not available mbti_type");
}

// проверяем text_type
if (!in_array($text_type, ["description", "short_description"])) {
	throw new ParamException(__METHOD__ . ": select is not available text_type");
}

// получаем строку с selection нашего пользователя
try {
	$mbti_selection_row = Gateway_Db_PivotUser_MbtiSelectionList::getOne($user->user_id, $mbti_type, $text_type);
} catch (cs_RowIsEmpty) {
	$mbti_selection_row = [];
}

// получаем конфиг и нужный текст из него
$config = \Compass\Pivot\getConfig("MBTI_INFO");
$text   = $config[$mbti_type][$text_type];

// получаем color_selection_list
$color_selection_list = [];
if (isset($mbti_selection_row->user_id)) {
	$color_selection_list = $mbti_selection_row->color_selection_list;
}

// формируем контент и отдаем его
$content = file_get_contents(__DIR__ . "/../../../api/src/Compass/Pivot/templates/mbti.tpl");

if ($text_type == "description" && ($platform == Type_Api_Platform::PLATFORM_ANDROID || $platform == Type_Api_Platform::PLATFORM_IOS)) {
	$text = "<b>Описание типа личности " . $config[$mbti_type]["title"] . "</b>\n\n" . $text;
}
$content = str_replace("{TEXT}", $text, $content);
$content = str_replace("{COLOR_SELECTION_LIST}", toJson($color_selection_list), $content);

echo $content;
