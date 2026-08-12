function getTopCourses(enrollments, topN) {
  const counts = {};

  for (const enrollment of enrollments) {
    if (counts[enrollment.course]) {
      counts[enrollment.course] = counts[enrollment.course] + 1;
    } else {
      counts[enrollment.course] = 1;
    }
  }

  const courses = [];

  for (const course in counts) {
    courses.push({ course: course, count: counts[course] });
  }

  courses.sort((a, b) => b.count - a.count);

  return courses.slice(0, topN);
}

// Example usage:
const enrollments = [
  { course: "JavaScript Basics", student: "Alice" },
  { course: "JavaScript Basics", student: "Bob" },
  { course: "Advanced CSS", student: "Charlie" },
  { course: "Advanced CSS", student: "Eve" },
  { course: "React for Beginners", student: "Frank" },
  { course: "Advanced CSS", student: "Dave" },
  { course: "Laravel for Beginners", student: "Grace" },
];

console.log(getTopCourses(enrollments, 3));
// Output: [{ course: 'Advanced CSS', count: 3 }, { course: 'JavaScript Basics', count: 2 }, { course: 'React for Beginners', count: 1 }]

const enrollments2 = [
  { course: "Python 101", student: "Ivy" },
  { course: "Python 101", student: "Jack" },
  { course: "Python 101", student: "Kim" },
  { course: "Python 101", student: "Leo" },
  { course: "Data Structures", student: "Mia" },
  { course: "Data Structures", student: "Noah" },
  { course: "Web Security", student: "Olivia" },
];

console.log(getTopCourses(enrollments2, 2));
// Output: [{ course: 'Python 101', count: 4 }, { course: 'Data Structures', count: 2 }]
