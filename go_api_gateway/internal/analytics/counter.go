package analytics

import (
	"fmt"
	"slices"
	"strings"
	"sync"
)

// названия счетчиков
const httpRequestsTotalName = "http_requests_total"

// счетчик для отправки в prometheus
type Counter struct {
	mu     sync.RWMutex
	name   string
	help   string
	labels []string
	values map[LabelsKey]int64
}

// инициализируем количество запросов к гейтвею
var (
	HttpRequestsTotal = DefaultMetricRegistry.NewCounter(
		httpRequestsTotalName,
		"Total number of HTTP requests",
		[]string{labelAuthMethod, labelTarget, labelStatusCode},
	)
)

// создать объект счетчика
func newCounter(name, help string, labels []string) *Counter {
	return &Counter{
		name:   name,
		help:   help,
		labels: labels,
		values: map[LabelsKey]int64{},
	}
}

// инкрементировать счетчик
func (c *Counter) Inc(labelValues ...string) error {

	if len(labelValues) != len(c.labels) {
		return fmt.Errorf("unexpected counter labels count %d, expected %d", len(labelValues), len(c.labels))
	}

	c.mu.RLock()
	defer c.mu.RUnlock()

	key := c.makeKey(labelValues)

	c.values[key]++

	return nil
}

// создать ключ для счетчика, который включает в себя лейблы
func (c *Counter) makeKey(labelValues []string) LabelsKey {

	return strings.Join(labelValues, "\x00")
}

// получить счетчик
func (c *Counter) Get(labelValues ...string) (int64, error) {

	if len(labelValues) != len(c.labels) {
		return 0, fmt.Errorf("unexpected counter labels count %d, expected %d", len(labelValues), len(c.labels))
	}

	key := c.makeKey(labelValues)

	c.mu.RLock()
	defer c.mu.RUnlock()

	return c.values[key], nil
}

// перевести счетчик в формат для prometheus
func (c *Counter) String() string {

	c.mu.RLock()
	defer c.mu.RUnlock()

	if len(c.values) == 0 {
		return ""
	}

	var builder strings.Builder

	builder.WriteString(fmt.Sprintf("# HELP %s %s\n", c.name, c.help))
	builder.WriteString(fmt.Sprintf("# TYPE %s counter\n", c.name))

	keys := make([]string, 0, len(c.values))

	for key := range c.values {
		keys = append(keys, key)
	}

	slices.Sort(keys)

	for _, key := range keys {

		labelValues := strings.Split(key, "\x00")
		value := c.values[key]

		if len(c.labels) == 0 {
			builder.WriteString(fmt.Sprintf("%s %d\n", c.name, value))
		} else {

			labelPairs := make([]string, len(c.labels))

			for i, label := range c.labels {
				labelPairs[i] = fmt.Sprintf(`%s="%s"`, label, labelValues[i])
			}

			builder.WriteString(fmt.Sprintf("%s{%s} %d\n", c.name, strings.Join(labelPairs, ","), value))
		}
	}

	return builder.String()
}
