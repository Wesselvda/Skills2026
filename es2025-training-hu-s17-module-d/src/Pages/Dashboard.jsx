import { useContext, useEffect, useMemo, useRef, useState } from "react";
import { Link } from "react-router";
import AppContext from "../Helpers/AppContext";

const CDN_CHART_JS = "https://cdn.jsdelivr.net/npm/chart.js";

const Dashboard = () => {
    const { user } = useContext(AppContext);
    const data = user || {};
    const profile = data.user || data || {};

    const userName = profile.name || profile.fullName || "User";
    const creditBalance = profile.creditBalance ?? profile.credits ?? 0;

    const stats = data.stats || profile.stats || {};
    const recentActivity = data.recentActivity || profile.recentActivity || [];

    const lineRef = useRef(null);
    const doughnutRef = useRef(null);
    const lineChartRef = useRef(null);
    const doughnutChartRef = useRef(null);

    const [chartLoaded, setChartLoaded] = useState(!!window.Chart);

    const chartData = useMemo(() => {
        const activityList = Array.isArray(recentActivity) ? [...recentActivity] : [];

        const normalized = activityList
            .map((activity) => {
                const timestamp = activity.timestamp ? new Date(activity.timestamp) : activity.bookedAt ? new Date(activity.bookedAt) : null;
                const creditsEarned = activity.creditsEarned ?? 0;
                const creditsPaid = activity.creditsPaid ?? 0;
                const delta = activity.type === "session_booked" ? -creditsPaid : creditsEarned - creditsPaid;

                return {
                    timestamp,
                    label: timestamp && !isNaN(timestamp) ? timestamp.toLocaleDateString("en-US", { month: "short", day: "numeric" }) : "Activity",
                    delta,
                };
            })
            .filter((activity) => activity.timestamp && !isNaN(activity.timestamp));

        normalized.sort((left, right) => left.timestamp - right.timestamp);

        const totalDelta = normalized.reduce((sum, activity) => sum + activity.delta, 0);
        const startingBalance = creditBalance - totalDelta;

        const labels = [];
        const values = [];
        let runningBalance = startingBalance;

        if (normalized.length === 0) {
            labels.push("Today");
            values.push(creditBalance);
        } else {
            labels.push(
                normalized[0].timestamp.toLocaleDateString("en-US", {
                    month: "short",
                    day: "numeric",
                }),
            );
            values.push(startingBalance);

            normalized.forEach((activity) => {
                runningBalance += activity.delta;
                labels.push(activity.label);
                values.push(runningBalance);
            });
        }

        return { labels, values };
    }, [creditBalance, recentActivity]);

    const metrics = [
        { label: "Enrolled Courses", value: stats.enrolledCourses ?? 0 },
        { label: "Completed Chapters", value: stats.completedChapters ?? 0 },
        { label: "Total Credits Earned", value: stats.totalCreditsEarned ?? 0 },
    ];

    const currentBalanceText = `${creditBalance} credits`;

    useEffect(() => {
        if (window.Chart) return;

        const script = document.createElement("script");
        script.src = CDN_CHART_JS;
        script.async = true;
        script.onload = () => setChartLoaded(true);
        script.onerror = () => setChartLoaded(false);
        document.body.appendChild(script);

        return () => {
            document.body.removeChild(script);
        };
    }, []);

    const courseStats = useMemo(() => {
        const totalCourses = stats.enrolledCourses ?? 0;
        const completedChapters = stats.completedChapters ?? 0;
        const totalCreditsEarned = stats.totalCreditsEarned ?? 0;
        return { totalCourses, completedChapters, totalCreditsEarned };
    }, [stats.completedChapters, stats.enrolledCourses, stats.totalCreditsEarned]);

    useEffect(() => {
        if (!chartLoaded) return;
        if (!window.Chart) return;

        const Chart = window.Chart;

        if (lineRef.current) {
            const ctx = lineRef.current.getContext("2d");
            if (lineChartRef.current) {
                lineChartRef.current.destroy();
            }
            lineChartRef.current = new Chart(ctx, {
                type: "line",
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: "Credits",
                            data: chartData.values,
                            borderColor: "#1f5faa",
                            backgroundColor: "rgba(31,95,170,0.08)",
                            fill: true,
                            tension: 0.3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                },
            });
        }

        if (doughnutRef.current) {
            const ctx = doughnutRef.current.getContext("2d");
            if (doughnutChartRef.current) {
                doughnutChartRef.current.destroy();
            }
            const completed = courseStats.completedChapters || 0;
            const remaining = Math.max(0, completed ? Math.max(3, completed * 2) - completed : 1);
            doughnutChartRef.current = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Completed", "Remaining"],
                    datasets: [
                        {
                            data: [completed, remaining],
                            backgroundColor: ["#1f5faa", "#e6e6e6"],
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                usePointStyle: true,
                                boxWidth: 10,
                            },
                        },
                    },
                    cutout: "58%",
                },
            });
        }

        return () => {
            if (lineChartRef.current) {
                lineChartRef.current.destroy();
                lineChartRef.current = null;
            }
            if (doughnutChartRef.current) {
                doughnutChartRef.current.destroy();
                doughnutChartRef.current = null;
            }
        };
    }, [
        chartLoaded,
        chartData.labels,
        chartData.values,
        courseStats.completedChapters,
    ]);

    return (
        <div className="dashboard-shell">
            <header className="dashboard-hero">
                <h1>Welcome back, {userName}!</h1>
                <p className="dashboard-balance-label">Current balance: {currentBalanceText}</p>
            </header>

            <section className="dashboard-metrics" aria-label="Dashboard metrics">
                {metrics.map((metric) => (
                    <article className="dashboard-metric-card" key={metric.label}>
                        <div className="dashboard-metric-value">{metric.value}</div>
                        <div className="dashboard-metric-label">{metric.label}</div>
                    </article>
                ))}
            </section>

            <section className="dashboard-charts" aria-label="Dashboard charts">
                <article className="dashboard-chart-card dashboard-chart-card--wide">
                    <h2>Credit Progress (Last 30 Days)</h2>
                    <div className="dashboard-chart-frame dashboard-chart-frame--line">
                        <canvas ref={lineRef} />
                    </div>
                </article>

                <article className="dashboard-chart-card">
                    <h2>Course Completion Status</h2>
                    <div className="dashboard-chart-frame dashboard-chart-frame--doughnut">
                        <canvas ref={doughnutRef} />
                    </div>
                </article>
            </section>

            <section className="dashboard-actions" aria-label="Dashboard navigation">
                <Link className="dashboard-action-button" to="/courses">
                    Browse Courses
                </Link>
                <Link className="dashboard-action-button dashboard-action-button--secondary" to="/mentors">
                    Book Mentor Session
                </Link>
            </section>

            {!chartLoaded && (
                <p className="dashboard-note">
                    Charts will load automatically when Chart.js is available.
                </p>
            )}
        </div>
    );
};

export default Dashboard;
