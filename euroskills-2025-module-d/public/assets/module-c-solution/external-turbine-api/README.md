# Offshore Wind-Farm External Turbine API — Mock

Small Express.js mock of the **Offshore Wind-Farm External Turbine API**.  
Includes a web control panel to flip scenarios, a static bearer token, Jest tests, and an IntelliJ `test.http`.

## Running with npm

### Prerequisites

- Node.js (LTS version recommended)
- npm

### Installation and Start

```bash
npm install
npm start
```

The mock API will start on **port 4000** by default.

- API endpoint: http://localhost:4000/turbines
- Control panel: http://localhost:4000/control

### Configuration

You can customize the server using environment variables:

- **PORT**: Server port (default: `4000`)
- **BEARER**: Bearer token for authentication (default: `SECRET_TOKEN_123`)

Example with custom configuration:

```bash
PORT=5000 BEARER=MyCustomToken npm start
```

### Testing the API

Test the API endpoint with curl:

```bash
curl -H "Authorization: Bearer SECRET_TOKEN_123" http://localhost:4000/turbines
```

### Control Panel

Open the control panel in your browser to simulate different scenarios:

http://localhost:4000/control

Use the control panel to:

- Return empty data
- Return partial turbine data
- Simulate API errors
- Test different response scenarios

## Running with Docker

### Build the Docker Image

```bash
docker build -t turbine-api-mock:latest .
```

### Run the Container

```bash
docker run --rm -p 4000:4000 \
  -e BEARER=SECRET_TOKEN_123 \
  -e PORT=4000 \
  turbine-api-mock:latest
```

The mock API will be available at:

- API endpoint: http://localhost:4000/turbines
- Control panel: http://localhost:4000/control

### Custom Port Mapping

To run on a different host port (e.g., 5000):

```bash
docker run --rm -p 5000:4000 \
  -e BEARER=SECRET_TOKEN_123 \
  -e PORT=4000 \
  turbine-api-mock:latest
```

Access at: http://localhost:5000/turbines

### Testing the Dockerized API

```bash
curl -H "Authorization: Bearer SECRET_TOKEN_123" http://localhost:4000/turbines
```
