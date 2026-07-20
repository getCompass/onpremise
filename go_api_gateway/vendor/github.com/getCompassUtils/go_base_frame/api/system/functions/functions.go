package functions

import (
	"crypto/md5"
	"crypto/rand"
	"crypto/sha1"
	"encoding/hex"
	"fmt"
	"log"
	"os"
	"path/filepath"
	"reflect"
	"strconv"
	"strings"
	"sync"
	"time"
)

// -------------------------------------------------------
// пакет содержащий вспомогательные функции системы
// -------------------------------------------------------

var (
	startTime = GetCurrentTimeStamp() // время начала работы микросервиса
)

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// создаем файл, если не был создан
func CreateFileIfNotExist(fileName string) error {

	// получаем состояние файла
	_, err := os.Stat(fileName)

	// проверяем существование файла
	if os.IsNotExist(err) {

		// создаем файл
		file, err := os.Create(fileName)
		if err != nil {
			return err
		}

		// закрываем файл
		_ = file.Close()
	}

	return nil
}

// получаем путь к директории исполняемого файла
func GetExecutableDir(executableDir string) string {

	// получаем путь к директории исполняемого файла
	executablePath, _ := os.Executable()
	executableDir = filepath.Dir(executablePath)

	return executableDir
}

// получаем текущее время
func GetCurrentTimeStamp() int64 {
	return int64(time.Now().Unix())
}

// получаем текущее время с точностью до миллисекунд
func GetCurrentTimeStampMilli() int64 {
	return int64(time.Now().UnixNano()) / (int64(time.Millisecond) / int64(time.Nanosecond))
}

// получаем время работы микросервиса
func GetUpTime() int64 {
	return GetCurrentTimeStamp() - startTime
}

// генерируем uuid
func GenerateUuid() string {

	b := make([]byte, 16)
	_, err := rand.Read(b)
	if err != nil {
		log.Fatal(err)
	}
	return fmt.Sprintf("%x-%x-%x-%x-%x", b[0:4], b[4:6], b[6:8], b[8:10], b[10:])
}

// конвертируем строку в int64
func StringToInt64(string string) int64 {

	// конвертируем строку
	value, err := strconv.Atoi(string)
	if err != nil {
		return 0
	}

	return int64(value)
}

// конвертируем строку в int32
func StringToInt32(string string) int32 {

	// конвертируем строку
	value, err := strconv.Atoi(string)
	if err != nil {
		return 0
	}

	return int32(value)
}

// конвертируем строку в int
func StringToInt(string string) int {

	// конвертируем строку
	value, err := strconv.Atoi(string)
	if err != nil {
		return 0
	}

	return value
}

// конвертируем строку в int
func InterfaceListToInt64List(interfaceList []interface{}) []int64 {

	int64List := make([]int64, 0)
	for _, v := range interfaceList {
		int64List = append(int64List, int64(v.(float64)))
	}

	return int64List
}

// конвертируем строку в bool
func StringToBool(someString string) bool {

	// если единица - true
	if someString == "1" {
		return true
	}

	// если ноль 0 - false
	if someString == "0" {
		return false
	}

	log.Fatal(fmt.Errorf("cant this int to bool"))

	return false
}

// оставляем только уникальные значения в массиве int64
func UniqueInt64(int64List []int64) []int64 {

	// создаем массивы
	uniqueInt64List := make([]int64, 0, len(int64List))
	m := make(map[int64]bool)

	// проходимся по массиву
	for _, value := range int64List {

		// проверяем наличие элемента в массиве
		if _, ok := m[value]; !ok {

			// сохраняем элемент
			m[value] = true
			uniqueInt64List = append(uniqueInt64List, value)
		}
	}

	return uniqueInt64List
}

// получаем разницу между двумя массивами int64
func DifferenceInt64List(a, b []int64) []int64 {

	mb := map[int64]bool{}
	for _, x := range b {
		mb[x] = true
	}
	var ab []int64
	for _, x := range a {
		if _, ok := mb[x]; !ok {
			ab = append(ab, x)
		}
	}
	return ab
}

