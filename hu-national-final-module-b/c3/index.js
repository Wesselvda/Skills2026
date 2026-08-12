function twoSum(nums, target) {
  const seen = {};

  for (let i = 0; i < nums.length; i++) {
    const needed = target - nums[i];

    if (seen[needed] !== undefined) {
      return [seen[needed], i];
    }

    seen[nums[i]] = i;
  }

  return [];
}

// Example usage:
console.log(twoSum([2, 7, 11, 15], 9));
// Output: [0, 1]   because nums[0] + nums[1] === 9

console.log(twoSum([3, 2, 4], 6));
// Output: [1, 2]   because nums[1] + nums[2] === 6

console.log(twoSum([-1, 8, 5, 3], 7));
// Output: [0, 1]   because nums[0] + nums[1] === 7
