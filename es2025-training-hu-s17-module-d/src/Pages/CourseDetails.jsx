import { useContext, useEffect, useMemo, useState } from "react";
import { Link, useNavigate, useParams } from "react-router";
import AppContext from "../Helpers/AppContext";
import { completeChapter, getCourse } from "../Helpers/ApiHelper";

const CourseDetails = () => {
    const { courseId } = useParams();
    const navigate = useNavigate();
    const { user, setUser } = useContext(AppContext);
    const [course, setCourse] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [feedback, setFeedback] = useState("");
    const [busyChapterId, setBusyChapterId] = useState(null);

    useEffect(() => {
        if (window.LinkedInShare) {
            window.LinkedInShare.init({
                container: "#linkedin-share-root",
                theme: "light",
                locale: "en-US",
            });
            return;
        }

        const style = document.createElement("link");
        style.rel = "stylesheet";
        style.href = "/assets/third-party/linkedin-share.css";

        const script = document.createElement("script");
        script.src = "/assets/third-party/linkedin-share.js";
        script.async = true;
        script.onload = () => {
            window.LinkedInShare?.init({
                container: "#linkedin-share-root",
                theme: "light",
                locale: "en-US",
            });
        };

        document.head.appendChild(style);
        document.body.appendChild(script);

        return () => {
            style.remove();
            script.remove();
        };
    }, []);

    useEffect(() => {
        getCourse(courseId, (err, data) => {
            if (err) {
                setError(err.message || "Could not load course.");
            } else {
                setCourse(data?.course || null);
            }
            setLoading(false);
        });
    }, [courseId]);

    const progress = useMemo(() => {
        const chapters = course?.chapters || [];
        const completed = chapters.filter((chapter) => chapter.isCompleted).length;
        const total = chapters.length;
        const earnedCredits = chapters.reduce(
            (sum, chapter) => sum + (chapter.isCompleted ? chapter.credits : 0),
            0,
        );

        return {
            completed,
            total,
            earnedCredits,
            totalCredits: course?.totalCredits || chapters.reduce((sum, chapter) => sum + chapter.credits, 0),
        };
    }, [course]);

    function updateUserAfterCompletion(result, chapter) {
        setUser((currentUser) => {
            if (!currentUser) {
                return currentUser;
            }

            const nextUser = { ...currentUser };
            nextUser.user = {
                ...currentUser.user,
                creditBalance: result.newBalance ?? currentUser.user?.creditBalance,
            };
            nextUser.stats = {
                ...currentUser.stats,
                completedChapters: (currentUser.stats?.completedChapters ?? 0) + 1,
                totalCreditsEarned: (currentUser.stats?.totalCreditsEarned ?? 0) + (result.creditsEarned ?? chapter.credits ?? 0),
            };
            nextUser.recentActivity = [
                {
                    type: "chapter_completed",
                    description: `Completed chapter ${chapter.title}`,
                    creditsEarned: result.creditsEarned ?? chapter.credits ?? 0,
                    timestamp: new Date().toISOString(),
                },
                ...(currentUser.recentActivity || []),
            ];

            return nextUser;
        });
    }

    function handleComplete(chapter) {
        setBusyChapterId(chapter.id);
        setFeedback("");

        completeChapter(course.id, chapter.id, (err, data) => {
            setBusyChapterId(null);

            if (err) {
                setFeedback(err.message || "Could not complete chapter.");
                return;
            }

            setCourse((currentCourse) => {
                if (!currentCourse) {
                    return currentCourse;
                }

                return {
                    ...currentCourse,
                    chapters: currentCourse.chapters.map((item) =>
                        item.id === chapter.id
                            ? { ...item, isCompleted: true }
                            : item,
                    ),
                };
            });

            updateUserAfterCompletion(data, chapter);
            setFeedback(data?.message || "Chapter completed.");
        });
    }

    function handleShare(courseTitle, chapterTitle, chapterCredits) {
        const summary = `I just completed ${chapterTitle} in ${courseTitle} and earned ${chapterCredits} credits.`;
        const shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.origin)}&summary=${encodeURIComponent(summary)}`;

        if (window.LinkedInShare?.open) {
            window.LinkedInShare.open({
                url: window.location.href,
                title: courseTitle,
                summary,
                source: "SkillShare Academy",
                tags: ["learning", "webdev", "skills"],
            });
            return;
        }

        window.open(shareUrl, "_blank", "noopener,noreferrer");
    }

    if (loading) {
        return <div className="course-state-panel">Loading course...</div>;
    }

    if (error || !course) {
        return (
            <section className="course-page-shell">
                <div className="course-hero-panel">
                    <h1>Course</h1>
                    <p className="course-feedback course-feedback--error">{error || "Course unavailable."}</p>
                    <button className="course-action course-action--secondary" type="button" onClick={() => navigate("/courses")}>
                        Back to Courses
                    </button>
                </div>
            </section>
        );
    }

    return (
        <section className="course-page-shell">
            <div className="course-hero-panel course-hero-panel--detail">
                <Link className="course-back-link" to="/courses">Back to Courses</Link>
                <h1>{course.title}</h1>
                <p>{course.description}</p>

                <div className="course-progress-grid">
                    <div className="course-progress-box">
                        <span>Chapter Progress</span>
                        <strong>{progress.completed} of {progress.total} chapters completed</strong>
                        <div className="course-progress-track">
                            <div className="course-progress-fill" style={{ width: `${progress.total ? (progress.completed / progress.total) * 100 : 0}%` }} />
                        </div>
                    </div>
                    <div className="course-progress-box">
                        <span>Credit Progress</span>
                        <strong>{progress.earnedCredits} of {progress.totalCredits} credits earned</strong>
                        <div className="course-progress-track">
                            <div className="course-progress-fill" style={{ width: `${progress.totalCredits ? (progress.earnedCredits / progress.totalCredits) * 100 : 0}%` }} />
                        </div>
                    </div>
                </div>

                {feedback && <p className="course-feedback">{feedback}</p>}
            </div>

            <div className="chapter-list">
                {(course.chapters || []).map((chapter) => (
                    <article className="chapter-card" key={chapter.id}>
                        <h2>{chapter.title}</h2>
                        <p>{chapter.description}</p>

                        <div className="chapter-view-button">
                            <span>{chapter.credits} Credits</span>
                        </div>

                        <div className="chapter-actions">
                            <button type="button" className="chapter-view-button" disabled>
                                View Chapter
                            </button>

                            {chapter.isCompleted ? (
                                <>
                                    <div className="chapter-complete-label">Chapter Completed</div>
                                    <button
                                        type="button"
                                        className="chapter-share-button"
                                        onClick={() => handleShare(course.title, chapter.title, chapter.credits)}
                                    >
                                        Share Achievement
                                    </button>
                                </>
                            ) : (
                                <button
                                    type="button"
                                    className="chapter-complete-button"
                                    onClick={() => handleComplete(chapter)}
                                    disabled={busyChapterId === chapter.id}
                                >
                                    {busyChapterId === chapter.id ? "Updating..." : "Mark as Completed"}
                                </button>
                            )}
                        </div>
                    </article>
                ))}
            </div>

            <div id="linkedin-share-root" />
        </section>
    );
};

export default CourseDetails;