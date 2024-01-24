package analyticspush

/*
 * модель, внутри которой хранятся методы с обновлением статуса пуша
 */

// помечаем в аналитике, что токен взят в работу
func (a *PushStruct) OnTalkingStartWorking() {

	a.AddType(PushStatusTalkingWorking)
}

// помечаем в аналитике, что токен взят в работу для пуша пивота
func (a *PushStruct) OnTalkingPivotStartWorking() {

	a.AddType(PushStatusPivotTalkingWorking)
}

// помечаем в аналитике, что диалог замьючен
func (a *PushStruct) OnConversationIsMuted() {

	a.AddType(PushStatusConversationMuted)
}

// помечаем в аналитике, что пуш отправлен в очередь на отправку
func (a *PushStruct) OnPushSendInQueue() {

	a.AddType(PushStatusQueue)
}

// помечаем в аналитике, что go_pusher взял пуш в работу
func (a *PushStruct) OnPusherStartWorking() {

	a.AddType(PushStatusPusherWorking)
}

// помечаем в аналитике, что пуш отправлен на firebase
func (a *PushStruct) OnPushSendToFirebase() {

	a.AddType(PushStatusSendToFirebase)
}

// помечаем в аналитике, что пуш отправлен на apns
func (a *PushStruct) OnPushSendToApns() {

	a.AddType(PushStatusSendToApns)
}

// помечаем в аналитике, что не найдена запись в таблицу device_token
func (a *PushStruct) OnDeviceTokenRowNotFound() {

	a.AddType(PushStatusDeviceTokenRowNotFound)
}

// помечаем в аналитике, что у пользователя пустой token_list
func (a *PushStruct) OnUserHaveEmptyTokenList() {

	a.AddType(PushStatusEmptyTokenList)
}

// помечаем в аналитике, что у пользователя пустой список девайсов с токенами
func (a *PushStruct) OnUserHaveEmptyDeviceWithTokenList() {

	a.AddType(PushStatusDeviceWithTokenListIsEmpty)
}

// помечаем в аналитике, что у пользователя отсутствует токен компании в девайсах
func (a *PushStruct) OnUserNotHaveCompanyTokenInDevices() {

	a.AddType(PushStatusCompanyTokenNotExist)
}

// помечаем в аналитике, что у пользователя отключены уведомления во всем приложении
func (a *PushStruct) OnPushNotificationsSnoozed() {

	a.AddType(PushStatusNotificationsSnoozed)
}

// помечаем в аналитике, что у пользователя отключены уведомления определенного типа
func (a *PushStruct) OnPushNotificationsDisabled() {

	a.AddType(PushStatusNotificationsDisabled)
}

// помечаем в аналитике, что сообщение было получено по вебсокету
func (a *PushStruct) OnWebSocketMessageReceived() {

	a.AddType(PushStatusWebSocketReceived)
}

// помечаем в аналитике, что список токенов невалиден
func (a *PushStruct) OnTokenListIsInvalid() {

	a.AddType(PushStatusNotValidTokenList)
}

// помечаем в аналитике, что несколько токенов невалидны
func (a *PushStruct) OnSeveralTokensIsInvalid() {

	a.AddType(PushStatusSeveralTokenNotValid)
}

// помечаем в аналитике, что пуши успешно отправлены на все токены
func (a *PushStruct) OnPushSuccessSendedToAllTokes() {

	a.AddType(PushStatusDone)
}

// помечаем в аналитике, что токен взят в работу
func (a *PushStruct) OnTokenStatusWorking() {

	a.AddType(TokenStatusWorking)
}

// помечаем в аналитике, что делаем переотправку на токен
func (a *PushStruct) DoResendToToken() {

	a.AddType(TokenStatusResend)
}

// помечаем в аналитике, что пуш на токен успешно отправлен
func (a *PushStruct) OnSendToTokenSuccess() {

	a.AddType(TokenStatusSuccess)
}

// помечаем в аналитике, что пуш на токен не отправлен
func (a *PushStruct) OnSendToTokenFailed() {

	a.AddType(TokenStatusFailed)
}

// помечаем в аналитике, что при переотправке токен оказался невалидным
func (a *PushStruct) OnResendTokenInvalid() {

	a.AddType(TokenStatusInvalid)
}

// помечаем в аналитике, что при переотправке сделали слишком много запросов к apns
func (a *PushStruct) OnResendTooManyRequestToApns() {

	a.AddType(TokenStatusTooManyRequestApns)
}

// помечаем в аналитике, что при переотправке случился таймаут
func (a *PushStruct) OnResendTimeout() {

	a.AddType(TokenStatusTimeout)
}
