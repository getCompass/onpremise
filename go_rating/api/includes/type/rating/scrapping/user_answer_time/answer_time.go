package user_answer_time

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Isolation "go_rating/api/includes/type/isolation"
	"go_rating/api/includes/type/rating/collecting/pivot"
	"go_rating/api/system"
	"sync"
	"time"
)

const slicePeriod = 2 * time.Hour // удаляем кэши старее этого значения
const microConversationUpdatePeriod = int64(15 * 60)
const microConversationClosePeriod = int64(24 * 60 * 60)

// агрегатор времени ответа на сообщения
// собирает и держит в себе время ответа на сообщения по 15 минут
type useranswertime struct {
	storedConversationList      map[string]*ConversationListStruct // кэш с микро-диалогами
	storedAnswerTimeListBy15Min map[int64]*AnswerTimeStruct        // время ответа на сообщения разбитое по 15 минуткам
	mx                          sync.RWMutex                       // семафор для хранилища
}

// ConversationListStruct структура объекта
type ConversationListStruct struct {
	MicroConversationList map[int64]*MicroConversationStruct
}

// MicroConversationStruct структура объекта
type MicroConversationStruct struct {
	SenderUserIdList              []int64
	IsAnswered                    int64
	MicroConversationStartAt      int64
	MicroConversationLocalStartAt string
	MicroConversationEndAt        int64
}

// AnswerTimeStruct структура объекта
type AnswerTimeStruct struct {
	SpaceId          int64
	ConversationList map[string]*ConversationAnswerTimeStruct
}

// ConversationAnswerTimeStruct структура объекта
type ConversationAnswerTimeStruct struct {
	UserAnswerTimeList []*UserAnswerTimeStruct
}

// UserAnswerTimeStruct структура объекта
type UserAnswerTimeStruct struct {
	UserID     int64 `json:"user_id"`
	AnswerTime int64 `json:"answer_time"`
	AnsweredAt int64 `json:"answered_at"`
}

// makeUserAnswerTime создает экземпляр useranswertime
func makeUserAnswerTime(isolation *Isolation.Isolation) *useranswertime {

	mAnswerTime := &useranswertime{
		storedConversationList:      map[string]*ConversationListStruct{},
		storedAnswerTimeListBy15Min: map[int64]*AnswerTimeStruct{},
		mx:                          sync.RWMutex{},
	}

	go mAnswerTime.routine(isolation)
	return mAnswerTime
}

// основная рутина жизненного цикла экземпляра useranswertime
func (m *useranswertime) routine(isolation *Isolation.Isolation) {

	Isolation.Inc("useranswertime-life-routine")

	// добавляем экземпляр в коллектор и объявляем вызов unregister колбека
	unregister := pivot.LeaseUserAnswerTimeCollector(Isolation.Global()).RegisterSource("useranswertime:"+isolation.GetUniq(), m)
	defer func() {

		unregister()
		Isolation.Dec("useranswertime-life-routine")
	}()

	for {

		select {

		case <-time.After(time.Minute * 15):

			// удаляем всю устаревшую статистику
			m.purgeAnswerTime(time.Now().Unix()-int64(slicePeriod.Seconds()), true)

		case <-time.After(time.Hour * 1):

			// удаляем старые микродиалоги
			m.purgeClosedMicroConversationList()

		case <-isolation.GetContext().Done():

			return
		}
	}
}

// добавляет время ответа пользователя на сообщение в хранилище
func (m *useranswertime) pushConversationAnswerState(spaceId int64, conversationKey string, senderUserId int64, receiverUserIdList []int64, sentAt int64, localSentAt string) (
	int64,
	string,
	int64,
	int64,
	int64,
	int64,
	error,
) {

	spaceId, conversationKey, answerTime, createdAt, microConversationStartAt,
		microConversationEndAt, err := m.pushOneConversationAnswerState(spaceId, conversationKey, senderUserId, receiverUserIdList, sentAt, localSentAt)

	if err != nil {
		log.Errorf("passed bad answer time %s", err.Error())
	}

	return spaceId, conversationKey, answerTime, createdAt, microConversationStartAt, microConversationEndAt, err
}

