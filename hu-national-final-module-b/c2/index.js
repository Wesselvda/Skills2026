function chunkArray(array, chunkSize) {
  const chunks = [];

  for (let i = 0; i < array.length; i += chunkSize) {
    chunks.push(array.slice(i, i + chunkSize));
  }

  return chunks;
}

// Example usage:
console.log(chunkArray([1, 2, 3, 4, 5], 2)); // Output: [[1, 2], [3, 4], [5]]
console.log(chunkArray([1, 2, 3, 4, 5], 3)); // Output: [[1, 2, 3], [4, 5]]
console.log(chunkArray([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 4)); // Output: [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10]]
