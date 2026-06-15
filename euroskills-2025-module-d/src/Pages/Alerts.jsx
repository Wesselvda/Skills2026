import React, { useEffect, useState } from "react";
import { apiCall } from "../Helpers/ApiHelper";
import { formatDateTime } from "../Helpers/FormatHelper";

const Alerts = () => {
    const [alerts, setAlerts] = useState([]);
    const [turbines, setTurbines] = useState([]);
    const [loading, setLoading] = useState(true);
    const [errorMessage, setErrorMessage] = useState("");

    useEffect(() => {
        Promise.all([
            apiCall("/alerts", {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
            }),
            apiCall("/turbines", {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
            }),
        ])
            .then(([alertsResponse, turbinesResponse]) => {
                if (!alertsResponse.ok || !turbinesResponse.ok) {
                    throw new Error("Failed to fetch alerts");
                }

                return Promise.all([
                    alertsResponse.json(),
                    turbinesResponse.json(),
                ]);
            })
            .then(([alertsData, turbinesData]) => {
                setAlerts(alertsData);
                setTurbines(turbinesData);
            })
            .catch((error) => {
                setErrorMessage(error.message);
            })
            .finally(() => {
                setLoading(false);
            });
    }, []);

    const turbineNames = turbines.reduce((names, turbine) => {
        names[turbine.id] = turbine.name;
        return names;
    }, {});

    return (
        <section className="page-section">
            <h1>Alerts</h1>
            {loading ? (
                <p>Loading...</p>
            ) : errorMessage ? (
                <p className="error">{errorMessage}</p>
            ) : alerts.length === 0 ? (
                <p>No alerts found.</p>
            ) : (
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Type</th>
                                <th>Turbine</th>
                                <th>Firing</th>
                                <th>Acknowledged</th>
                            </tr>
                        </thead>
                        <tbody>
                            {alerts.map((alert) => (
                                <tr key={alert.id}>
                                    <td>{formatDateTime(alert.timestamp)}</td>
                                    <td>{alert.type}</td>
                                    <td>
                                        {turbineNames[alert.turbineId] ??
                                            `Turbine ${alert.turbineId}`}
                                    </td>
                                    <td>{alert.status}</td>
                                    <td>{alert.acknowledged}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </section>
    );
};

export default Alerts;
