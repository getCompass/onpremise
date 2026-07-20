package analytics

import (
	"runtime"
	"time"
)

// функция для постоянного мониторинга системы
func monitorSystem() {

	t := time.NewTicker(5 * time.Second)
	defer t.Stop()

	for range t.C {

		// читаем информацию о памяти
		var mem runtime.MemStats
		runtime.ReadMemStats(&mem)

		MemoryUsage.Set(float64(mem.Alloc))
		GoroutinesCount.Set(float64(runtime.NumGoroutine()))
	}
}
