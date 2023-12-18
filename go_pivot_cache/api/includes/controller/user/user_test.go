package user_test

import (
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"go_pivot_cache/api/includes/controller/user"
	"google.golang.org/grpc/status"
	"testing"
)

// проверяем что можно получить пользователя
func TestOkUsersGetInfo(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод user.getInfo отдаст пользователя")

	// добавляем пользователя в базу
	var userId int64 = 1
	_ = addMockUser(userId)
	defer deleteUser(userId)

	// получаем и проверяем
	response, _ := user.GetInfo(userId)
	I.AssertEqual(userId, response.UserId)

	// получаем его еще раз
	response, _ = user.GetInfo(userId)

	// проверяем что вернулась та же инфа, так как пользователь закэширован
	I.AssertEqual(userId, response.UserId)
}

// проверяем что можно получить пользователя
func TestOkUsersGetListInfo(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод user.getListInfo отдаст пользователей")

	// добавляем пользователя в базу
	var userId int64 = 1
	_ = addMockUser(userId)
	defer deleteUser(userId)
	userIdList := make([]int64, 1)
	userIdList[0] = userId

	// получаем и проверяем
	response, _ := user.GetListInfo(userIdList)
	I.AssertEqual(userId, response.UserList[0].UserId)

	// получаем его еще раз
	response, _ = user.GetListInfo(userIdList)

	// проверяем что вернулась та же инфа, так как пользователь закэширован
	I.AssertEqual(userId, response.UserList[0].UserId)
}

// проверяем что метод отдаст 901 если передать не существующий user_id
func TestErrorUsersGetInfoIsUnknownUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод users.getInfo отдаст 901 если передать не существующий user_id")

	var userId int64 = 999999

	// вызываем метод и проверяем ответ
	_, err := user.GetInfo(userId)
	I.AssertEqual(901, int(status.Code(err)))
}

// проверяем что метод отдаст пустой список
func TestErrorUsersGetInfoListIsUnknownUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод users.getListInfo отдаст ошибку 903, если передать не существующий user_id")

	var userId int64 = 999999
	userIdList := make([]int64, 1)
	userIdList[0] = userId

	// вызываем метод и проверяем ответ
	_, err := user.GetListInfo(userIdList)
	I.AssertEqual(903, int(status.Code(err)))
}

// проверяем что метод отдаст 401 если передать некорректный user_id
func TestErrorUsersGetInfoIsBadUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод users.getInfo отдаст 401 если передать не существующий user_id")

	var userId int64 = 0

	// вызываем метод и проверяем ответ
	_, err := user.GetInfo(userId)
	I.AssertEqual(401, int(status.Code(err)))
}

// проверяем что метод отдаст 401 если передать некорректный user_id
func TestErrorUsersGetListInfoIsBadUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод users.getListInfo отдаст 401 если передать не существующие user_ids в списке")

	var userId int64 = 0
	userIdList := make([]int64, 1)
	userIdList[0] = userId

	// вызываем метод и проверяем ответ
	_, err := user.GetListInfo(userIdList)
	I.AssertEqual(401, int(status.Code(err)))
}
