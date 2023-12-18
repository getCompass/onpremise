package Generator

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
)

// хранилище всех генераторов
// когда стартует компания, она запускает для себя экземпляры этих генераторов
var activeGeneratorStore = struct {
	store map[string]*Generator // само хранилище
	mx    sync.RWMutex          // мьютекс, чтобы не падало ничего
}{
	store: map[string]*Generator{},
	mx:    sync.RWMutex{},
}

// StartGenerator инициализирует новый raw генератор
// все генераторы должны быть проинициализированы до того, как начнут заселяться компании
func StartGenerator(g *Generator) error {

	activeGeneratorStore.mx.Lock()
	defer activeGeneratorStore.mx.Unlock()

	// сохраняем и запускаем генератор
	activeGeneratorStore.store[g.Name] = g
	g.Start()

	return nil
}

// StopGenerator инициализирует новый raw генератор
// все генераторы должны быть проинициализированы до того, как начнут заселяться компании
func StopGenerator(name string) {

	activeGeneratorStore.mx.Lock()
	defer activeGeneratorStore.mx.Unlock()

	if _, isExist := activeGeneratorStore.store[name]; !isExist {

		log.Warningf("attempt to delete non-existing generator %s", name)
		return
	}

	activeGeneratorStore.store[name].stateChannel <- true
	delete(activeGeneratorStore.store, name)
}
