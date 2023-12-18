package global_observer

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
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

		isolation, companyCtx := companyContextList.StartEnv(
			ctx, companyId, config, globalIsolation.GetConfig().CapacityLimit, globalIsolation.GetShardingConfig().Mysql.MaxConnections, globalIsolation,
		)

		if isolation == nil {
			continue
		}

		// запускаем observer по ней
		observer.WorkCompanyObserver(companyCtx, isolation)
	}
}