// добавляет время ответа пользователя на сообщение в хранилище
func (m *useranswertime) pushOneConversationAnswerState(spaceId int64, conversationKey string, senderUserId int64, receiverUserIdList []int64, sentAt int64, localSentAt string) (
	int64,
	string,
	int64,
	int64,
	int64,
	int64,
	error,
) {

	// получаем микро-диалог для senderUserId
	senderMicroConversation := m.getMicroConversation(conversationKey, senderUserId)

	answerTime := int64(0)
	answeredAt := int64(0)
	microConversationStartAt := int64(0)
	microConversationEndAt := int64(0)

	// есть ли микро-диалог для senderUserId и есть ли кто-то из receiverUserIdList в списке отправителей
	microConversationCreatedByReceiverUserIdList := map[int64]bool{}
	if senderMicroConversation != nil {
		microConversationCreatedByReceiverUserIdList = makeMicroConversationCreatedByReceiverUserIdList(receiverUserIdList, senderMicroConversation)
	}

	// создаем/обновляем микро-диалог для каждого из receiverUserIdList
	for _, userId := range receiverUserIdList {
		m.doCacheMicroConversationReceiver(conversationKey, microConversationCreatedByReceiverUserIdList, senderUserId, userId, sentAt, localSentAt)
	}

	// если есть микро-диалог созданный для отправителя (значит от него ожидается ответ)
	if senderMicroConversation != nil {

		// если еще не отвечали
		if senderMicroConversation.IsAnswered == 0 {

			// на случай если уже списали время ранее (хз как такое возможно без ручного скрипта добавления времени)
			userAnswerTime := m.getUserAnswerTime(conversationKey, senderUserId)
			if userAnswerTime == nil {

				// записываем время ответа пользователя в кэш
				answerTime = calculateAnswerTimeByLocalTime(sentAt, localSentAt, senderMicroConversation.MicroConversationStartAt, senderMicroConversation.MicroConversationLocalStartAt)
				if answerTime > 0 {
					answeredAt = m.setUserAnswerTime(spaceId, conversationKey, senderUserId, answerTime)
				}
			}

			// помечае что ответили в микро-диалоге и списали время
			senderMicroConversation.IsAnswered = 1
		}

		// обновляем время закрытия микро-диалога
		senderMicroConversation.MicroConversationEndAt = sentAt + microConversationUpdatePeriod

		// обновляем микро-диалог
		m.setMicroConversation(conversationKey, senderUserId, senderMicroConversation)
		microConversationStartAt = senderMicroConversation.MicroConversationStartAt
		microConversationEndAt = senderMicroConversation.MicroConversationEndAt
	}

	return spaceId, conversationKey, answerTime, answeredAt, microConversationStartAt, microConversationEndAt, nil
}

// формируем мапу user_id => bool
func makeMicroConversationCreatedByReceiverUserIdList(receiverUserIdList []int64, microConversation *MicroConversationStruct) map[int64]bool {

	// есть ли микро-диалог для senderUserId и есть ли кто-то из receiverUserIdList в списке отправителей
	microConversationCreatedByReceiverUserIdList := map[int64]bool{}
	for _, userId := range receiverUserIdList {

		isExist := false
		for _, receiverUserId := range microConversation.SenderUserIdList {

			if userId == receiverUserId {

				isExist = true
				break
			}
		}
		microConversationCreatedByReceiverUserIdList[userId] = isExist
	}

	return microConversationCreatedByReceiverUserIdList
}

