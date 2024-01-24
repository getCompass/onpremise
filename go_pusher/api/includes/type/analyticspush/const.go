package analyticspush

const (
	TokenServiceFirebase     = 1
	TokenServiceApns         = 2
	TokenServiceFirebaseVoIP = 3
	TokenServiceApnsVoIP     = 4

	// статус пуша
	PushStatusTalkingWorking             = 1  // толкинг взял в работу
	PushStatusConversationMuted          = 2  // диалог замьючен
	PushStatusQueue                      = 3  // отправлен в очередь
	PushStatusPusherWorking              = 4  // pusher взял в работу
	PushStatusDeviceTokenRowNotFound     = 5  // не найдена запись в device_token
	PushStatusEmptyTokenList             = 6  // пустой token_list
	PushStatusNotificationsSnoozed       = 7  // у пользователя отключены уведолмения в приложении
	PushStatusNotificationsDisabled      = 8  // у пользователя отключен тип данного уведомления
	PushStatusWebSocketReceived          = 12 // статус означающий что сообщение отправлено на вебсокет
	PushStatusDeviceWithTokenListIsEmpty = 13 // список девайсов с токенами пользователя пуст
	PushStatusUnknownTokenVersion        = 14 // неизвестная версия токена
	PushStatusCompanyTokenNotExist       = 26 // отсутствует токен компании
	PushStatusPivotTalkingWorking        = 27 // толкинг взял в работу пуш пивота

	PushStatusNotValidTokenList    = 9  // статус означающий что никакой токен не прошел
	PushStatusSeveralTokenNotValid = 10 // статус означающий что несколько токенов не прошло
	PushStatusDone                 = 11 // статус означающий что все токены прошли ок

	PushStatusSendToApns     = 22 // отправили пуш на apns
	PushStatusSendToFirebase = 23 // отправили пуш на firebase

	// статус токена
	TokenStatusWorking = 15 // токен взят в работу
	TokenStatusResend  = 16 // токен отправлен на переотправку

	// конечные статусы
	TokenStatusSuccess            = 17 // токен успешно отправлен
	TokenStatusFailed             = 18 // не смогли переотправить токен
	TokenStatusInvalid            = 19 // токен не валиден
	TokenStatusTooManyRequestApns = 20 // статус означающий что было совершено много запросов к APNS
	TokenStatusTimeout            = 21 // токен отправлен с таймаутом
	TokenStatusUnknownError       = 24 // пуш упал с не известной ошибкой
	TokenStatusLargeBody          = 25 // пуш apns со слишком большим телом
)
