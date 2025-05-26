package global_observer

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/conf"
	companyContext "go_company/api/includes/type/company_context"
	GlobalIsolation "go_company/api/includes/type/global_isolation"
	Isolation "go_company/api/includes/type/isolation"
	"go_company/api/observer"
	"time"
)

// WorkGlobalObserver Work метод для выполнения работы через время
func WorkGlobalObserver(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	// делаем сначало синхронно
	updateCompanyList(ctx, globalIsolation, companyContextList)

	go func() {

		for {

			// используем select для выхода по истечении времени жизни контекста
			select {

			case <-time.After(time.Millisecond):

				updateCompanyList(ctx, globalIsolation, companyContextList)

			case <-ctx.Done():

				log.Infof("Закрыли глобальный обсервер")
				return

			}
		}
	}()
}

// обновляем список компаний
func updateCompanyList(ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList) {

	configList, err := companyContext.UpdateWorldConfig(globalIsolation)
	if err != nil {
		log.Error(err.Error())
	}

	for companyId, config := range configList {

		startCompanyEnv(companyId, config, ctx, globalIsolation, companyContextList, 0)
	}
}

// стартуем новую среду для компании
func startCompanyEnv(companyId int64, companyConfig *conf.CompanyConfigStruct, ctx context.Context, globalIsolation *GlobalIsolation.GlobalIsolation, companyContextList *Isolation.CompanyEnvList, errorCount int) {

	isolation, companyCtx, err := companyContextList.StartEnv(
		ctx, companyId, companyConfig, globalIsolation.GetConfig().CapacityLimit, globalIsolation.GetShardingConfig().Mysql.MaxConnections, globalIsolation,
	)

	if isolation != nil {

		// запускаем observer по ней
		observer.WorkCompanyObserver(companyCtx, isolation)
	} else {

		if err == nil {
			return
		}

		if !globalIsolation.GetConfig().IsIsolationCreateRepeat {
			return
		}

		// в случае ошибки инкрементим количество ошибок - значит что-то не удалось
		errorCount++

		if errorCount > 5 {
			return
		}

		// пробуем получить ещё раз
		time.Sleep(time.Second)
		startCompanyEnv(companyId, companyConfig, ctx, globalIsolation, companyContextList, errorCount)
	}
}