// считаем время ответа
func calculateAnswerTimeByLocalTime(sentAt int64, localSentAt string, microConversationStartAt int64, microConversationLocalStartAt string) int64 {

	answerTime := sentAt - microConversationStartAt
	const layout = "02.01.2006 15:04:05 -0700"
	const workingHoursStart = 10
	const workingHoursEnd = 19

	timeLocalSentAt, err := time.Parse(layout, localSentAt)
	if err != nil {
		return answerTime
	}

	timeMicroConversationLocalStartAt, err := time.Parse(layout, microConversationLocalStartAt)
	if err != nil {
		return answerTime
	}

	timeMicroConversationLocalStartAt = timeMicroConversationLocalStartAt.In(timeLocalSentAt.Location())

	// если ответ в не рабочее время и время ответа больше 1 часа - не пишем стату
	if (timeLocalSentAt.Hour() < workingHoursStart || timeLocalSentAt.Hour() >= workingHoursEnd) && answerTime > 60*60*1 {
		return 0
	}

	// если ответ в субботу или воскресенье и время ответа больше 1 часа - не пишем стату
	weekday := timeLocalSentAt.Weekday()
	if (weekday == time.Saturday || weekday == time.Sunday) && answerTime > 60*60*1 {
		return 0
	}

	// если написали до начала рабочего дня, но ответил после начала рабочего дня - корректируем (не рабочее время корректируется выше)
	if timeMicroConversationLocalStartAt.Day() == timeLocalSentAt.Day() && timeMicroConversationLocalStartAt.Hour() < workingHoursStart && timeLocalSentAt.Hour() >= workingHoursStart {

		// корректируем время ответа
		timeMicroConversationLocalStartAt = makeTimeWorkingStartAt(workingHoursStart, timeMicroConversationLocalStartAt)
		return timeLocalSentAt.Unix() - timeMicroConversationLocalStartAt.Unix()
	}

	// если написали в один день, а ответил на следующий день, но ответил после начала рабочего дня - корректируем (не рабочее время корректируется выше)
	if timeMicroConversationLocalStartAt.Day() != timeLocalSentAt.Day() && timeLocalSentAt.Hour() >= workingHoursStart && timeLocalSentAt.Hour() < workingHoursEnd {

		timeWorkingStartAt := makeTimeWorkingStartAt(workingHoursStart, timeLocalSentAt)
		secondsFromStartWorkDay := timeLocalSentAt.Unix() - timeWorkingStartAt.Unix()

		timeWorkingEndAt := makeTimeWorkingStartAt(workingHoursEnd, timeMicroConversationLocalStartAt)
		secondsFromEndWorkDay := timeWorkingEndAt.Unix() - timeMicroConversationLocalStartAt.Unix()

		// корректируем время ответа
		return secondsFromEndWorkDay + secondsFromStartWorkDay
	}

	// иначе отдаем как есть
	return answerTime
}

func makeTimeWorkingStartAt(workingHoursStart int, t time.Time) time.Time {

	return time.Date(t.Year(), t.Month(), t.Day(), workingHoursStart, 0, 0, 0, t.Location())
}

// CollectUserAnswerTime возвращает все 15-ти минутки
// после сбора данных очищает хранилище
func (m *useranswertime) CollectUserAnswerTime() []*pivot.UserAnswerTimeConversationListStruct {

	var output []*pivot.UserAnswerTimeConversationListStruct

	m.mx.Lock()
	defer m.mx.Unlock()

	// проходим по всем 15-ти минуткам
	answerTimeSendTill := int64(0)
	for min15Start, storedAnswerTime := range m.storedAnswerTimeListBy15Min {

		// текущий кэш не трогаем
		if min15Start >= system.Min15Start() {
			continue
		}

		// сохраняем до какой максимальной 15-ти минутки почистили
		if min15Start > answerTimeSendTill {
			answerTimeSendTill = min15Start
		}

		// пробегаемся по всем 15-ти минуткам
		for conversationKey, conversationItem := range storedAnswerTime.ConversationList {

			// собираем массив пользователей
			userAnswerTimeList := make([]*pivot.UserAnswerTimeListStruct, 0)
			for _, userAnswerItem := range conversationItem.UserAnswerTimeList {

				userAnswerTimeList = append(userAnswerTimeList, &pivot.UserAnswerTimeListStruct{
					UserID:     userAnswerItem.UserID,
					AnswerTime: userAnswerItem.AnswerTime,
					AnsweredAt: userAnswerItem.AnsweredAt,
				})
			}

			// добавляем диалог в ответ
			output = append(output, &pivot.UserAnswerTimeConversationListStruct{
				Min15StartAt:       min15Start,
				SpaceId:            storedAnswerTime.SpaceId,
				ConversationKey:    conversationKey,
				UserAnswerTimeList: userAnswerTimeList,
			})

		}
	}

	// запускаем очистку хранилища если что-то очистили
	if answerTimeSendTill > 0 {

		m.purgeAnswerTime(answerTimeSendTill, false)
		log.Infof("answer time was collected till %d", answerTimeSendTill)
	}

	return output
}

