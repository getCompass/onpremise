package Isolation

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/conf"
	analyticsWs "go_sender/api/includes/type/analytics/ws"
	"go_sender/api/includes/type/db/company_data"
	GlobalIsolation "go_sender/api/includes/type/global_isolation"
	"go_sender/api/includes/type/pusher"
	"go_sender/api/includes/type/thread"
	"go_sender/api/includes/type/token"
	"go_sender/api/includes/type/user_notification"
	"go_sender/api/includes/type/ws"
	"sync"
)

type CompanyEnvList struct {
	mutex   sync.Mutex
	EnvList map[int64]*Isolation
}

const VacantCompanyStatus = 1
const ActiveCompanyStatus = 2

// MakeCompanyEnvList создаем env
func MakeCompanyEnvList() *CompanyEnvList {

	return &CompanyEnvList{
		mutex:   sync.Mutex{},
		EnvList: make(map[int64]*Isolation),
	}
}

// инициализирует новую среду для компании
// если сервис больше не может вмещать в себя компании, возвращает ошибку
func (companyEnv *CompanyEnvList) StartEnv(ctx context.Context, companyId int64, companyConfig *conf.CompanyConfigStruct, capacityLimit int, globalIsolation *GlobalIsolation.GlobalIsolation, companyDataConn *company_data.DbConn, pusherConn *pusher.Conn) (*Isolation, context.Context) {

	companyEnv.mutex.Lock()
	isolation, exists := companyEnv.EnvList[companyId]
	companyEnv.mutex.Unlock()

	if exists {

		if companyConfig.Status != ActiveCompanyStatus && companyConfig.Status != VacantCompanyStatus {

			log.Infof("Компания не активная останавливаем %d", companyId)
			companyEnv.StopEnv(companyId)
		}

		return nil, nil
	}

	if len(companyEnv.EnvList) >= capacityLimit {

		log.Errorf("service capacity limit reached")
		return nil, nil
	}

	companyContext, cancel := context.WithCancel(ctx)

	// генерируем новый контекст исполнения для сервиса
	isolation, err := MakeIsolation(companyContext, companyId, companyConfig, cancel, globalIsolation, companyDataConn, pusherConn)
	if err != nil {
		return nil, nil
	}

	// добавляешь в хранилище
	companyEnv.mutex.Lock()
	companyEnv.EnvList[companyId] = isolation
	companyEnv.mutex.Unlock()

	log.Infof("added isolation for company %d", companyId)
	return isolation, companyContext
}

// останавливает среду исполнения компании
// если сервис не обслуживает компанию, то вернет ошибку
func (companyEnv *CompanyEnvList) StopEnv(companyId int64) {

	companyEnv.mutex.Lock()
	isolation, exists := companyEnv.EnvList[companyId]
	delete(companyEnv.EnvList, companyId)
	companyEnv.mutex.Unlock()
	if !exists {
		return
	}

	// удаляем все observer
	isolation.Cancel()

	if isolation.CompanyDataConn != nil {

		// отключаемся от БД
		err := isolation.CompanyDataConn.Conn.Close()
		if err != nil {
			log.Error(err.Error())
		}
	}

	log.Infof("company %d not served", companyId)

}

// GetEnv возвращает среду исполнения для компании
func (companyEnv *CompanyEnvList) GetEnv(companyId int64) *Isolation {

	companyEnv.mutex.Lock()
	isolation, exists := companyEnv.EnvList[companyId]
	companyEnv.mutex.Unlock()

	if exists {
		return isolation
	}

	log.Debug(fmt.Sprintf("isolation for company %d not found", companyId))
	return nil
}

// GetCompanyIdList возвращает список всех обслуживаемых компаний
func (companyEnv *CompanyEnvList) GetCompanyIdList() map[int64]struct{} {

	// подготовим слайс с выделением памяти
	companyIdList := make(map[int64]struct{})

	companyEnv.mutex.Lock()

	//пройдемся по всем компаниям и вернем их ID
	for k := range companyEnv.EnvList {
		companyIdList[k] = struct{}{}
	}

	companyEnv.mutex.Unlock()

	return companyIdList
}

/** Пакет описывает сущность изоляции исполнения внутри модуля
  Изоляция может быть связана с компанией или быть глобальной для сервиса **/

// структура контекста компании
type Isolation struct {
	companyId               int64
	companyConfig           *conf.CompanyConfigStruct
	Cancel                  context.CancelFunc
	Context                 context.Context
	SocketKeyToPivot        string
	globalIsolation         *GlobalIsolation.GlobalIsolation
	CompanyDataConn         *company_data.DbConn
	UserConnectionStore     *ws.UserConnectionStore
	AnalyticStore           *ws.AnalyticStore
	AnalyticWsStore         *analyticsWs.AnalyticStore
	PusherConn              *pusher.Conn
	ThreadUcStore           *thread.UserConnectionStore
	ThreadKeyStore          *thread.KeyStore
	ThreadAStore            *thread.AuthStore
	UserNotificationStorage *user_notification.UserNotificationStorage
	NotificationSubStorage  *user_notification.SubStorage
	TokenStore              *token.Store
}

// возвращает ид компании для изоляции
func (i *Isolation) GetCompanyId() int64 {

	return i.companyId
}

// возвращает ид компании для изоляции
func (i *Isolation) GetGlobalIsolation() *GlobalIsolation.GlobalIsolation {

	return i.globalIsolation
}

// возвращает ид компании для изоляции
func (i *Isolation) GetCompanyConfig() *conf.CompanyConfigStruct {

	return i.companyConfig
}

// MakeIsolation возвращает новую локальную изоляцию сервиса
func MakeIsolation(ctx context.Context, companyId int64, companyConfig *conf.CompanyConfigStruct, cancel context.CancelFunc, globalIsolation *GlobalIsolation.GlobalIsolation, companyDataConn *company_data.DbConn, pusherConn *pusher.Conn) (*Isolation, error) {

	isolation := Isolation{
		companyId:               companyId,
		companyConfig:           companyConfig,
		Cancel:                  cancel,
		Context:                 ctx,
		globalIsolation:         globalIsolation,
		CompanyDataConn:         companyDataConn,
		AnalyticStore:           ws.MakeAnalyticStore(),
		AnalyticWsStore:         analyticsWs.MakeAnalyticWsStore(),
		PusherConn:              pusherConn,
		ThreadUcStore:           thread.MakeThreadUserConnectionStore(),
		ThreadAStore:            thread.MakeThreadAuthStore(),
		ThreadKeyStore:          thread.MakeThreadKeyStore(),
		UserNotificationStorage: user_notification.MakeUserNotificationStorage(),
		NotificationSubStorage:  user_notification.MakeSubStorage(),
		TokenStore:              token.MakeStore(),
	}

	if companyId == 0 {
		isolation.UserConnectionStore = ws.MakeUserConnectionStore(globalIsolation.BalancerConn)
	} else {
		isolation.UserConnectionStore = ws.MakeUserConnectionStore(globalIsolation.EmptyBalancerConn)
	}

	return &isolation, nil
}
