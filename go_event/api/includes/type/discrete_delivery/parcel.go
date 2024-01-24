package DiscreteDelivery

// Parcel тип, описывающий посылку
type Parcel struct {
	unique       string      // ключ уникальности
	content      interface{} // содержимое, система доставки не знает, что там
	attemptCount int         // количество попыток доставить
	errorCount   int         // количество ошибок при доставке
}

// InitParcel создает новую посылку
func InitParcel(key string, content interface{}) *Parcel {

	return &Parcel{
		content: content,
		unique:  key,
	}
}

// GetContent возвращает данные о содержимом
func (parcel *Parcel) GetContent() interface{} {

	return parcel.content
}

// GetUuid возвращает, id для посылки
func (parcel *Parcel) GetUuid() string {

	return parcel.unique
}

// GetAttemptCount возвращает, attemptCount для посылки
func (parcel *Parcel) GetAttemptCount() int {

	return parcel.attemptCount
}

// GetErrorCount возвращает, errorCount для посылки
func (parcel *Parcel) GetErrorCount() int {

	return parcel.errorCount
}

// OnSent увеличивает число попыток отправки
func (parcel *Parcel) OnSent() {

	parcel.attemptCount++
}

// OnError увеличивает число ошибок
func (parcel *Parcel) OnError() {

	parcel.errorCount++
}