// выполняет очистку записей за 15 минут
func (m *useranswertime) purgeAnswerTime(answerTimeSendTill int64, needBlock bool) {

	// спорное решение, но позволяет держать код чуть чище в других местах
	// при сборе данных нужно почистить хранилище, не снимая блокировки в основном хранилище
	if needBlock {

		m.mx.Lock()
		defer m.mx.Unlock()
	}

	for min15Start := range m.storedAnswerTimeListBy15Min {

		if min15Start <= answerTimeSendTill {

			delete(m.storedAnswerTimeListBy15Min, min15Start)
			log.Infof("answer time for %v was purged", min15Start)
		}
	}
}

// добавляет время получения сообщения пользователям в хранилище
func (m *useranswertime) pushForReceivers(conversationKey string, senderUserId int64, receiverUserIdList []int64, sentAt int64, localSentAt string) {

	if err := m.pushOneForReceivers(conversationKey, senderUserId, receiverUserIdList, sentAt, localSentAt); err != nil {
		log.Errorf("passed bad answer time for receivers %s", err.Error())
	}
}

// добавляет время получения сообщения пользователям в хранилище
func (m *useranswertime) pushOneForReceivers(conversationKey string, senderUserId int64, receiverUserIdList []int64, receivedAt int64, senderLocalSentAt string) error {

	// получаем микро-диалог для senderUserId
	senderMicroConversation := m.getMicroConversation(conversationKey, senderUserId)

	// есть ли кто-то из receiverUserIdList в списке отправителей
	microConversationCreatedByReceiverUserIdList := map[int64]bool{}
	if senderMicroConversation != nil {
		microConversationCreatedByReceiverUserIdList = makeMicroConversationCreatedByReceiverUserIdList(receiverUserIdList, senderMicroConversation)
	}

	// обновляем микро-диалог для получателей
	for _, userId := range receiverUserIdList {
		m.doCacheMicroConversationReceiver(conversationKey, microConversationCreatedByReceiverUserIdList, senderUserId, userId, receivedAt, senderLocalSentAt)
	}

	return nil
}

// закрывает микро-диалог
func (m *useranswertime) closeMicroConversation(conversationKey string, senderUserId int64, receiverUserIdList []int64) {

	if err := m.closeOneMicroConversation(conversationKey, senderUserId, receiverUserIdList); err != nil {
		log.Errorf("can't close micro conversation %s", err.Error())
	}
}

// закрывает микро-диалог
func (m *useranswertime) closeOneMicroConversation(conversationKey string, senderUserId int64, receiverUserIdList []int64) error {

	// получаем микро-диалог для senderUserId
	senderMicroConversation := m.getMicroConversation(conversationKey, senderUserId)
	if senderMicroConversation == nil {

		for _, receiverUserId := range receiverUserIdList {

			receiverMicroConversation := m.getMicroConversation(conversationKey, receiverUserId)
			if receiverMicroConversation != nil {

				for _, userId := range receiverMicroConversation.SenderUserIdList {

					if userId == senderUserId {

						m.deleteMicroConversation(conversationKey, receiverUserId)
						break
					}
				}
			}
		}

		return nil
	}

	m.deleteMicroConversation(conversationKey, senderUserId)
	return nil
}

// выполняет очистку закрытых микро-диалогов
func (m *useranswertime) purgeClosedMicroConversationList() {

	m.mx.Lock()
	defer m.mx.Unlock()

	for conversationKey, conversationItem := range m.storedConversationList {

		// удаляем закрытые микро диалоги
		for userId, microConversationItem := range conversationItem.MicroConversationList {

			// если ответа не было 24 часа и более
			if microConversationItem.MicroConversationEndAt == 0 && microConversationItem.MicroConversationStartAt+microConversationClosePeriod < time.Now().Unix() {

				delete(m.storedConversationList[conversationKey].MicroConversationList, userId)
				continue
			}

			// если диалог закрылся больше 5 минут назад
			if microConversationItem.MicroConversationEndAt != 0 && microConversationItem.MicroConversationEndAt+int64(5*60) < time.Now().Unix() {
				delete(m.storedConversationList[conversationKey].MicroConversationList, userId)
			}
		}

		// если в диалоге не осталось микро-диалогов
		if len(m.storedConversationList[conversationKey].MicroConversationList) == 0 {
			delete(m.storedConversationList, conversationKey)
		}
		log.Infof("micro conversation list for conversation %v was purged", conversationKey)
	}
}

