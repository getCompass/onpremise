package pivot_user

import (
	"fmt"
	"math"
)

const dbKey = "pivot_user"

// получаем shardKey на основе userID
func getShardKey(userID int64) string {

	return fmt.Sprintf("%s_%d0m", dbKey, int64(math.Ceil(float64(userID)/10000000)))
}
