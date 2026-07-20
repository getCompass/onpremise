package analytics

import (
	"fmt"
	"slices"
	"strings"
	"sync"
)

// названия для датчиков
const (
	memoryUsageName          = "memory_usage"
	goroutinesCountName      = "goroutines_count"
	httpConnectionsCountName = "http_connections_count"
)

// инициализируем датчики для используемой памяти и запущенных горутин
var (
	MemoryUsage = DefaultMetricRegistry.NewGauge(
		memoryUsageName,
		"Memory usage of go microservice",
		nil,
	)
	GoroutinesCount = DefaultMetricRegistry.NewGauge(
		goroutinesCountName,
		"Goroutines count of go microservice",
		nil,
	)
	HttpConnectionsCount = DefaultMetricRegistry.NewGauge(
		httpConnectionsCountName,
		"Active http connections count of go microservice",
		nil,
	)
)

// датчик для отправки в prometheus
type Gauge struct {
	mu     sync.RWMutex
	name   string
	help   string
	labels []string
	values map[LabelsKey]float64
}

// создать новый счетчик
func newGauge(name, help string, labels []string) *Gauge {

	return &Gauge{
		name:   name,
		help:   help,
		labels: labels,
		values: map[LabelsKey]float64{},
	}
}

// установить значение счетчика
func (g *Gauge) Set(value float64, labelValues ...string) error {

	if len(labelValues) != len(g.labels) {
		return fmt.Errorf("unexpected gauge labels count %d, expected %d", len(labelValues), len(g.labels))
	}

	g.mu.Lock()
	defer g.mu.Unlock()

	key := g.makeKey(labelValues)

	g.values[key] = value

	return nil
}

// создать ключ для датчика
func (g *Gauge) makeKey(labelValues []string) LabelsKey {

	return strings.Join(labelValues, "\x00")
}

// конвертировать датчик в формат для prometheus
func (g *Gauge) String() string {

	g.mu.RLock()
	defer g.mu.RUnlock()

	if len(g.values) == 0 {
		return ""
	}

	var builder strings.Builder

	builder.WriteString(fmt.Sprintf("# HELP %s %s\n", g.name, g.help))
	builder.WriteString(fmt.Sprintf("# TYPE %s gauge\n", g.name))

	keys := make([]string, 0, len(g.values))

	for key := range g.values {
		keys = append(keys, key)
	}

	slices.Sort(keys)

	for _, key := range keys {

		labelValues := strings.Split(key, "\x00")
		value := g.values[key]

		if len(g.labels) == 0 {
			builder.WriteString(fmt.Sprintf("%s %f\n", g.name, value))
		} else {

			labelPairs := make([]string, len(g.labels))

			for i, label := range g.labels {
				labelPairs[i] = fmt.Sprintf(`%s="%s"`, label, labelValues[i])
			}

			builder.WriteString(fmt.Sprintf("%s{%s} %f\n", g.name, strings.Join(labelPairs, ","), value))
		}
	}

	return builder.String()
}
