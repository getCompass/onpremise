package Isolation

import (
	"context"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

/** Пакет описывает сущность изоляции исполнения внутри модуля
  Изоляция может быть связана с компанией или быть глобальной для сервиса **/

/** ЭТОТ ПАКЕТ НЕ МОЖЕТ ПОДКЛЮЧАТЬ ДРУГИЕ ПАКЕТЫ КАК ЗАВИСИМОСТИ,
  ИНАЧЕ ЭТО ВЫЗЫВАЕТ ПОРОЧНЫЙ КРУГ ЦИКЛИЧЕСКИХ ИМПОРТОВ **/

type ModifyIsolationFn func(*Isolation) error

var preInitFnList []ModifyIsolationFn
var initFnList []ModifyIsolationFn
var invalidateFnList []ModifyIsolationFn

var preInitGlobalFnList []ModifyIsolationFn
var initGlobalFnList []ModifyIsolationFn
var invalidateGlobalFnList []ModifyIsolationFn

// RegPackage добавляет функции для регистрации пакета в изоляции
func RegPackage(source string, preInitFn ModifyIsolationFn, initFn ModifyIsolationFn, invalidateFn ModifyIsolationFn) {

	log.Infof("добавляю модификаторы изоляции от источника %s", source)

	if preInitFn != nil {
		preInitFnList = append(preInitFnList, preInitFn)
	}

	if initFn != nil {
		initFnList = append(initFnList, initFn)
	}

	if invalidateFn != nil {
		invalidateFnList = append(invalidateFnList, invalidateFn)
	}
}

// RegPackageGlobal добавляет функции для регистрации пакета в глобальной изоляции
func RegPackageGlobal(source string, preInitFn ModifyIsolationFn, initFn ModifyIsolationFn, invalidateFn ModifyIsolationFn) {

	log.Infof("добавляю глобальные модификаторы изоляции от источника %s", source)

	if preInitFn != nil {
		preInitGlobalFnList = append(preInitGlobalFnList, preInitFn)
	}

	if initFn != nil {
		initGlobalFnList = append(initGlobalFnList, initFn)
	}

	if invalidateFn != nil {
		invalidateGlobalFnList = append(invalidateGlobalFnList, invalidateFn)
	}
}

// Isolation структура контекста компании
type Isolation struct {
	uniq         string
	isGlobal     bool
	companyId    int64
	abstractList map[string]interface{}
	lifeChannel  chan bool
	mainCtx      context.Context
	cancelCtx    context.CancelFunc
}

// функция жизненного цикла изоляции
func (i *Isolation) lifeRoutine() {

	needBreak := false

	Inc("isolation-routine")
	defer Dec("isolation-routine")

	for {

		if needBreak {
			break
		}

		select {
		case <-i.lifeChannel:

			// если закрылся канал, просто выходим из цикла
			log.Info("инвалидирую изоляцию — получен сигнал закрытия канала")
			needBreak = true
		case <-i.mainCtx.Done():

			// пытаемся инвалидиировать через глобальный метод, чтобы закрыть канал;
			// сразу после этого попадем в case выше, если канал не закрылся,
			// то выходим из цикла, что-то могло закрыть его прям в этот момент
			log.Info("инвалидирую изоляцию — глобальный контекст завершился")
			if i.Invalidate() != nil {
				needBreak = true
			}
		}
	}

	// останавливаем все, что было привязано к изоляции
	if err := i.invalidate(); err != nil {
		log.Errorf("не удалось инвалидировать изоляцию %s", err.Error())
	}

	// не забываем остановить контекст изоляции
	i.cancelCtx()
}

// инвалидирует изоляцию
func (i *Isolation) invalidate() error {

	// применяем все первичные модифицирующие вызовы
	if err := modifyIsolation(i, invalidateFnList); err != nil {
		return err
	}

	i.cancelCtx()
	return nil
}

// Set устанавливает значение именованного значения в настройках изоляции
func (i *Isolation) Set(flagName string, value interface{}) {

	if value != nil {

		if _, exists := i.abstractList[flagName]; exists {
			panic(fmt.Sprintf("rewriting isolation value %s", flagName))
		}

		i.abstractList[flagName] = value
		Inc(fmt.Sprintf("isolation-routine-%s", flagName))
	} else {

		delete(i.abstractList, flagName)
		Dec(fmt.Sprintf("isolation-routine-%s", flagName))
	}
}

// Get получает именование значение для изоляции
// если значение не установлено, возвращает nil
func (i *Isolation) Get(flagName string) interface{} {

	value, isExist := i.abstractList[flagName]
	if !isExist {
		return nil
	}

	return value
}

// IsGlobal возвращает флаг глобальности изоляции
func (i *Isolation) IsGlobal() bool {

	return i.isGlobal
}

// GetCompanyId возвращает ид компании для изоляции
func (i *Isolation) GetCompanyId() int64 {

	return i.companyId
}

// GetContext контекст для изоляции
func (i *Isolation) GetContext() context.Context {

	return i.mainCtx
}

// Invalidate инвалидирует изоляцию
func (i *Isolation) Invalidate() (err error) {

	defer func() {

		if recover() != nil {

			err = errors.New("attempt to invalidate closed isolation")
			log.Error(err.Error())
		}
	}()

	close(i.lifeChannel)
	err = nil
	return err
}

// GetUniq уникальный ключ изоляции
func (i *Isolation) GetUniq() string {

	return i.uniq
}

// MakeIsolation возвращает новую локальную изоляцию сервиса
// создать глобальную изоляцию самостоятельно нельзя
func MakeIsolation(uniq string, companyId int64) (*Isolation, error) {

	if companyId == 0 {
		return nil, errors.New("company id must be defined in non-global isolation")
	}

	// изоляции наследуют контекст глобальной изоляции
	// если глобальный заканчивается, то и эти тоже завершают свою работу
	ctx, cancel := context.WithCancel(Global().GetContext())

	isolation := &Isolation{
		uniq:         uniq,
		isGlobal:     false,
		companyId:    companyId,
		abstractList: map[string]interface{}{},
		lifeChannel:  make(chan bool),
		mainCtx:      ctx,
		cancelCtx:    cancel,
	}

	// применяем все первичные модифицирующие вызовы
	if err := modifyIsolation(isolation, preInitFnList); err != nil {
		return nil, err
	}

	// применяем все обычные модифицирующие вызовы
	if err := modifyIsolation(isolation, initFnList); err != nil {
		return nil, err
	}

	go isolation.lifeRoutine()
	return isolation, nil
}

// глобальная изоляция, одна на сервис
// отвечает за все действия, совершаемые с сервисом глобально, без привязки к компаниям
var globalIsolation Isolation
var isGlobalInitialized = false

// MakeGlobalIsolation инициализирует глобальную изоляцию
// глобальная изоляция существует в единственном экземпляре для всего модуля
func MakeGlobalIsolation(ctx context.Context) *Isolation {

	// убеждаемся, что глобальной изоляции еще не инициализировалась
	if isGlobalInitialized {
		panic("global isolation can not be initialized twice")
	}

	log.Warning("initializing global isolation")

	globalIsolation = Isolation{
		uniq:         "global",
		isGlobal:     true,
		companyId:    0,
		abstractList: map[string]interface{}{},
		mainCtx:      ctx,
		cancelCtx:    func() {},
	}

	// применяем все первичные модифицирующие вызовы
	if err := modifyIsolation(&globalIsolation, preInitGlobalFnList); err != nil {
		panic(fmt.Sprintf("global isolation can not be initialized: %s", err.Error()))
	}

	// применяем все обычные модифицирующие вызовы
	if err := modifyIsolation(&globalIsolation, initGlobalFnList); err != nil {
		panic(fmt.Sprintf("global isolation can not be initialized: %s", err.Error()))
	}

	isGlobalInitialized = true
	log.Warning("global isolation initialized successfully")

	return &globalIsolation
}

// Global инициализирует глобальную изоляцию
// глобальная изоляция существует в единственном экземпляре для всего модуля
func Global() *Isolation {

	return &globalIsolation
}

// вызывает модифицирующие функции на изоляции
func modifyIsolation(isolation *Isolation, callbackList []ModifyIsolationFn) error {

	// применяем все модифицирующие вызовы
	for _, callback := range callbackList {

		if err := callback(isolation); err != nil {
			return err
		}
	}

	return nil
}
