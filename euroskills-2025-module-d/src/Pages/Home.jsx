import React, { useEffect, useRef, useState } from "react";
import { apiCall } from "../Helpers/ApiHelper";
import { Link, useNavigate, useParams } from "react-router";

const Home = () => {
    const [selectedTurbine, setSelectedTurbine] = useState();
    const [turbines, setTurbines] = useState([]);
    const [selectedTurbineStatus, setSelectedTurbineStatus] = useState();
    const [powerHistory, setPowerHistory] = useState([]);
    const navigate = useNavigate();
    const { turbineId } = useParams();

    function getTurbines() {
        apiCall("/turbines", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
        })
            .then((response) => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error("Failed to fetch turbines");
                }
            })
            .then((data) => {
                console.log(data);
                setTurbines(data);
            })
            .catch((error) => {
                console.error("Error fetching turbines:", error);
            });
    }

    function getTurbineStatus(turbineId) {
        return apiCall(`/turbines/${turbineId}/status`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
        })
            .then((response) => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error("Failed to fetch turbine status");
                }
            })
            .then((data) => {
                setSelectedTurbineStatus(data);
                setPowerHistory((previous) =>
                    [...previous, data.powerMw].slice(-20),
                );
                setTurbines((previous) =>
                    previous.map((turbine) =>
                        turbine.id === turbineId
                            ? {
                                  ...turbine,
                                  name: data.name ?? turbine.name,
                                  status: data.status?.value ?? turbine.status,
                              }
                            : turbine,
                    ),
                );
            })
            .catch((error) => {
                console.error("Error fetching turbine status:", error);
                return "unknown";
            });
    }

    useEffect(() => {
        setSelectedTurbineStatus("loading");
        setPowerHistory([]);

        if (selectedTurbine) {
            getTurbineStatus(selectedTurbine.id);

            let interval = setInterval(() => {
                getTurbineStatus(selectedTurbine.id);
            }, 5000);

            return () => clearInterval(interval);
        }
    }, [selectedTurbine]);

    useEffect(() => {
        getTurbines();
    }, []);

    useEffect(() => {
        if (!turbineId) {
            setSelectedTurbine(null);
            return;
        }

        const routeTurbine = turbines.find(
            (turbine) => String(turbine.id) === turbineId,
        );

        if (routeTurbine) {
            setSelectedTurbine((current) =>
                current?.id === routeTurbine.id ? current : routeTurbine,
            );
        } else if (turbines.length > 0) {
            setSelectedTurbine(null);
        }
    }, [turbineId, turbines]);

    function calculateTurbineLocation(turbine) {
        const mapWidth = 800;
        const mapHeight = 600;

        const minLat = Math.min(...turbines.map((t) => t.location.lat));
        const maxLat = Math.max(...turbines.map((t) => t.location.lat));
        const minLng = Math.min(...turbines.map((t) => t.location.lng));
        const maxLng = Math.max(...turbines.map((t) => t.location.lng));

        const latRange = maxLat - minLat;
        const lngRange = maxLng - minLng;

        const x = ((turbine.location.lng - minLng) / lngRange) * mapWidth;
        const y =
            mapHeight -
            ((turbine.location.lat - minLat) / latRange) * mapHeight;
        return { x, y };
    }

    return (
        <>
            <div className="home-layout">
                <section id="map">
                    <div className="map-inner">
                        {turbines.map((turbine) => {
                            const turbineLocation =
                                calculateTurbineLocation(turbine);

                            return (
                                <div
                                    key={turbine.id}
                                    className="turbine"
                                    onClick={() =>
                                        navigate(`/turbines/${turbine.id}`)
                                    }
                                    style={{
                                        left: `${turbineLocation.x}px`,
                                        top: `${turbineLocation.y}px`,
                                    }}
                                >
                                    <TurbineIcon status={turbine.status} />
                                </div>
                            );
                        })}
                    </div>
                </section>
                {selectedTurbine ? (
                    <aside className="turbine-details-wrapper">
                        <div className="turbine-header">
                            <div>{selectedTurbine.name}</div>
                            <div
                                className="close"
                                onClick={() => navigate("/")}
                            >
                                &times;
                            </div>
                        </div>
                        {selectedTurbineStatus === "loading" ? (
                            <div>Loading...</div>
                        ) : (
                            <>
                                <div className="turbine-details">
                                    <div className="top-wrapper">
                                        <div className="stats-list">
                                            <GroupedValueStat
                                                label="Location"
                                                values={[
                                                    {
                                                        label: "Lat",
                                                        value: formatNumber(
                                                            selectedTurbine
                                                                .location.lat,
                                                        ),
                                                    },
                                                    {
                                                        label: "Lon",
                                                        value: formatNumber(
                                                            selectedTurbine
                                                                .location.lng,
                                                        ),
                                                    },
                                                ]}
                                            />
                                            <FieldStat
                                                label="Status"
                                                field={
                                                    selectedTurbineStatus.status
                                                }
                                            />
                                            <GroupedFieldStat
                                                label="Yaw / Pitch"
                                                fields={[
                                                    {
                                                        label: "Yaw",
                                                        field: selectedTurbineStatus.yaw,
                                                        unit: "°",
                                                    },
                                                    {
                                                        label: "Pitch",
                                                        field: selectedTurbineStatus.pitch,
                                                        unit: "°",
                                                    },
                                                ]}
                                            />
                                            <GroupedFieldStat
                                                label="Production Stats"
                                                fields={[
                                                    {
                                                        label: "RPM",
                                                        field: selectedTurbineStatus.rpm,
                                                    },
                                                    {
                                                        label: "Power",
                                                        field: selectedTurbineStatus.powerMw,
                                                        unit: "MW",
                                                    },
                                                    {
                                                        label: "Temp",
                                                        field: selectedTurbineStatus.temperature,
                                                        unit: "°C",
                                                    },
                                                ]}
                                            />
                                            <StaticStat
                                                label="Turbine Freshness"
                                                value={
                                                    selectedTurbineStatus.freshness
                                                }
                                                lastUpdated={
                                                    selectedTurbineStatus.lastUpdated
                                                }
                                            />
                                        </div>
                                        <div className="threed-wrapper">
                                            <TurbineRenderer
                                                selectedTurbineStatus={
                                                    selectedTurbineStatus
                                                }
                                                turbine={selectedTurbine}
                                            />
                                        </div>
                                    </div>
                                    <div className="bottom-wrapper">
                                        <div className="graph-wrapper">
                                            <PowerGraph
                                                history={powerHistory}
                                            />
                                        </div>
                                        <div className="action-wrapper">
                                            <Link
                                                to={`/turbines/${selectedTurbine.id}/actions`}
                                                className="btn"
                                            >
                                                View Actions
                                            </Link>
                                            <Link
                                                to={`/turbines/${selectedTurbine.id}/logs`}
                                                className="btn"
                                            >
                                                View Logs
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </>
                        )}
                    </aside>
                ) : (
                    <aside className="selecttip-wrapper">
                        <h1 className="selecttip">
                            Select a turbine to see its details
                        </h1>
                    </aside>
                )}
            </div>
        </>
    );
};

