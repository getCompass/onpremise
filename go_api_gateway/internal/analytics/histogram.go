package analytics

import (
	"fmt"
	"slices"
	"strings"
	"sync"
)

// названия для гистограмм
const gatewayProcessTimeName = "gateway_process_time"
const fullRequestTimeName = "full_request_time"

// гистограмма для отправки в prometheus
type Histogram struct {
	mu           sync.RWMutex
	name         string
	help         string
	buckets      []float64
	labels       []string
	counts       map[string]uint64
	sums         map[string]float64
	bucketCounts map[string][]uint64
}

// возможные значения обработки запроса гейтвея в секундах
var durationBuckets = []float64{0.001, 0.0025, 0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10}

// инициализируем гистограмму с временем работы гейтвея
var (
	GatewayProcessTime = DefaultMetricRegistry.NewHistogram(
		gatewayProcessTimeName,
		"Time of processing request in gateway",
		durationBuckets,
		[]string{labelAuthMethod, labelTarget, labelStatusCode},
	)
	FullRequestTime = DefaultMetricRegistry.NewHistogram(
		fullRequestTimeName,
		"Time of processing request in app through gateway",
		durationBuckets,
		[]string{labelAuthMethod, labelTarget, labelStatusCode},
	)
)

// создать гистограмму
func newHistogram(name, help string, buckets []float64, labels []string) *Histogram {

	sortedBuckets := make([]float64, len(buckets))
	copy(sortedBuckets, buckets)
	slices.Sort(sortedBuckets)

	return &Histogram{
		name:         name,
		help:         help,
		buckets:      sortedBuckets,
		labels:       labels,
		counts:       make(map[string]uint64),
		sums:         make(map[string]float64),
		bucketCounts: make(map[string][]uint64),
	}
}

// определить значение в необходимый бакет гистограммы
func (h *Histogram) Observe(value float64, labelValues ...string) error {

	if len(labelValues) != len(h.labels) {
		return fmt.Errorf("unexpected counter labels count %d, expected %d", len(labelValues), len(h.labels))
	}

	h.mu.Lock()
	defer h.mu.Unlock()

	key := h.makeKey(labelValues)

	h.counts[key]++
	h.sums[key] += value

	if _, exists := h.bucketCounts[key]; !exists {
		h.bucketCounts[key] = make([]uint64, len(h.buckets)+1)
	}

	bucketIndex := len(h.buckets)

	for i, bucket := range h.buckets {

		if value <= bucket {

			bucketIndex = i
			break
		}
	}

	h.bucketCounts[key][bucketIndex]++

	return nil
}

// создать ключ для гистограммы
func (h *Histogram) makeKey(labelValues []string) LabelsKey {

	return strings.Join(labelValues, "\x00")
}

// конвертировать гистограмму в формат для prometheus
func (h *Histogram) String() string {

	h.mu.RLock()
	defer h.mu.RUnlock()

	var builder strings.Builder

	builder.WriteString(fmt.Sprintf("# HELP %s %s\n", h.name, h.help))
	builder.WriteString(fmt.Sprintf("# TYPE %s histogram\n", h.name))

	for key := range h.counts {

		labelValues := strings.Split(key, "\x00")

		labelStr := ""

		if len(h.labels) > 0 {

			labelPairs := make([]string, len(h.labels))
			for i, label := range h.labels {
				labelPairs[i] = fmt.Sprintf(`%s="%s"`, label, labelValues[i])
			}

			labelStr = fmt.Sprintf("{%s", strings.Join(labelPairs, ","))
		}

		cumulativeCount := uint64(0)
		for i, bucket := range h.buckets {

			cumulativeCount += h.bucketCounts[key][i]
			builder.WriteString(fmt.Sprintf("%s_bucket%s,le=\"%f\"} %d\n", h.name, labelStr, bucket, cumulativeCount))
		}

		cumulativeCount += h.bucketCounts[key][len(h.buckets)]

		builder.WriteString(fmt.Sprintf("%s_bucket%s,le=\"+Inf\"} %d\n", h.name, labelStr, cumulativeCount))

		builder.WriteString(fmt.Sprintf("%s_sum%s} %f\n", h.name, labelStr, h.sums[key]))
		builder.WriteString(fmt.Sprintf("%s_count%s} %d\n", h.name, labelStr, h.counts[key]))

	}

	return builder.String()
}