// закрыт ли микро-диалог
func isMicroConversationClosed(microConversationStartAt int64, microConversationEndAt int64) bool {

	// если ответа не было 24 часа и более
	if microConversationEndAt == 0 && microConversationStartAt+microConversationClosePeriod < time.Now().Unix() {
		return true
	}

	// если диалог закрылся
	if microConversationEndAt != 0 && microConversationEndAt < time.Now().Unix() {
		return true
	}

	return false
}

// --------------------------------------------
// region protected методы для работы с кэшом
// --------------------------------------------

// кэшируем когда пользователь получил сообщение
func (m *useranswertime) doCacheMicroConversationReceiver(conversationKey string, microConversationCreatedByReceiverUserIdList map[int64]bool, senderUserId int64, userId int64, receivedAt int64, senderLocalSentAt string) {

	// получаем микро-диалог для получателя
	receiverMicroConversation := m.getMicroConversation(conversationKey, userId)

	// если есть и не закрыт, то обновляем время старта/время окончания
	if receiverMicroConversation != nil {

		// если ответа не было, то обновляем время старта микро-диалога
		if receiverMicroConversation.IsAnswered == 0 {

			receiverMicroConversation.MicroConversationStartAt = receivedAt
			receiverMicroConversation.MicroConversationLocalStartAt = senderLocalSentAt
			return
		}

		// иначе обновляем время окончания микро-диалога
		receiverMicroConversation.MicroConversationEndAt = receivedAt + microConversationUpdatePeriod
		m.setMicroConversation(conversationKey, userId, receiverMicroConversation)
		return
	}

	// если есть микро-диалог с получетелем
	if microConversationCreatedByReceiverUserIdList[userId] {
		return
	}

	// иначе создаем микро-диалог
	SenderUserIdList := make([]int64, 0)
	SenderUserIdList = append(SenderUserIdList, senderUserId)
	m.setMicroConversation(conversationKey, userId, &MicroConversationStruct{
		SenderUserIdList:              SenderUserIdList,
		IsAnswered:                    0,
		MicroConversationStartAt:      receivedAt,
		MicroConversationLocalStartAt: senderLocalSentAt,
		MicroConversationEndAt:        0,
	})
}

// получаем время ответа пользователя из кэша
func (m *useranswertime) getUserAnswerTime(conversationKey string, userId int64) *UserAnswerTimeStruct {

	m.mx.Lock()
	defer m.mx.Unlock()

	min15Start := system.Min15Start()
	min15Cache, exist := m.storedAnswerTimeListBy15Min[min15Start]
	if !exist {
		return nil
	}

	conversation, exist := min15Cache.ConversationList[conversationKey]
	if !exist {
		return nil
	}

	// проходим по списку пользователей
	for _, v := range conversation.UserAnswerTimeList {

		// если нашли нужного пользователя
		if v.UserID == userId {
			return v
		}
	}

	return nil
}

// добавляем время ответа пользователя из кэша
func (m *useranswertime) setUserAnswerTime(spaceId int64, conversationKey string, userId int64, answerTime int64) int64 {

	m.mx.Lock()
	defer m.mx.Unlock()

	min15Start := system.Min15Start()
	min15Cache, exist := m.storedAnswerTimeListBy15Min[min15Start]
	if !exist {

		min15Cache = &AnswerTimeStruct{
			SpaceId:          spaceId,
			ConversationList: make(map[string]*ConversationAnswerTimeStruct),
		}
		m.storedAnswerTimeListBy15Min[min15Start] = min15Cache
	}

	conversationItem, exist := min15Cache.ConversationList[conversationKey]
	if !exist {

		conversationItem = &ConversationAnswerTimeStruct{
			UserAnswerTimeList: make([]*UserAnswerTimeStruct, 0),
		}
		min15Cache.ConversationList[conversationKey] = conversationItem
	}

	answeredAt := functions.GetCurrentTimeStamp()
	userAnswerTime := &UserAnswerTimeStruct{
		UserID:     userId,
		AnswerTime: answerTime,
		AnsweredAt: answeredAt,
	}
	conversationItem.UserAnswerTimeList = append(conversationItem.UserAnswerTimeList, userAnswerTime)

	return answeredAt
}