const formatNumber = (value) =>
    typeof value === "number" ? value.toFixed(4) : "Missing";

const formatFieldValue = (field, unit) => {
    if (!field || field.freshness === "missing" || field.value == null) {
        return "Missing";
    }

    return unit ? `${field.value} ${unit}` : field.value;
};

const formatLastUpdated = (date) => {
    if (!date) {
        return "";
    }

    return new Date(date).toLocaleString();
};

const FreshnessBadge = ({ freshness }) => {
    if (!freshness) {
        return null;
    }

    return (
        <span className={`freshness-badge ${freshness}`}>
            {freshness === "missing" ? "! missing" : freshness}
        </span>
    );
};

const StaticStat = ({ label, value, lastUpdated }) => (
    <div className="stat-wrapper">
        <div className="stat-label">{label}</div>
        <div className="stat-value">{value}</div>
        {lastUpdated && (
            <div className="stat-updated">{formatLastUpdated(lastUpdated)}</div>
        )}
    </div>
);

const GroupedValueStat = ({ label, values }) => (
    <div className="stat-wrapper grouped-stat">
        <div className="stat-label">{label}</div>
        <div className="stat-group">
            {values.map(({ label: valueLabel, value }) => (
                <div className="stat-group-item" key={valueLabel}>
                    <div className="stat-label">{valueLabel}</div>
                    <div className="stat-value">{value}</div>
                </div>
            ))}
        </div>
    </div>
);

