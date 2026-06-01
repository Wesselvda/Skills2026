import { useContext, useEffect, useMemo, useState } from "react";
import { Link } from "react-router";
import AppContext from "../Helpers/AppContext";
import { enrollInCourse, getCourses } from "../Helpers/ApiHelper";

const Courses = () => {
    const { user } = useContext(AppContext);
    const [courses, setCourses] = useState([]);
    const [query, setQuery] = useState("");
    const [difficulty, setDifficulty] = useState("all");
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [notice, setNotice] = useState("");
    const [busyId, setBusyId] = useState(null);

    useEffect(() => {
        getCourses((err, data) => {
            if (err) {
                setError(err.message || "Could not load courses.");
            } else {
                setError("");
                setCourses(data?.courses);
            }
            setLoading(false);
        });
    }, []);

    const filteredCourses = useMemo(() => {
        return courses.filter((course) => {
            const matchesQuery =
                !query ||
                `${course.title} ${course.description}`
                    .toLowerCase()
                    .includes(query.toLowerCase());
            const matchesDifficulty =
                difficulty === "all" || course.difficulty === difficulty;

            return matchesQuery && matchesDifficulty;
        });
    }, [courses, difficulty, query]);

    function updateCourseEnrollment(courseId) {
        setCourses((currentCourses) =>
            currentCourses.map((course) =>
                course.id === courseId
                    ? { ...course, isEnrolled: true }
                    : course,
            ),
        );
    }

    function handleEnroll(course) {
        setBusyId(course.id);
        setNotice("");

        enrollInCourse(course.id, (err, data) => {
            setBusyId(null);

            if (err) {
                setNotice(err.message || "Could not enroll in course.");
                return;
            }

            updateCourseEnrollment(course.id);
            setNotice(data?.message || "Successfully enrolled in course.");
        });
    }

    return (
        <div className="course-page-shell">
            <section className="course-hero-panel">
                <h1>Course Catalog</h1>
                <p>Discover and enroll in courses to advance your skills.</p>

                <div className="course-filters">
                    <input
                        type="search"
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder="Search courses by title or description..."
                    />
                    <select
                        value={difficulty}
                        onChange={(event) => setDifficulty(event.target.value)}
                    >
                        <option value="all">All Difficulties</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>

                {notice && <p className="course-feedback">{notice}</p>}
                {error && !notice && (
                    <p className="course-feedback course-feedback--error">
                        {error}
                    </p>
                )}
            </section>

            <section className="course-grid" aria-label="Course list">
                {loading ? (
                    <article className="course-state-panel">
                        Loading courses...
                    </article>
                ) : filteredCourses.length ? (
                    filteredCourses.map((course) => (
                        <article className="course-card" key={course.id}>
                            <h2>{course.title}</h2>
                            <p className="course-description">
                                {course.description}
                            </p>

                            <div className="course-meta-grid">
                                <div className="course-meta-box">
                                    <span>Difficulty</span>
                                    <strong>{course.difficulty}</strong>
                                </div>
                                <div className="course-meta-box">
                                    <span>Chapters</span>
                                    <strong>{course.totalChapters}</strong>
                                </div>
                                <div className="course-meta-box">
                                    <span>Total Credits</span>
                                    <strong>
                                        {course.totalCredits} credits
                                    </strong>
                                </div>
                            </div>

                            {course.isEnrolled ? (
                                <Link
                                    className="course-action course-action--primary"
                                    to={`/courses/${course.id}`}
                                >
                                    Continue Learning
                                </Link>
                            ) : (
                                <button
                                    type="button"
                                    className="course-action course-action--secondary"
                                    onClick={() => handleEnroll(course)}
                                    disabled={busyId === course.id}
                                >
                                    {busyId === course.id
                                        ? "Enrolling..."
                                        : "Enroll Now"}
                                </button>
                            )}
                        </article>
                    ))
                ) : (
                    <article className="course-state-panel">
                        No courses match your search.
                    </article>
                )}
            </section>

            <footer className="course-footer-note">
                Showing course access for {user?.user?.name || "User"}.
            </footer>
        </div>
    );
};

export default Courses;
