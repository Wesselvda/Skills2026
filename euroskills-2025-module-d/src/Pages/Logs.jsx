import React, { useEffect, useState } from "react";
import { Link, useParams } from "react-router";
import { apiCall } from "../Helpers/ApiHelper";
import { formatDateTime } from "../Helpers/FormatHelper";

const Logs = () => {
    const { turbineId } = useParams();
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [errorMessage, setErrorMessage] = useState("");

    useEffect(() => {
        apiCall(`/turbines/${turbineId}/logs`, {
            method: "GET",
            headers: {
                Accept: "application/json",
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to fetch logs");
                }

                return response.json();
            })
            .then((data) => {
                setLogs(data.entries || []);
            })
            .catch((error) => {
                setErrorMessage(error.message);
            })
            .finally(() => {
                setLoading(false);
            });
    }, [turbineId]);

    return (
        <section className="page-section">
            <div className="page-heading">
                <h1>Logs</h1>
                <Link className="btn" to={`/turbines/${turbineId}`}>
                    Back to Turbine
                </Link>
            </div>
            {loading ? (
                <p>Loading...</p>
            ) : errorMessage ? (
                <p className="error">{errorMessage}</p>
            ) : logs.length === 0 ? (
                <p>No logs found.</p>
            ) : (
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Level</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            {logs.map((log, index) => (
                                <tr key={`${log.timestamp}-${index}`}>
                                    <td>{formatDateTime(log.timestamp)}</td>
                                    <td>{log.level}</td>
                                    <td className="log-message">
                                        {log.message}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </section>
    );
};

export default Logs;
