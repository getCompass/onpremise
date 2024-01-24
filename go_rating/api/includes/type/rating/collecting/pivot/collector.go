package pivot

import (
	"fmt"
	Isolation "go_rating/api/includes/type/isolation"
	"sync"
)

// ScreentimeProvider интерфейс для всего,
// что хочет слать экранное время в pivot
type ScreentimeProvider interface {
	CollectScreenTime() []*UserScreenTimeListStruct
}

// UserScreenTimeListStruct структура объекта
type UserScreenTimeListStruct struct {
	SpaceId            int64                   `json:"space_id"`
	CacheAt            int64                   `json:"cache_at"`
	UserScreenTimeList []*UserScreenTimeStruct `json:"user_screen_time_list"`
}

// UserScreenTimeStruct структура объекта
type UserScreenTimeStruct struct {
	UserID        int64  `json:"user_id"`
	ScreenTime    int64  `json:"screen_time"`
	LocalOnlineAt string `json:"local_online_at"`
}

// ScreentimeCollector сборщик экранного времени для Pivot
// работает как прослойка между агрегаторами и Pivot
// экранное время не отправляется непосредственно из экземпляра ScreentimeProvider
type ScreentimeCollector struct {
	sourceList map[string]ScreentimeProvider
	mx         sync.RWMutex
}

// MakeScreentimeCollector инициализирует новый сборщик экранного времени
func MakeScreentimeCollector(_ *Isolation.Isolation) *ScreentimeCollector {

	lc := &ScreentimeCollector{
		sourceList: map[string]ScreentimeProvider{},
		mx:         sync.RWMutex{},
	}

	return lc
}

// RegisterSource регистрирует экземпляр
// после экземпляра регистрации экранное время будет отправляться в pivot
func (lc *ScreentimeCollector) RegisterSource(key string, source ScreentimeProvider) func() {

	lc.mx.Lock()
	defer lc.mx.Unlock()

	if _, exists := lc.sourceList[key]; exists {
		panic(fmt.Sprintf("provider with key %s already registered", key))
	}

	lc.sourceList[key] = source

	return func() {

		lc.unregisterSource(key)
	}
}

// unregisterSource удалят экземпляр
// после удаления экранное время из экземпляра собираться не будет
func (lc *ScreentimeCollector) unregisterSource(key string) {

	lc.mx.Lock()
	defer lc.mx.Unlock()

	delete(lc.sourceList, key)
}

// собирает все экраное время из экземпляров
func (lc *ScreentimeCollector) collect() []*UserScreenTimeListStruct {

	var output []*UserScreenTimeListStruct

	lc.mx.RLock()

	// собираем экранное время со всех источников
	for _, source := range lc.sourceList {
		output = append(output, source.CollectScreenTime()...)
	}

	lc.mx.RUnlock()

	return output
}

// CollectScreenTime возвращает собранное экранное время одним объектом
func CollectScreenTime(isolation *Isolation.Isolation) ([]*UserScreenTimeListStruct, error) {

	mCollector := LeaseScreentimeCollector(isolation)
	if mCollector == nil {
		return make([]*UserScreenTimeListStruct, 0), fmt.Errorf("isolation doesn't have screen time instance")
	}

	return mCollector.collect(), nil
}

// --------------------------------------------
// region количество действий
// --------------------------------------------

// ActionProvider интерфейс для всего,
// что хочет слать количество действий в pivot
type ActionProvider interface {
	CollectUserAction() []*UserActionListStruct
	CollectConversationAction() []*ConversationActionListStruct
}

// UserActionListStruct структура объекта
type UserActionListStruct struct {
	SpaceId  int64             `json:"space_id"`
	ActionAt int64             `json:"action_at"`
	UserList []*UserListStruct `json:"user_list"`
}

// UserListStruct структура объекта
type UserListStruct struct {
	UserId     int64            `json:"user_id"`
	ActionList ActionListStruct `json:"action_list"`
}

// ConversationActionListStruct структура объекта
type ConversationActionListStruct struct {
	SpaceId          int64                     `json:"space_id"`
	ActionAt         int64                     `json:"action_at"`
	ConversationList []*ConversationListStruct `json:"conversation_list"`
}

// ConversationListStruct структура объекта
type ConversationListStruct struct {
	ConversationMap string           `json:"conversation_map"`
	ActionList      ActionListStruct `json:"action_list"`
}

// ActionListStruct структура объекта
type ActionListStruct struct {
	GroupsCreated              int64 `json:"groups_created"`
	ConversationsRead          int64 `json:"conversations_read"`
	ConversationMessagesSent   int64 `json:"conversation_messages_sent"`
	ConversationReactionsAdded int64 `json:"conversation_reactions_added"`
	ConversationRemindsCreated int64 `json:"conversation_reminds_created"`
	Calls                      int64 `json:"calls"`
	ThreadsCreated             int64 `json:"threads_created"`
	ThreadsRead                int64 `json:"threads_read"`
	ThreadMessagesSent         int64 `json:"thread_messages_sent"`
	ThreadReactionsAdded       int64 `json:"thread_reactions_added"`
	ThreadRemindsCreated       int64 `json:"thread_reminds_created"`
}

// ActionCollector сборщик количества действий для Pivot
// работает как прослойка между агрегаторами и Pivot
// количество действий не отправляется непосредственно из экземпляра ActionProvider
type ActionCollector struct {
	sourceList map[string]ActionProvider
	mx         sync.RWMutex
}

// MakeActionCollector инициализирует новый сборщик количества действий
func MakeActionCollector(_ *Isolation.Isolation) *ActionCollector {

	lc := &ActionCollector{
		sourceList: map[string]ActionProvider{},
		mx:         sync.RWMutex{},
	}

	return lc
}

