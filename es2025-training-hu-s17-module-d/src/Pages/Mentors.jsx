import { useContext, useEffect, useRef, useState } from "react";
import AppContext from "../Helpers/AppContext";
import { getMentorSessions, bookMentorSession, getUser } from "../Helpers/ApiHelper";

const Mentors = () => {
  const { user, setUser } = useContext(AppContext);
  const [sessions, setSessions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [busyId, setBusyId] = useState(null);
  const pollingRef = useRef(null);

  useEffect(() => {
    getMentorSessions((err, data) => {
      if (err) {
        setError(err.message || "Could not load mentor sessions.");
      } else {
        setError("");
        setSessions(data?.sessions || []);
      }
      setLoading(false);
    });
  }, []);

  function startPolling() {
    if (pollingRef.current) return;
    pollingRef.current = setInterval(() => {
      getUser((err, data) => {
        if (!err && setUser) setUser(data);
      });
    }, 30000);
  }

  useEffect(() => {
    startPolling();
    return () => {
      if (pollingRef.current) {
        clearInterval(pollingRef.current);
        pollingRef.current = null;
      }
    };
  }, []);

  function handleBook(session) {
    setBusyId(session.id);
    setError("");

    bookMentorSession(session.id, (err, data) => {
      setBusyId(null);

      if (err) {
        setError(err.message || "Could not book session.");
        return;
      }

      const booking = data?.booking || {
        id: `b-${Date.now()}`,
        sessionId: session.id,
        status: "pending",
        mentorName: session.mentorName || (session.mentor && session.mentor.name),
        time: session.sessionDate || session.time,
        credits: session.creditCost ?? session.credits,
      };

      if (setUser) {
        setUser((prev) => {
          if (!prev) return prev;
          const next = { ...prev };
          next.sessions = next.sessions ? [...next.sessions, booking] : [booking];
          return next;
        });
      }

      startPolling();
    });
  }

  return (
    <div className="course-page-shell">
      <section className="course-hero-panel mentor-hero">
        <h1>MENTOR SESSION BOOKING</h1>
        <p>Book one-on-one sessions with expert mentors to accelerate your learning</p>

        <div className="mentor-balance">
          <strong>Your Current Balance: {user?.user?.creditBalance ?? user?.creditBalance ?? 0} Credits</strong>
          <p style={{marginTop:8}}>Sessions are automatically checked for confirmations every 30 seconds</p>
        </div>

        {error && <p className="course-feedback course-feedback--error">{error}</p>}
      </section>

      <section className="mentors-section">
        <h2 style={{textTransform:'uppercase', marginBottom:12}}>Available Sessions</h2>

        <div className="mentors-list" aria-label="Mentor sessions">
        {loading ? (
          <article className="course-state-panel">Loading sessions...</article>
        ) : sessions.length ? (
          sessions.map((s) => (
            <article className="mentor-card" key={s.id}>
              <h2>{`${s.mentorName || (s.mentor && s.mentor.name) || 'Mentor'}`}</h2>
              <div style={{marginTop:6}}>
                <strong style={{display:'block'}}>Expertise</strong>
                <p className="course-description" style={{marginTop:6}}>{s.expertise || s.topic || '—'}</p>
              </div>

              <p className="mentor-experience" style={{marginTop:8}}>{
                (s.experienceLevel === 'senior' && 'Senior Developer with 8+ years experience') ||
                (s.experienceLevel === 'mid' && 'Mid-level Developer with 3-6 years experience') ||
                (s.experienceLevel === 'junior' && 'Junior Developer with 0-2 years experience') ||
                ''
              }</p>

              <div className="mentor-divider" />

              <div className="mentor-meta-grid">
                <div className="course-meta-box">
                  <span>Date</span>
                  <strong>{s.sessionDate ? new Date(s.sessionDate).toLocaleDateString(undefined, {weekday:'long', year:'numeric', month:'long', day:'numeric'}) : '—'}</strong>
                </div>
                <div className="course-meta-box">
                  <span>Time</span>
                  <strong>{s.sessionDate ? new Date(s.sessionDate).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : '—'}</strong>
                </div>
                <div className="course-meta-box">
                  <span>Duration</span>
                  <strong>{s.durationMinutes || 60} minutes</strong>
                </div>
                <div className="course-meta-box">
                  <span>Cost</span>
                  <strong>{s.creditCost ?? s.credits} credits</strong>
                </div>
              </div>

              <div className={`mentor-actions ${user?.sessions?.some(b => b.sessionId === s.id) ? 'mentor-actions--column' : ''}`}>
                <button className="mentor-profile-button" disabled>View Profile</button>
                {user?.sessions?.some(b => b.sessionId === s.id) ? (
                  <button className="mentor-booked">Session Booked</button>
                ) : (
                  <button className={`mentor-book-button ${s.isAvailable ? 'mentor-book-button--available' : 'mentor-book-button--unavailable'}`} onClick={() => handleBook(s)} disabled={!s.isAvailable || busyId === s.id}>{busyId === s.id ? 'Booking...' : (!s.isAvailable ? 'Not available' : 'Book sessoin')}</button>
                )}
              </div>
            </article>
          ))
        ) : (
          <article className="course-state-panel">No sessions available.</article>
        )}
        </div>
      </section>

      <section className="course-hero-panel" style={{marginTop:20}}>
        <h2>Your Bookings</h2>
        <p>Pending bookings will be updated automatically.</p>

        {!user?.sessions || !user.sessions.length ? (
          <p className="course-state-panel">You have no bookings.</p>
        ) : (
          user.sessions.map((b) => {
            const mentorName = b.mentorName || (b.mentor && b.mentor.name) || b.mentor?.name || 'Mentor';
            const timeVal = b.time || b.sessionDate || b.date || b.scheduledAt || null;
            const statusVal = b.status || b.state || b.bookingStatus || 'pending';
            const creditsVal = b.credits || b.creditCost || b.cost || '—';

            return (
              <article className="course-card" key={b.id}>
                <h3>{mentorName}</h3>
                <div className="course-meta-grid">
                  <div className="course-meta-box">
                    <span>When</span>
                    <strong>{timeVal ? new Date(timeVal).toLocaleString() : '—'}</strong>
                  </div>
                  <div className="course-meta-box">
                    <span>Status</span>
                    <strong>{(statusVal || 'pending').toUpperCase()}</strong>
                  </div>
                  <div className="course-meta-box">
                    <span>Cost</span>
                    <strong>{creditsVal} credits</strong>
                  </div>
                </div>
              </article>
            );
          })
        )}
      </section>
    </div>
  );
};

export default Mentors;
