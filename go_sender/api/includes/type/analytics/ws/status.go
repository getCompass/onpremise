package analyticsWs

/*
 * модель, внутри которой хранятся методы с обновлением статуса ws
 */

// помечаем в аналитике, что открыли соединение ws
func (a *WsStruct) OnOpenWsConnect() {

	a.AddType(OnOpenWsConnect)
}

// помечаем в аналитике, что ивент отправлен
func (a *WsStruct) OnEventSend() {

	a.AddType(OnEventSend)
}

// помечаем в аналитике, что ивент не отправлен
func (a *WsStruct) OnEventIsNotSend() {

	a.AddType(OnEventIsNotSend)
}

// помечаем в аналитике, что закрыли соединение ws
func (a *WsStruct) OnCloseWsConnect() {

	a.AddType(OnCloseWsConnect)
}