package observer

// оставил закомменченный пример, чтобы было проще потом и не искать как выглядит ;)
// var _is1DayWork atomic.Value

// метод для выполнения работы через время
func Work() {

}

//каждые день
//func _doWork1DayExample() {

//if _is1DayWork.Load() != nil && _is1DayWork.Load().(bool) == true {
//	return
//}
//_is1DayWork.Store(true)
//
//	for {
//
//		// задержка 2 секунды
//		time.Sleep(2 * time.Second * 60 * 24)
//	}
//}