// RegisterSource регистрирует экземпляр
// после экземпляра регистрации количество действий будет отправляться в pivot
func (lc *ActionCollector) RegisterSource(key string, source ActionProvider) func() {

	lc.mx.Lock()
	defer lc.mx.Unlock()

	if _, exists := lc.sourceList[key]; exists {
		panic(fmt.Sprintf("provider with key %s already registered", key))
	}

	lc.sourceList[key] = source

	return func() {

		lc.unregisterSource(key)
	}
}

// unregisterSource удалят экземпляр
// после удаления количество действий из экземпляра собираться не будет
func (lc *ActionCollector) unregisterSource(key string) {

	lc.mx.Lock()
	defer lc.mx.Unlock()

	delete(lc.sourceList, key)
}

// собирает все действия из экземпляров
func (lc *ActionCollector) collectUserAction() []*UserActionListStruct {

	var output []*UserActionListStruct

	lc.mx.RLock()

	// собираем действия со всех источников
	for _, source := range lc.sourceList {
		output = append(output, source.CollectUserAction()...)
	}

	lc.mx.RUnlock()

	return output
}

// CollectUserAction возвращает собранное количество действий одним объектом
func CollectUserAction(isolation *Isolation.Isolation) ([]*UserActionListStruct, error) {

	mCollector := LeaseActionCollector(isolation)
	if mCollector == nil {
		return make([]*UserActionListStruct, 0), fmt.Errorf("isolation doesn't have action instance")
	}

	return mCollector.collectUserAction(), nil
}

// собирает все действия из экземпляров
func (lc *ActionCollector) collectConversationAction() []*ConversationActionListStruct {

	var output []*ConversationActionListStruct

	lc.mx.RLock()

	// собираем действия со всех источников
	for _, source := range lc.sourceList {
		output = append(output, source.CollectConversationAction()...)
	}

	lc.mx.RUnlock()

	return output
}

// CollectConversationAction возвращает собранное количество действий одним объектом
func CollectConversationAction(isolation *Isolation.Isolation) ([]*ConversationActionListStruct, error) {

	mCollector := LeaseActionCollector(isolation)
	if mCollector == nil {
		return make([]*ConversationActionListStruct, 0), fmt.Errorf("isolation doesn't have action instance")
	}

	return mCollector.collectConversationAction(), nil
}

// --------------------------------------------
// region время ответа на сообщения
// --------------------------------------------

// UserAnswerTimeProvider интерфейс для всего,
// что хочет слать время ответа на сообщения в pivot
type UserAnswerTimeProvider interface {
	CollectUserAnswerTime() []*UserAnswerTimeConversationListStruct
}

// UserAnswerTimeConversationListStruct структура объекта
type UserAnswerTimeConversationListStruct struct {
	Min15StartAt       int64                       `json:"min15_start_at"`
	SpaceId            int64                       `json:"space_id"`
	ConversationKey    string                      `json:"conversation_key"`
	UserAnswerTimeList []*UserAnswerTimeListStruct `json:"user_answer_time_list"`
}

// UserAnswerTimeListStruct структура объекта
type UserAnswerTimeListStruct struct {
	UserID     int64 `json:"user_id"`
	AnswerTime int64 `json:"answer_time"`
	AnsweredAt int64 `json:"answered_at"`
}

// UserAnswerTimeCollector сборщик времени ответа на сообщения для Pivot
// работает как прослойка между агрегаторами и Pivot
// экранное время не отправляется непосредственно из экземпляра UserAnswerTimeProvider
type UserAnswerTimeCollector struct {
	sourceList map[string]UserAnswerTimeProvider
	mx         sync.RWMutex
}

// MakeUserAnswerTimeCollector инициализирует новый сборщик времени ответа на сообщения
func MakeUserAnswerTimeCollector(_ *Isolation.Isolation) *UserAnswerTimeCollector {

	lc := &UserAnswerTimeCollector{
		sourceList: map[string]UserAnswerTimeProvider{},
		mx:         sync.RWMutex{},
	}

	return lc
}

// RegisterSource регистрирует экземпляр
// после экземпляра регистрации время ответа на сообщения будет отправляться в pivot
func (lc *UserAnswerTimeCollector) RegisterSource(key string, source UserAnswerTimeProvider) func() {

	lc.mx.Lock()
	defer lc.mx.Unlock()

	if _, exists := lc.sourceList[key]; exists {
		panic(fmt.Sprintf("provider with key %s already registered", key))
	}

	lc.sourceList[key] = source

	return func() {

		lc.unregisterSource(key)
	}
}

// unregisterSource удалят экземпляр
// после удаления время ответа на сообщения из экземпляра собираться не будет
func (lc *UserAnswerTimeCollector) unregisterSource(key string) {

	lc.mx.Lock()
	defer lc.mx.Unlock()

	delete(lc.sourceList, key)
}

// собирает все ответы на сообщения из экземпляров
func (lc *UserAnswerTimeCollector) collect() []*UserAnswerTimeConversationListStruct {

	var output []*UserAnswerTimeConversationListStruct

	lc.mx.RLock()

	// собираем время ответа на сообщения со всех источников
	for _, source := range lc.sourceList {
		output = append(output, source.CollectUserAnswerTime()...)
	}

	lc.mx.RUnlock()

	return output
}

// CollectUserAnswerTime возвращает собранное время ответа на сообщения одним объектом
func CollectUserAnswerTime(isolation *Isolation.Isolation) ([]*UserAnswerTimeConversationListStruct, error) {

	mCollector := LeaseUserAnswerTimeCollector(isolation)
	if mCollector == nil {
		return make([]*UserAnswerTimeConversationListStruct, 0), fmt.Errorf("isolation doesn't have user answer time instance")
	}

	return mCollector.collect(), nil
}