// функция для проверки наличия string в string slice
func StringSliceContains(slice []string, item string) bool {
	for _, i := range slice {
		if i == item {
			return true
		}
	}
	return false
}

// получаем md5 хэш сумму файла
func GetMd5SumString(srcString string) string {
	md5Bytes := md5.Sum([]byte(srcString))
	result := hex.EncodeToString(md5Bytes[:])
	return result
}

// считаем разницу между a и b
func Div(a int, b int) int {
	return (a - a%b) / b
}

// проверяем, что в слайсе есть строка
func IsStringInSlice(a string, list []string) bool {
	for _, b := range list {
		if b == a {
			return true
		}
	}
	return false
}

// проверяем есть ли такой элемент в массиве
func InArray(val interface{}, array interface{}) (exists bool, index int) {

	exists = false
	index = -1

	switch reflect.TypeOf(array).Kind() {
	case reflect.Slice:
		s := reflect.ValueOf(array)

		for i := 0; i < s.Len(); i++ {

			if reflect.DeepEqual(val, s.Index(i).Interface()) {
				index = i
				exists = true
				return
			}
		}
	}

	return
}

// функция для получения текущего часа
func HourStart() int64 {

	timeObj := time.Now()

	year := timeObj.Year()
	month := timeObj.Month()
	day := timeObj.Day()
	hour := timeObj.Hour()
	location := timeObj.Location()

	hourStart := time.Date(year, month, day, hour, 0, 0, 0, location).Unix()

	return hourStart
}

// функция для получениея текущего дня
func DayStart() int64 {

	timeObj := time.Now()

	year := timeObj.Year()
	month := timeObj.Month()
	day := timeObj.Day()
	location := timeObj.Location()

	dayStart := time.Date(year, month, day, 0, 0, 0, 0, location).Unix()

	return dayStart
}

// функция для получения текущего часа с параметром
func HourStartWithParam(t time.Time) int64 {

	year := t.Year()
	month := t.Month()
	day := t.Day()
	hour := t.Hour()
	location := t.Location()

	hourStart := time.Date(year, month, day, hour, 0, 0, 0, location).Unix()

	return hourStart
}

// функция для получениея текущего дня с параметром
func DayStartWithParam(t time.Time) int64 {

	year := t.Year()
	month := t.Month()
	day := t.Day()
	location := t.Location()

	dayStart := time.Date(year, month, day, 0, 0, 0, 0, location).Unix()

	return dayStart
}

// функция для получениея начала недели по времени
func WeekStart(t time.Time) int64 {

	weekday := time.Duration(t.Weekday())
	if weekday == 0 {
		weekday = 7
	}

	year, month, day := t.Date()
	currentZeroDay := time.Date(year, month, day, 0, 0, 0, 0, time.UTC)
	return currentZeroDay.Add(-1 * (weekday - 1) * 24 * time.Hour).Unix()
}

func IntToString(someInt int) string {
	return fmt.Sprintf("%v", someInt)
}

func Int64ToString(someInt int64) string {
	return fmt.Sprintf("%v", someInt)
}

func Uint64ToString(someInt uint64) string {
	return fmt.Sprintf("%v", someInt)
}

// массив в строку
func ArrayToString(a []int, delim string) string {
	return strings.Trim(strings.Replace(fmt.Sprint(a), " ", delim, -1), "[]")
}

// проверка sync.Map на пустоту
func IsEmpty(someMap *sync.Map) bool {

	isEmpty := true
	someMap.Range(func(_, _ interface{}) bool {

		// return false чтобы сразу выйти из перебора
		isEmpty = false
		return false
	})

	return isEmpty
}

// получаем sha1 хэш
func GetSha1String(srcString string) string {

	h := sha1.New()
	_, _ = h.Write([]byte(srcString))
	return hex.EncodeToString(h.Sum(nil))
}

