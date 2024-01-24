package session_test

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"go_pivot_cache/api/includes/controller/session"
	"testing"
)

// тест для метода session.GetInfo, что он нормально отработает
func TestOkSessionGetInfo(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод session.GetInfo отдаст сессию")

	var sessionUniq = functions.GenerateUuid()
	var userId int64 = 324
	var shardId = "10m"
	var tableId = "1"
	var ipAddress = "192.168.1.1"

	// проверяем что сессии нет в кэше
	I.AssertEqual(false, isSessionInCache(sessionUniq))

	_ = addMockActiveUserSession(sessionUniq, userId, ipAddress)
	_ = addMockUser(userId)

	getUserId, err := session.GetInfo(sessionUniq, shardId, tableId)
	I.AssertEqual(nil, err)
	I.AssertEqual(userId, getUserId)

	// проверяем что сессия есть в кэше
	I.AssertEqual(true, isSessionInCache(sessionUniq))
}

// тест для метода session.DeleteByUserId, что он нормально отработает
func TestOkSessionDeleteByUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод session.DeleteByUserId удалит сессию по user_id")

	var sessionUniq = functions.GenerateUuid()
	var userId int64 = 324
	var shardId = "10m"
	var tableId = "1"
	var ipAddress = "192.168.1.1"

	_ = addMockActiveUserSession(sessionUniq, userId, ipAddress)
	_ = addMockUser(userId)

	getUserId, err := session.GetInfo(sessionUniq, shardId, tableId)
	I.AssertEqual(nil, err)
	I.AssertEqual(userId, getUserId)

	// проверяем что сессия есть в кэше
	I.AssertEqual(true, isSessionInCache(sessionUniq))

	// удаляем сессию по user_id
	session.DeleteByUserId(userId)

	I.AssertEqual(false, isSessionInCache(sessionUniq))
}

// тест для метода session.DeleteBySessionUniq, что он нормально отработает
func TestOkSessionDeleteBySessionUniq(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод session.DeleteBySessionUniq удалит сессию по session_uniq")

	var sessionUniq = functions.GenerateUuid()
	var userId int64 = 324
	var shardId = "10m"
	var tableId = "1"
	var ipAddress = "192.168.1.1"

	_ = addMockActiveUserSession(sessionUniq, userId, ipAddress)
	_ = addMockUser(userId)

	getUserId, err := session.GetInfo(sessionUniq, shardId, tableId)
	I.AssertEqual(nil, err)
	I.AssertEqual(userId, getUserId)

	// проверяем что сессия есть в кэше
	I.AssertEqual(true, isSessionInCache(sessionUniq))

	session.DeleteBySessionUniq(sessionUniq)
	I.AssertEqual(false, isSessionInCache(sessionUniq))
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// получаем статус сессии в кэше ли она
func isSessionInCache(sessionUniq string) bool {

	isExist := session.GetSessionCacheStatus(sessionUniq)

	return isExist
}