const FieldStat = ({ label, field, unit }) => (
    <div
        className={`stat-wrapper ${field?.freshness === "missing" ? "is-missing" : ""}`}
    >
        <div className="stat-label">
            {label}
            <FreshnessBadge freshness={field?.freshness} />
        </div>
        <div className="stat-value">{formatFieldValue(field, unit)}</div>
        <div className="stat-updated">
            {formatLastUpdated(field?.lastUpdated)}
        </div>
    </div>
);

const GroupedFieldStat = ({ label, fields }) => (
    <div
        className={`stat-wrapper grouped-stat ${
            fields.some(({ field }) => field?.freshness === "missing")
                ? "is-missing"
                : ""
        }`}
    >
        <div className="stat-label">{label}</div>
        <div className="stat-group">
            {fields.map(({ label: fieldLabel, field, unit }) => (
                <div className="stat-group-item" key={fieldLabel}>
                    <div className="stat-label">
                        {fieldLabel}
                        <FreshnessBadge freshness={field?.freshness} />
                    </div>
                    <div className="stat-value">
                        {formatFieldValue(field, unit)}
                    </div>
                    <div className="stat-updated">
                        {formatLastUpdated(field?.lastUpdated)}
                    </div>
                </div>
            ))}
        </div>
    </div>
);

const PowerGraph = ({ history }) => {
    const canvasRef = useRef(null);

    useEffect(() => {
        const canvas = canvasRef.current;

        if (!canvas) {
            return;
        }

        const drawChart = () => {
            const context = canvas.getContext("2d");
            const width = canvas.clientWidth;
            const height = canvas.clientHeight;
            const scale = window.devicePixelRatio || 1;

            canvas.width = width * scale;
            canvas.height = height * scale;
            context.setTransform(scale, 0, 0, scale, 0, 0);
            context.clearRect(0, 0, width, height);

            const values = history.map((field) => {
                if (field?.freshness === "missing" || field?.value == null) {
                    return null;
                }

                const value = Number(field.value);
                return Number.isFinite(value) ? value : null;
            });
            const liveValues = values.filter((value) => value != null);
            const minValue = Math.min(...liveValues, 0);
            const maxValue = Math.max(...liveValues, 1);
            const valueRange = maxValue - minValue || 1;
            const maxIndex = Math.max(values.length - 1, 1);
            const padding = 6;
            const chartWidth = width - padding * 2;
            const chartHeight = height - padding * 2;
            const points = values
                .map((value, index) => {
                    if (value == null) {
                        return null;
                    }

                    return {
                        x: padding + (index / maxIndex) * chartWidth,
                        y:
                            padding +
                            chartHeight -
                            ((value - minValue) / valueRange) * chartHeight,
                        value,
                    };
                })
                .filter(Boolean);

            context.strokeStyle = "#2759ec";
            context.lineWidth = 3;

            if (points.length > 0) {
                context.beginPath();
                points.forEach((point, index) => {
                    if (index === 0) {
                        context.moveTo(point.x, point.y);
                    } else {
                        context.lineTo(point.x, point.y);
                    }
                });
                context.stroke();
            }

            points.forEach((point) => {
                context.beginPath();
                context.arc(point.x, point.y, 3, 0, Math.PI * 2);
                context.fillStyle = "#fff";
                context.fill();
                context.strokeStyle = "#2759ec";
                context.lineWidth = 2;
                context.stroke();
            });

            values.forEach((value, index) => {
                if (value != null) {
                    return;
                }

                context.beginPath();
                context.arc(
                    padding + (index / maxIndex) * chartWidth,
                    height - padding,
                    3,
                    0,
                    Math.PI * 2,
                );
                context.fillStyle = "#b42318";
                context.fill();
            });
        };

        drawChart();
    }, [history]);

    return (
        <div className="power-graph">
            <canvas
                className="power-line-chart"
                ref={canvasRef}
            />
        </div>
    );
};