// получаем микро-диалог из кэша
func (m *useranswertime) getMicroConversation(conversationKey string, userId int64) *MicroConversationStruct {

	m.mx.Lock()
	defer m.mx.Unlock()

	conversation, exist := m.storedConversationList[conversationKey]
	if !exist {
		return nil
	}

	microConversation, exist := conversation.MicroConversationList[userId]
	if !exist {
		return nil
	}

	if isMicroConversationClosed(microConversation.MicroConversationStartAt, microConversation.MicroConversationEndAt) {
		return nil
	}

	return microConversation
}

// обновляем микро-диалог в кэше
func (m *useranswertime) setMicroConversation(conversationKey string, userId int64, microConversation *MicroConversationStruct) {

	m.mx.Lock()
	defer m.mx.Unlock()

	_, exist := m.storedConversationList[conversationKey]
	if !exist {

		conversation := &ConversationListStruct{
			MicroConversationList: make(map[int64]*MicroConversationStruct),
		}
		m.storedConversationList[conversationKey] = conversation
	}

	m.storedConversationList[conversationKey].MicroConversationList[userId] = microConversation
}

// удаляем микро-диалог в кэше
func (m *useranswertime) deleteMicroConversation(conversationKey string, userId int64) {

	m.mx.Lock()
	defer m.mx.Unlock()

	_, exist := m.storedConversationList[conversationKey]
	if !exist {
		return
	}

	_, exist = m.storedConversationList[conversationKey].MicroConversationList[userId]
	if !exist {
		return
	}

	delete(m.storedConversationList[conversationKey].MicroConversationList, userId)
}

// --------------------------------------------
// region методы пакета, работающие с изоляцией
// --------------------------------------------

// PushConversationAnswerState добавляет время ответа на сообщение и время последнего ответа в агрегатор
func PushConversationAnswerState(isolation *Isolation.Isolation, conversationKey string, senderUserId int64, receiverUserIdList []int64, sentAt int64, localSentAt string) (
	int64,
	string,
	int64,
	int64,
	int64,
	int64,
	error,
) {

	mAnswerTime := leaseUserAnswerTime(isolation)
	if mAnswerTime == nil {
		return isolation.GetCompanyId(), "", 0, 0, 0, 0, fmt.Errorf("isolation doesn't have user_answer_time instance")
	}

	spaceId, conversationKey, answerTime, createdAt, microConversationStartAt,
		microConversationEndAt, err := mAnswerTime.pushConversationAnswerState(
		isolation.GetCompanyId(),
		conversationKey,
		senderUserId,
		receiverUserIdList,
		sentAt,
		localSentAt,
	)

	return spaceId, conversationKey, answerTime, createdAt, microConversationStartAt, microConversationEndAt, err
}

// PushForReceivers добавляет время получения сообщений в агрегатор
func PushForReceivers(isolation *Isolation.Isolation, conversationKey string, senderUserId int64, receiverUserIdList []int64, sentAt int64, localSentAt string) error {

	mAnswerTime := leaseUserAnswerTime(isolation)
	if mAnswerTime == nil {
		return fmt.Errorf("isolation doesn't have user_answer_time instance")
	}

	mAnswerTime.pushForReceivers(conversationKey, senderUserId, receiverUserIdList, sentAt, localSentAt)
	return nil
}

// CloseMicroConversation закрывает микро-диалог в агрегаторе
func CloseMicroConversation(isolation *Isolation.Isolation, conversationKey string, senderUserId int64, receiverUserIdList []int64) error {

	mAnswerTime := leaseUserAnswerTime(isolation)
	if mAnswerTime == nil {
		return fmt.Errorf("isolation doesn't have user_answer_time instance")
	}

	mAnswerTime.closeMicroConversation(conversationKey, senderUserId, receiverUserIdList)
	return nil
}
