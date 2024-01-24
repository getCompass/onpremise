package EventBroker

import (
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
)

// хранилище слушателей для события
type listenerStore struct {
	eventType string       // какое событие слушается
	store     []*listener  // список слушателей события
	mx        sync.RWMutex // мьютекс
}

// создает новое хранилище слушателей
func makeListenerStore(eventType string, listenerList ...*listener) *listenerStore {

	store := listenerStore{
		eventType: eventType,
		store:     listenerList,
		mx:        sync.RWMutex{},
	}

	return &store
}

// добавляет нового слушателя в список слушателей события
func (lStore *listenerStore) attach(listener *listener) {

	lStore.mx.Lock()
	lStore.store = append(lStore.store, listener)

	log.Success(fmt.Sprintf("listener %s:%s successfully attached to %s event", listener.subscriber, listener.group, lStore.eventType))

	lStore.mx.Unlock()
}

// удаляет слушателя из списка слушателей
func (lStore *listenerStore) detach(listener *listener) {

	lStore.mx.Lock()

	for k, existingListener := range lStore.store {

		// слушатель считается одинаковым, если совпадает группа и уникальный идентификатор слушателя
		if existingListener.isEqual(listener) {

			lStore.store = append(lStore.store[:k], lStore.store[k+1:]...)
			log.Warning(fmt.Sprintf("listener %s:%s successfully detached from %s event", existingListener.subscriber, existingListener.group, lStore.eventType))

			break
		}
	}

	lStore.mx.Unlock()
}

// возвращает наличие слушателей в хранилище
func (lStore *listenerStore) isEmpty() bool {

	return len(lStore.store) == 0
}

// возвращает хранилище для события
func (lStore *listenerStore) getListeners() []*listener {

	lStore.mx.RLock()
	defer lStore.mx.RUnlock()

	return lStore.store
}

// тип — хранилище всех слушателей для всех событий
type listenerListStore struct {
	mx    sync.RWMutex              // мьютекс
	store map[string]*listenerStore // хранилище список слушателей
}

// добавляет хранилище для события
func (llStore *listenerListStore) attach(eventType string) {

	llStore.mx.Lock()
	defer llStore.mx.Unlock()

	// создаем хранилище, если его не существует
	if _, isExist := llStore.store[eventType]; !isExist {

		llStore.store[eventType] = makeListenerStore(eventType)
	}
}

// удаляет хранилище для события, если хранилище непустое, то его нельзя удалить
func (llStore *listenerListStore) detach(eventType string) {

	llStore.mx.Lock()
	defer llStore.mx.Unlock()

	// создаем хранилище, если его не существует
	if _, isExist := llStore.store[eventType]; !isExist {
		return
	}

	// удаляем список слушателей
	delete(llStore.store, eventType)
}

// GetEventStore возвращает хранилище события
func (llStore *listenerListStore) GetEventStore(eventType string) (*listenerStore, error) {

	llStore.mx.RLock()
	defer llStore.mx.RUnlock()

	if _, isExist := llStore.store[eventType]; !isExist {
		return nil, errors.New(fmt.Sprintf("there is not llStore for event %s", eventType))
	}

	return llStore.store[eventType], nil
}

// GetEventListeners возвращает слушателей события
func (llStore *listenerListStore) GetEventListeners(eventType string) ([]*listener, error) {

	lStore, err := llStore.GetEventStore(eventType)

	if err != nil {
		return nil, err
	}

	return lStore.getListeners(), nil
}

// AttachListenerToEvent добавляет подписчика события
func (llStore *listenerListStore) AttachListenerToEvent(eventType string, listener *listener) error {

	lStore, err := llStore.GetEventStore(eventType)

	if err != nil {

		llStore.attach(eventType)
		lStore, err = llStore.GetEventStore(eventType)

		if err != nil {
			return err
		}
	}

	// удаляем существующий, если есть и цепляем новый
	lStore.detach(listener)
	lStore.attach(listener)

	return nil
}

// DetachListenerFromEvent удаляет подписчика события
func (llStore *listenerListStore) DetachListenerFromEvent(eventType string, listener *listener) error {

	// проверяем существование хранилища
	lStore, err := llStore.GetEventStore(eventType)

	// хранилища не существует, считаем, что все было успешно
	if err != nil {
		return nil
	}

	// удаляем слушателя из списка слушателей
	lStore.detach(listener)

	// если список слушателей пуст, то удаляем его
	if lStore.isEmpty() {
		llStore.detach(eventType)
	}

	return nil
}

// хранилище всех слушателей для всех событий
// работает как глобальное хранилище, использует как источник слушателей почти для все изоляций
var globalListenerListStore = listenerListStore{
	mx:    sync.RWMutex{},              // мьютекс
	store: map[string]*listenerStore{}, // хранилище список слушателей
}

// GetListenerListStore возвращает хранилище для работы
func GetListenerListStore() *listenerListStore {
	return &globalListenerListStore
}