const TurbineIcon = ({ status }) => {
    let color;

    switch (status) {
        case "started":
            color = "#53af53";
            break;
        case "maintenance":
            color = "#ff7b00";
            break;
        default:
            color = "#777";
    }

    return (
        <svg
            fill={color}
            height="800px"
            width="800px"
            version="1.1"
            id="Layer_1"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 355.788 355.788"
        >
            <g>
                <g>
                    <g>
                        <path
                            d="M285.631,179.288l-70.107-60.749c-1.463-6.975-4.886-13.616-10.29-19.021c-0.625-0.625-1.274-1.216-1.933-1.792
                            l-9.789-83.828C192.586,5.975,185.872,0,177.894,0c-7.978,0-14.691,5.975-15.616,13.898l-9.898,84.759
                            c-0.303,0.284-0.608,0.566-0.904,0.861c-5.052,5.052-8.499,11.278-10.103,18.06l-71.216,61.71
                            c-6.028,5.224-7.19,14.135-2.701,20.73c3.02,4.435,7.95,6.875,13.007,6.875c2.462,0,4.955-0.579,7.271-1.79l68.71-35.902
                            l-4.12,168.587h-19.591c-4.971,0-9,4.029-9,9s4.029,9,9,9h91.572c4.971,0,9-4.029,9-9s-4.029-9-9-9h-19.591l-4.104-167.925
                            l67.442,35.24c2.316,1.21,4.809,1.79,7.271,1.79c5.057,0,9.987-2.442,13.008-6.877
                            C292.821,193.423,291.659,184.512,285.631,179.288z M96.687,180.117l46.197-40.03c1.692,4.388,4.2,8.45,7.457,11.995
                            L96.687,180.117z M177.894,35.36l6.243,53.463c-1.897-0.288-3.828-0.438-5.783-0.438c-2.279,0-4.524,0.21-6.722,0.6
                            L177.894,35.36z M170.33,337.79L170.33,337.79l4.242-173.567c1.249,0.123,2.511,0.185,3.783,0.185
                            c1.384,0,2.756-0.08,4.113-0.225l4.244,173.607H170.33z M192.504,140.546c-3.779,3.78-8.805,5.861-14.15,5.861
                            c-5.345,0-10.37-2.082-14.149-5.861c-3.78-3.779-5.862-8.805-5.862-14.15c0-5.345,2.082-10.37,5.862-14.15
                            c3.779-3.779,8.805-5.861,14.149-5.861c5.346,0,10.371,2.082,14.15,5.861C200.307,120.049,200.307,132.744,192.504,140.546z
                            M206.061,152.403c3.297-3.51,5.81-7.493,7.52-11.729l45.52,39.443L206.061,152.403z"
                        />
                        <circle cx="178.355" cy="126.396" r="8.635" />
                    </g>
                </g>
            </g>
        </svg>
    );
};

const TurbineRenderer = ({ selectedTurbineStatus, turbine }) => {
    const turbineRef = useRef(null);
    const turbineRendererRef = useRef(null);

    useEffect(() => {
        if (!turbineRef.current || !window.Turbine) {
            return;
        }

        turbineRendererRef.current = new window.Turbine(
            turbineRef.current,
            "#f0f0f0",
        );
        turbineRendererRef.current.render();

        return () => {
            turbineRendererRef.current?.destroy();
            turbineRendererRef.current = null;
        };
    }, [turbine?.id]);

    useEffect(() => {
        if (
            !turbineRendererRef.current ||
            selectedTurbineStatus === "loading" ||
            !selectedTurbineStatus
        ) {
            return;
        }

        turbineRendererRef.current.updateTurbine(
            selectedTurbineStatus.pitch?.value ?? 0,
            selectedTurbineStatus.yaw?.value ?? 0,
            selectedTurbineStatus.rpm?.value ?? 0,
        );
    }, [selectedTurbineStatus]);

    return (
        <div
            ref={turbineRef}
            id="turbine-container"
            style={{ width: "150px", height: "250px" }}
        ></div>
    );
};

export default Home;
