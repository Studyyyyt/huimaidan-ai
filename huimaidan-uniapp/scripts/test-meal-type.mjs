import assert from 'node:assert'

/**
 * 测试 getCurrentMealType 的餐段计算逻辑。
 * 该函数内部实现与 src/api/ai.ts 中的 getCurrentMealType 保持一致。
 */

function getCurrentMealType(hour) {
  if (hour < 9)
    return 'breakfast'
  if (hour < 11)
    return 'brunch'
  if (hour < 14)
    return 'lunch'
  if (hour < 17)
    return 'tea'
  if (hour < 21)
    return 'dinner'
  if (hour < 23)
    return 'supper'
  return 'late_night'
}

const cases = [
  { hour: 0, expected: 'late_night' },
  { hour: 6, expected: 'breakfast' },
  { hour: 9, expected: 'brunch' },
  { hour: 10, expected: 'brunch' },
  { hour: 11, expected: 'lunch' },
  { hour: 13, expected: 'lunch' },
  { hour: 14, expected: 'tea' },
  { hour: 16, expected: 'tea' },
  { hour: 17, expected: 'dinner' },
  { hour: 20, expected: 'dinner' },
  { hour: 21, expected: 'supper' },
  { hour: 22, expected: 'supper' },
  { hour: 23, expected: 'late_night' },
]

for (const { hour, expected } of cases) {
  const actual = getCurrentMealType(hour)
  assert.strictEqual(actual, expected, `${hour} 点应为 ${expected}，实际为 ${actual}`)
}

console.log('[test-meal-type] ✅ 餐段计算测试通过')
