package EventData

import (
	"encoding/json"
	Event "go_event/api/includes/type/event"
)

// SystemEventBrokerEventList список событий, чтобы не использовать строки при создании подписок
var SystemEventBrokerEventList = struct {
	EventGeneratorAdded   string
	EventGeneratorRemoved string
	TaskGeneratorAdded    string
	TaskGeneratorRemoved  string
}{
	EventGeneratorAdded:   "system_event_broker.event_generator_added",   // рейтинг рабочих часов за период времени
	EventGeneratorRemoved: "system_event_broker.event_generator_removed", // всего действий за период времени
	TaskGeneratorAdded:    "system_event_broker.task_generator_added",    // всего действий с карточкой сотрудника за период времени
	TaskGeneratorRemoved:  "system_event_broker.task_generator_removed",  // всего действий с карточкой сотрудника за период времени
}

// SystemEventBrokerEventGeneratorAdded структура для события отправки рейтинга
type SystemEventBrokerEventGeneratorAdded struct {
	EventName        string                 `json:"name"`              // имя генератора
	Period           int                    `json:"period"`            // с каким периодом он выбрасывает события
	SubscriptionItem Event.SubscriptionItem `json:"subscription_item"` // предмет подписки
	EventData        json.RawMessage        `json:"event_data"`        // какие данные генератор добавлять в событие
}

// SystemEventBrokerEventGeneratorRemoved структура для события отправки рейтинга
type SystemEventBrokerEventGeneratorRemoved struct {
	EventName string `json:"name"` // имя генератора
}

// SystemEventBrokerTaskGeneratorAdded структура для события отправки рейтинга
type SystemEventBrokerTaskGeneratorAdded struct {
	TaskName         string                 `json:"name"`              // имя генератора
	TaskType         int                    `json:"task_type"`         // тип задачи
	Period           int                    `json:"period"`            // с каким периодом он выбрасывает события
	SubscriptionItem Event.SubscriptionItem `json:"subscription_item"` // предмет подписки
	TaskData         json.RawMessage        `json:"task_data"`         // какие данные генератор добавлять в событие
	Module           string                 `json:"module"`            // в какой модуль пушить задачи
	Group            string                 `json:"group"`             // группа очереди для задач
}

// SystemEventBrokerTaskGeneratorRemoved структура для события отправки рейтинга
type SystemEventBrokerTaskGeneratorRemoved struct {
	TaskName string `json:"name"` // имя генератора
}
