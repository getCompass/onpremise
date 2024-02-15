package Isolation

import (
	"context"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/conf"
	"go_company_cache/api/includes/type/config"
	"go_company_cache/api/includes/type/db/company_data"
	GlobalIsolation "go_company_cache/api/includes/type/global_isolation"
	"go_company_cache/api/includes/type/member"
	"go_company_cache/api/includes/type/session"
	"sync"
)

type CompanyEnvList struct {
	mutex   sync.RWMutex
	envList map[int64]*Isolation
}

const vacantCompanyStatus = 1
const activeCompanyStatus = 2

// MakeCompanyEnvList создаем env
func MakeCompanyEnvList() *CompanyEnvList {

	return &CompanyEnvList{
		mutex:   sync.RWMutex{},
		envList: make(map[int64]*Isolation),
	}
}

// инициализирует новую среду для компании
// если сервис больше не может вмещать в себя компании, возвращает ошибку
func (companyEnv *CompanyEnvList) StartEnv(ctx context.Context, companyId int64, companyConfig *conf.CompanyConfigStruct, capacityLimit int, mysqlMaxConn int, globalIsolation *GlobalIsolation.GlobalIsolation) (*Isolation, context.Context) {

	companyEnv.mutex.RLock()
	isolation, exists := companyEnv.envList[companyId]
	companyEnv.mutex.RUnlock()

	if exists {

		if companyConfig.Status != activeCompanyStatus && companyConfig.Status != vacantCompanyStatus {

			log.Infof("Компания не активная останавливаем %d", companyId)
			companyEnv.StopEnv(companyId)
		}

		// если компания перешла из состояния из вакантной в активную, не надо делать новую изоляцию
		return nil, nil
	}

	if len(companyEnv.envList) >= capacityLimit {

		log.Errorf("service capacity limit reached")
		return nil, nil
	}

	companyContext, cancel := context.WithCancel(ctx)

	// генерируем новый контекст исполнения для сервиса
	isolation, err := MakeIsolation(companyContext, companyId, companyConfig, cancel, mysqlMaxConn, globalIsolation)
	if err != nil {

		log.Errorf("Failed to create isolation for %d. Error: %d", companyId, err)
		return nil, nil
	}

	// добавляешь в хранилище
	companyEnv.mutex.Lock()
	companyEnv.envList[companyId] = isolation
	companyEnv.mutex.Unlock()

	log.Infof("added isolation for company %d", companyId)
	return isolation, companyContext
}

// останавливает среду исполнения компании
// если сервис не обслуживает компанию, то вернет ошибку
func (companyEnv *CompanyEnvList) StopEnv(companyId int64) {

	companyEnv.mutex.Lock()
	isolation, exists := companyEnv.envList[companyId]

	if exists {
		delete(companyEnv.envList, companyId)
	}
	companyEnv.mutex.Unlock()
	if !exists {
		return
	}

	// удаляем все observer
	isolation.Cancel()

	// закрываем соединение для company_data
	err := isolation.CompanyDataConn.Conn.Close()
	if err != nil {
		log.Error(err.Error())
	}

	log.Infof("company %d not served", companyId)
}

// GetEnv возвращает среду исполнения для компании
func (companyEnv *CompanyEnvList) GetEnv(companyId int64) *Isolation {

	companyEnv.mutex.RLock()
	isolation, exists := companyEnv.envList[companyId]
	companyEnv.mutex.RUnlock()

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

	companyEnv.mutex.RLock()

	// пройдемся по всем компаниям и вернем их ID
	for k := range companyEnv.envList {
		companyIdList[k] = struct{}{}
	}

	companyEnv.mutex.RUnlock()

	return companyIdList
}

/** Пакет описывает сущность изоляции исполнения внутри модуля
  Изоляция может быть связана с компанией или быть глобальной для сервиса **/

// структура контекста компании
type Isolation struct {
	companyId        int64
	companyConfig    *conf.CompanyConfigStruct
	Cancel           context.CancelFunc
	Context          context.Context
	SocketKeyToPivot string
	globalIsolation  *GlobalIsolation.GlobalIsolation
	MemberStore      *member.Storage
	ConfigStore      *config.Storage
	SessionStorage   *session.Storage
	CompanyDataConn  *company_data.DbConn
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
func MakeIsolation(ctx context.Context, companyId int64, companyConfig *conf.CompanyConfigStruct, cancel context.CancelFunc, mysqlMaxConn int, globalIsolation *GlobalIsolation.GlobalIsolation) (*Isolation, error) {

	if companyId == 0 {
		return nil, errors.New("company id must be defined in non-global context")
	}

	host := fmt.Sprintf("%s:%d", companyConfig.Mysql.Host, companyConfig.Mysql.Port)
	companyDataConn, err := company_data.MakeConnection(ctx, host, companyConfig.Mysql.User, companyConfig.Mysql.Pass, mysqlMaxConn)
	if err != nil {
		return nil, err
	}

	isolation := Isolation{
		companyId:       companyId,
		companyConfig:   companyConfig,
		Cancel:          cancel,
		Context:         ctx,
		globalIsolation: globalIsolation,
		CompanyDataConn: companyDataConn,
		MemberStore:     member.MakeStore(),
		ConfigStore:     config.MakeStore(),
		SessionStorage:  session.MakeStore(),
	}

	return &isolation, nil
}
