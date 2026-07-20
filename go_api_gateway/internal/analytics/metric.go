package analytics

import (
	"fmt"
	"slices"
	"strings"
	"sync"
)

const (
	labelStatusCode = "status_code"
	labelAuthMethod = "auth_method"
	labelTarget     = "target"
)

// алиас для ключа, состоящий из лейблов
type LabelsKey = string

// интерфейс метрики с необходимым функционалом для взаимодействия с prometheus
type Metric interface {
	String() string
}

// реестр метрик
type MetricRegistry struct {
	mu      sync.RWMutex
	metrics map[string]Metric
}

// дефолтный реестр
var DefaultMetricRegistry = &MetricRegistry{
	metrics: make(map[string]Metric),
}

// функция инициализации пакета
func init() {

	// запускаем рутину для периодического дампа информации о системе
	go monitorSystem()
}

// зарегистрировать новую метрику в реесте
func (r *MetricRegistry) register(name string, metric Metric) {

	r.mu.Lock()
	defer r.mu.Unlock()

	r.metrics[name] = metric
}

// создать новый счетчик в реестре
func (r *MetricRegistry) NewCounter(name, help string, labels []string) *Counter {

	counter := newCounter(name, help, labels)
	r.register(name, counter)

	return counter
}

// получить счетчик из реестра
func (r *MetricRegistry) GetCounter(name string) *Counter {

	r.mu.RLock()
	defer r.mu.RUnlock()

	if metric, ok := r.metrics[name]; ok {
		if counter, ok := metric.(*Counter); ok {
			return counter
		}
	}

	return nil
}

// создать новую гистограмму
func (r *MetricRegistry) NewHistogram(name, help string, buckets []float64, labels []string) *Histogram {

	histogram := newHistogram(name, help, buckets, labels)
	r.register(name, histogram)

	return histogram
}

// получить гистограмму из реестра
func (r *MetricRegistry) GetHistogram(name string) *Histogram {

	r.mu.RLock()
	defer r.mu.RUnlock()

	if metric, ok := r.metrics[name]; ok {
		if histogram, ok := metric.(*Histogram); ok {
			return histogram
		}
	}

	return nil
}

// создать новый датчик
func (r *MetricRegistry) NewGauge(name, help string, labels []string) *Gauge {

	gauge := newGauge(name, help, labels)
	r.register(name, gauge)

	return gauge
}

// получить датчик
func (r *MetricRegistry) GetGauge(name string) *Gauge {

	r.mu.RLock()
	defer r.mu.RUnlock()

	if metric, ok := r.metrics[name]; ok {
		if gauge, ok := metric.(*Gauge); ok {
			return gauge
		}
	}

	return nil
}

// перевести все метрики в реестре в формат prometheus
func (r *MetricRegistry) String() string {

	r.mu.RLock()
	defer r.mu.RUnlock()

	var builder strings.Builder

	names := make([]string, 0, len(r.metrics))

	for name := range r.metrics {
		names = append(names, name)
	}

	slices.Sort(names)

	for _, name := range names {
		builder.WriteString(fmt.Sprintf("%s\n", r.metrics[name].String()))
	}

	return builder.String()
}