// удаляем элемент из списка
func RemoveItemFromListInt64(item int64, list []int64) []int64 {

	var newList []int64
	for _, v := range list {

		if v != item {
			newList = append(newList, v)
		}
	}

	return newList
}

// проверяем, что в слайсе есть int
func IsInt64InSlice(a int64, list []int64) bool {

	for _, b := range list {
		if b == a {
			return true
		}
	}
	return false
}

// провеяряем что находимся на тестовом окружении
func AssertTestServer(serverType string) {

	if serverType != "test-server" {
		panic("run tests on public server!!!")
	}
}

// получаем количество дней для выбранного года и времени
func GetDaysCountByTimestamp(timeAt int64, yearStart int) int {

	timeDate := time.Unix(timeAt, 0)
	year := timeDate.Year()
	month := timeDate.Month()
	day := timeDate.Day()

	// получаем количество прошедших дней с выбранного года
	t1 := time.Date(yearStart, time.Month(1), 1, 12, 0, 0, 0, time.UTC)
	t2 := time.Date(year, month, day, 12, 0, 0, 0, time.UTC)

	// получаем разницу в днях между первым днем первого месяца выбранного года
	// и выбранным временем
	dayCount := int(t2.Sub(t1).Hours()/24 + 1)

	return dayCount
}

// получаем количество дней на текущий день для выбранного года
func GetDaysCountByYear(year int) int {

	// получаем количество прошедших дней с выбранного года
	t1 := time.Date(year, time.Month(1), 1, 12, 0, 0, 0, time.UTC)
	t2 := time.Date(year, time.Now().Month(), time.Now().Day(), 12, 0, 0, 0, time.UTC)

	// получаем разницу в днях между первым днем первого месяца выбранного года и текущим моментом
	dayCount := int(t2.Sub(t1).Hours()/24 + 1)

	return dayCount
}

// получаем ключи int64 из мапы
func GetInt64MapKeyList(mapItem map[int64]bool) []int64 {

	keys := make([]int64, 0, len(mapItem))
	for k := range mapItem {
		keys = append(keys, k)
	}

	return keys
}

// получаем ключи int из мапы
func GetIntMapKeyList(mapItem map[int]bool) []int {

	keys := make([]int, 0, len(mapItem))
	for k := range mapItem {
		keys = append(keys, k)
	}

	return keys
}

// проверяем, что в слайсе есть строка и берем ее индекс
func GetKeyInStringSlice(a string, list []string) int {
	for k, b := range list {
		if b == a {
			return k
		}
	}
	return -1
}

// получить пересечение списков
func ListIntersection(s1, s2 []string) (inter []string) {
	hash := make(map[string]bool)
	for _, e := range s1 {
		hash[e] = true
	}
	for _, e := range s2 {

		if hash[e] {
			inter = append(inter, e)
		}
	}

	inter = RemoveDups(inter)
	return
}

// SliceDiff получить расхождение списков
func SliceDiff(a, b []int64) []int64 {
	mb := make(map[int64]struct{}, len(b))
	for _, x := range b {
		mb[x] = struct{}{}
	}
	var diff []int64
	for _, x := range a {
		if _, found := mb[x]; !found {
			diff = append(diff, x)
		}
	}
	return diff
}

// удалить дубликаты из списка
func RemoveDups(elements []string) (nodups []string) {
	encountered := make(map[string]bool)
	for _, element := range elements {
		if !encountered[element] {
			nodups = append(nodups, element)
			encountered[element] = true
		}
	}
	return
}

// удалить дубликаты из списка (на generic)
func RemoveDuplicates[T comparable](elements []T) (nodups []T) {

	encountered := make(map[T]bool)
	for _, element := range elements {
		if !encountered[element] {
			nodups = append(nodups, element)
			encountered[element] = true
		}
	}
	return
}
