package Generator

/** Пакет генераторов событий **/
/** В этом файле описана логика подписки на события **/

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
	"time"
)

// Generator сырой генератор, генерируется внешних данных и служит шаблоном,
// по которому потом генерируются события в контекстах брокеров
type Generator struct {

	// канал статуса состояния генератора
	stateChannel chan bool

	// каналы, в которые пушатся события для рутин-изоляций
	chStore   map[string]*Channel
	chStoreMx sync.RWMutex

	// данные определяющие работу генератора
	Name   string `json:"name"`   // имя генератора
	Period int    `json:"period"` // с каким периодом он выбрасывает события

	// функция, непосредственно генерирующая данные
	generateFn func() (interface{}, error)
}

// MakeGenerator создает новый генератор, но не запускает его
func MakeGenerator(name string, period int, generateFn func() (interface{}, error)) *Generator {

	return &Generator{
		Name:         name,
		Period:       period,
		generateFn:   generateFn,
		chStoreMx:    sync.RWMutex{},
		stateChannel: make(chan bool),
		chStore:      map[string]*Channel{},
	}
}

// AttachReceiver добавляет новый канал, в который генератор будет пушить сгенерированные штуки
func (g *Generator) AttachReceiver(key string) *Channel {

	g.chStoreMx.Lock()
	defer g.chStoreMx.Unlock()

	if _, exist := g.chStore[key]; !exist {

		ch := &Channel{
			ch:         make(chan interface{}),
			isReleased: true,
		}
		g.chStore[key] = ch
	}

	return g.chStore[key]
}

// DetachReceiver удаляем канал, если в его больше не нужно ничего пушить
func (g *Generator) DetachReceiver(key string) {

	g.chStoreMx.Lock()
	defer g.chStoreMx.Unlock()

	ch, exists := g.chStore[key]
	if !exists {
		return
	}

	// закрываем канал и удаляем
	ch.close()
	delete(g.chStore, key)
}

// открепляет всех получателей
func (g *Generator) detachAllReceivers() {

	g.chStoreMx.Lock()
	defer g.chStoreMx.Unlock()

	for key, ch := range g.chStore {

		// закрываем канал и удаляем запись
		ch.close()
		delete(g.chStore, key)
	}

}

// Start запускаем периодическую рассылку сообщения с помощью генератора
// каждый тик генератора отправляет событие во все компании, изоляции которых привязаны к нему
func (g *Generator) Start() {

	// устанавливаем минимальное значение для частоты тиков
	if g.Period < 60 {
		g.Period = 60
	}

	// инициализируем тикер с периодом тика из данных
	ticker := time.NewTicker(time.Duration(g.Period) * time.Second)

	go func() {

		for {
			select {
			case <-g.stateChannel:

				// отключаем генератор, когда в нем пропадает надобность
				ticker.Stop()
				g.detachAllReceivers()

				return
			case <-ticker.C:
				g.tick()
			}
		}
	}()
}

// инициирует тик генератора
// событие раскидывается по всем известным изоляциям
func (g *Generator) tick() {

	g.chStoreMx.RLock()

	// чтобы на ноль не делил,
	// при пустой мапе ничего все равно не происходит
	if len(g.chStore) < 1 {

		g.chStoreMx.RUnlock()
		return
	}

	// создаем специальное хранилище для тика, чтобы не блокировать надолго основное хранилище
	tickStore := make([]string, len(g.chStore))

	// копируем ключи каналов в хранилище
	for key := range g.chStore {
		tickStore = append(tickStore, key)
	}
	g.chStoreMx.RUnlock()

	// проходимся по составленному списку, чтобы выполнить функцию
	for _, key := range tickStore {

		g.chStoreMx.Lock()

		// получаем канал из стора
		ch, exist := g.chStore[key]

		// если он перестал существовать (например, открепили получателя), то идем к следующему ключу
		if !exist {

			g.chStoreMx.Unlock()
			continue
		}

		// пушим событие и ждем следующей отправки
		data, err := g.generateFn()
		if err != nil {

			g.chStoreMx.Unlock()
			continue
		}

		// если возникла ошибка (канал занят), пропускаем итерацию
		if err := ch.push(data); err != nil {

			log.Errorf("[GENERATOR] %s: channel is busy for receiver %s", g.Name, key)
			g.chStoreMx.Unlock()
			continue
		}

		g.chStoreMx.Unlock()

		time.Sleep(g.calculateDelay())
	}
}

// высчитывает задержку между тиками
func (g *Generator) calculateDelay() time.Duration {

	// считаем задержку между рассылками одного события по разным контекстам
	// это нужно для того, чтобы избежать условной тысячи тиков в один момент времени
	delay := (g.Period / len(g.chStore)) * 1000

	// если задержка получилась слишком большой,
	// то уменьшаем ее, чтобы не ждать подолгу и раньше освободить горутину
	if delay > 500 {
		delay = 500
	}

	return time.Duration(delay) * time.Millisecond
}
