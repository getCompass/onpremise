package observer

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_activity/api/includes/type/activitycache"
	"go_activity/api/includes/type/db/pivot_user"
	"sync/atomic"
	"time"
)

var (
	is1HourWorker   atomic.Value
	is5SecondWorker atomic.Value
)

// Work метод для выполнения работы через время
func Work(ctx context.Context) {

	go doWork1Hour()

	go doWork5Second(ctx)
}

// каждый час
func doWork1Hour() {

	if is1HourWorker.Load() != nil && is1HourWorker.Load().(bool) {
		return
	}
	is1HourWorker.Store(true)

	for {

		time.Sleep(time.Hour)
	}
}

func doWork5Second(ctx context.Context) {

	// проверяем, не запущен ли уже воркер
	if is5SecondWorker.Load() != nil && is5SecondWorker.Load().(bool) {
		return
	}
	is5SecondWorker.Store(true)
	defer is5SecondWorker.Store(false) // гарантируем разблокировку

	for {

		if ctx.Err() != nil {
			break
		}

		// ждем 5 секунд пока кеш наполнится
		time.Sleep(time.Second * 5)

		// получаем все что есть в переменную
		activities := activitycache.GetAllActivity()
		if len(activities) <= 0 {
			continue
		}

		// чистим кеш чтобы сразу наполнялись только новые записи
		activitycache.ResetCache()

		// создаём временное хранилище для максимальных значений
		aggregatedActivities := make(map[int64]int64)

		// агрегируем максимальные значения LastPingWsAt для каждого userId
		for _, a := range activities {
			userID := a[0].(int64)
			lastPing := a[2].(int64)

			// обновляем значение, если оно больше текущего
			if currentMax, exists := aggregatedActivities[userID]; !exists || lastPing > currentMax {
				aggregatedActivities[userID] = lastPing
			}
		}

		// преобразовываем агрегированные данные к нужной структуре
		userActivities := make([]pivot_user.UserAddedActivity, 0, len(aggregatedActivities))
		for userID, lastPing := range aggregatedActivities {
			userActivities = append(userActivities, pivot_user.UserAddedActivity{
				UserId:       userID,
				LastPingWsAt: lastPing,
			})
		}

		// выполняем массовый insert/update в БД онлайна по аккаунту
		err := pivot_user.InsertOrUpdateUserActivities(ctx, userActivities)
		if err != nil {
			log.Errorf("Ошибка при обработке активностей: %v", err)
		}

		// создаём временное хранилище для максимальных значений по сессиям
		sessionAggregatedActivities := make(map[string]pivot_user.UserSessionActivity)

		// агрегируем максимальные значения LastPingWsAt для каждой комбинации userId + sessionUniq
		for _, a := range activities {
			userID := a[0].(int64)
			sessionUniq := a[1].(string)
			lastPing := a[2].(int64)

			// формируем ключ для уникальности userId + sessionUniq
			key := fmt.Sprintf("%d:%s", userID, sessionUniq)

			// обновляем значение, если оно больше текущего
			if currentActivity, exists := sessionAggregatedActivities[key]; !exists || lastPing > currentActivity.LastPingWsAt {
				sessionAggregatedActivities[key] = pivot_user.UserSessionActivity{
					UserId:       userID,
					SessionUniq:  sessionUniq,
					LastPingWsAt: lastPing,
				}
			}
		}

		// преобразовываем агрегированные данные к нужной структуре
		sessionActivities := make([]pivot_user.UserSessionActivity, 0, len(sessionAggregatedActivities))
		for _, activity := range sessionAggregatedActivities {
			sessionActivities = append(sessionActivities, activity)
		}

		// выполняем массовый update в БД по онлайну сессий
		err = pivot_user.UpdateUserSessionActivity(ctx, sessionActivities)
		if err != nil {
			log.Errorf("Ошибка при обработке активностей сессий: %v", err)
		}
	}
}
