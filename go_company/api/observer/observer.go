package observer

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/includes/methods/methods_rating"
	Isolation "go_company/api/includes/type/isolation"
	timer_worker "go_company/api/includes/type/timer/worker"
	readMessageObserver "go_company/api/observer/read_message"
	"time"
)

const (

	// интервалы с которыми работают различные воркера обсервера
	timerWorkerInterval           = time.Second
	ratingWorkerInterval          = time.Minute * 5
	reactionWorkerInterval        = time.Millisecond * 150
	readParticipantWorkerInterval = time.Second
)

// WorkCompanyObserver Work метод для выполнения работы в компаниях через время
func WorkCompanyObserver(ctx context.Context, companyIsolation *Isolation.Isolation) {

	go doWorkTimer(ctx, companyIsolation)
	go goWorkDumpRating(ctx, companyIsolation)
	go goWorkReactionHandler(ctx, companyIsolation)
	go goWorkReadMessageHandler(ctx, companyIsolation)
}

// каждую итерацию для таймера
func doWorkTimer(ctx context.Context, companyIsolation *Isolation.Isolation) {

	for {

		// используем select для выхода по истечении времени жизни контекста
		select {

		case <-time.After(timerWorkerInterval):

			// пробуем выполнить задачи из кэша, которые с отложенным выполнением
			taskList := companyIsolation.TimerStore.GetNeedWorkTask()

			for _, task := range taskList {

				timer_worker.DoWorkTask(companyIsolation, task)
			}

		case <-ctx.Done():

			log.Infof("Закрыли обсервер таймера для компании %d", companyIsolation.GetCompanyId())
			return

		}
	}
}

// запускаем observer который дампим рейтинг
func goWorkDumpRating(ctx context.Context, companyIsolation *Isolation.Isolation) {

	for {

		// используем select для выхода по истечении времени жизни контекста
		select {

		case <-time.After(ratingWorkerInterval):

			year := time.Now().Year()
			day := functions.GetDaysCountByYear(year)

			err := methods_rating.DumpToActiveTable(companyIsolation.Context, companyIsolation.MainStorage, companyIsolation.RatingStore, companyIsolation.CompanyDataConn,
				companyIsolation.GetGlobalIsolation(), year, day)
			if err != nil {
				log.Errorf("%v", err)
			}

		case <-ctx.Done():

			log.Infof("Закрыли обсервер рейтинга для компании %d", companyIsolation.GetCompanyId())
			return

		}
	}
}

// запускаем observer который разруливает кэш go рутины
func goWorkReactionHandler(ctx context.Context, companyIsolation *Isolation.Isolation) {

	for {

		// используем select для выхода по истечении времени жизни контекста
		select {

		case <-time.After(reactionWorkerInterval):

			// получаем кэш
			cache := companyIsolation.ReactionStore.GetAndClearCache()

			// спим
			if len(cache) < 1 {
				continue
			}

			// работаем с кешом иcontroller/reaction.go:81 потом его чистим и меняем
			handleReactionCache(companyIsolation, cache)

		case <-ctx.Done():

			log.Infof("Закрыли обсервер реакций для компании %d", companyIsolation.GetCompanyId())
			return

		}
	}
}

// запускаем observer который разруливает кэш go рутины по просмотренным сообщениям
func goWorkReadMessageHandler(ctx context.Context, companyIsolation *Isolation.Isolation) {

	for {

		// используем select для выхода по истечении времени жизни контекста
		select {

		case <-time.After(readParticipantWorkerInterval):

			// получаем кэш
			cache := companyIsolation.ReadMessageStore.GetAndClearCache()

			// спим
			if len(cache) < 1 {
				continue
			}

			// работаем с кешом
			err := readMessageObserver.HandleReadParticipants(companyIsolation, cache)

			if err != nil {
				log.Errorf("Произошла ошибка read_message: %v", err)
			}
		case <-ctx.Done():

			log.Infof("Закрыли обсервер просмотренных сообщений для компании %d", companyIsolation.GetCompanyId())
			return

		}
	}
}
