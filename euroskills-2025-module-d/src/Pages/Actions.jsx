import React, { useEffect, useState } from "react";
import { Link, useParams } from "react-router";
import { apiCall } from "../Helpers/ApiHelper";
import { formatDateTime } from "../Helpers/FormatHelper";

const Actions = () => {
    const { turbineId } = useParams();
    const [actions, setActions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [errorMessage, setErrorMessage] = useState("");

    useEffect(() => {
        apiCall(`/turbines/${turbineId}/actions`, {
            method: "GET",
            headers: {
                Accept: "application/json",
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to fetch actions");
                }

                return response.json();
            })
            .then((data) => {
                setActions(data);
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
                <h1>Actions</h1>
                <Link className="btn" to={`/turbines/${turbineId}`}>
                    Back to Turbine
                </Link>
            </div>
            {loading ? (
                <p>Loading...</p>
            ) : errorMessage ? (
                <p className="error">{errorMessage}</p>
            ) : actions.length === 0 ? (
                <p>No actions found.</p>
            ) : (
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Pitch</th>
                                <th>Yaw</th>
                                <th>Timestamp</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            {actions.map((action, index) => (
                                <tr key={`${action.timestamp}-${index}`}>
                                    <td>{action.type}</td>
                                    <td>{action.pitch ?? "-"}</td>
                                    <td>{action.yaw ?? "-"}</td>
                                    <td>{formatDateTime(action.timestamp)}</td>
                                    <td>{action.user}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </section>
    );
};

export default Actions;
