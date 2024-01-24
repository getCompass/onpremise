package EventBroker

/** Пакет брокера системны событий, отвечает за подписку на события и доставку событий
  Файл работы с внешним хранилищем всех подписок (БД)
*/

import (
	"context"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	SystemEvent "go_event/api/includes/type/event"
	"go_event/api/system/sharding"
)

// получить запись по имени подписчика
func getStoredSubscription(ctx context.Context, subscriber string) (SystemEvent.SubscriptionData, error) {

	// получаем имя базы и таблицы из конфига
	dbName := conf.GetEventConf().SubscriberStorage.Db
	tableName := conf.GetEventConf().SubscriberStorage.Table

	// составляем и делаем запрос
	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `subscriber` = ? LIMIT ?", tableName)
	subscriberList, err := sharding.Mysql(dbName).GetAll(ctx, query, subscriber, 1)

	if err != nil {
		return SystemEvent.SubscriptionData{}, err
	}

	if len(subscriberList) == 0 {

		// если в бд нет, то возвращаем пустышку
		return SystemEvent.SubscriptionData{
			Subscriber:       subscriber,
			SubscriptionList: []SystemEvent.SubscriptionItem{},
		}, nil
	}

	// конвертируем данные в нужную структуру
	subscription, err := rowToStruct(subscriberList[0])

	if err != nil {
		return SystemEvent.SubscriptionData{}, err
	}

	return subscription, nil
}

// получить всех подписчиков
func getAllStoredSubscriptions(ctx context.Context) []SystemEvent.SubscriptionData {

	// получаем имя базы и таблицы из конфига
	dbName := conf.GetEventConf().SubscriberStorage.Db
	tableName := conf.GetEventConf().SubscriberStorage.Table

	// составляем и делаем запрос
	query := fmt.Sprintf("SELECT * FROM `%s` LIMIT ?", tableName)
	rawSubscriberList, _ := sharding.Mysql(dbName).GetAll(ctx, query, 1000)

	var subscriberList []SystemEvent.SubscriptionData
	subscriber := SystemEvent.SubscriptionData{}

	// проходимся по сырым данным из базы, конвертируем их в структуры и добавляем в слайс
	for _, rawSubscriber := range rawSubscriberList {

		subscriber, _ = rowToStruct(rawSubscriber)
		subscriberList = append(subscriberList, subscriber)
	}

	return subscriberList
}

// конвертируем сырые данные в нужную структуру
func rowToStruct(row map[string]string) (SystemEvent.SubscriptionData, error) {

	var subscriptionList []SystemEvent.SubscriptionItem

	// пытаемся сконвертировать список подписок в нужную структуру
	err := go_base_frame.Json.Unmarshal([]byte(row["subscription_list"]), &subscriptionList)

	if err != nil {
		return SystemEvent.SubscriptionData{}, errors.New("invalid subsciption json data structure")
	}

	// составляем структуру подписчика
	subscription := SystemEvent.SubscriptionData{
		Subscriber:       row["subscriber"],
		SubscriptionList: subscriptionList,
	}

	return subscription, nil
}

// UpdateStoredSubscription обновляем запись
func UpdateStoredSubscription(ctx context.Context, subscriber string, subscriptionList []SystemEvent.SubscriptionItem, updatedAt int64) error {

	// получаем имя базы и таблицы из конфига
	dbName := conf.GetEventConf().SubscriberStorage.Db
	tableName := conf.GetEventConf().SubscriberStorage.Table

	// конвертируем данные в json
	jsonSubscriptionList, _ := go_base_frame.Json.Marshal(subscriptionList)

	// подготавливаем данные для вставки
	dataToInsert := map[string]interface{}{
		"subscriber":        subscriber,
		"subscription_list": jsonSubscriptionList,
		"updated_at":        updatedAt,
	}

	// вставляем или обновляем данные
	err := sharding.Mysql(dbName).InsertOrUpdate(ctx, tableName, dataToInsert)

	if err != nil {

		log.Errorf("Error on save subscriber to storage: %s", err)
		return err
	}

	return nil
}

// вызывается на старте для создания дефолтных подписок
func getDefault() []SystemEvent.SubscriptionData {

	var subscriptionList []SystemEvent.SubscriptionData
	err := go_base_frame.Json.Unmarshal(conf.GetEventConf().SubscriptionList, &subscriptionList)

	if err != nil {

		log.Error(err.Error())
		return subscriptionList
	}

	return subscriptionList
}
