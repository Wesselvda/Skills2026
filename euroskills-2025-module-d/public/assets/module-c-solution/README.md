# Module C Solution – Offshore Wind Farm API

This folder contains a complete working solution for Module C (REST API development), which is provided as the backend for Module D (Interactive Frontend) development. The solution includes a Node.js/Express API that manages offshore wind turbines, alerts, actions, and logs, along with an external turbine API mock service.

## System Overview

The system consists of three main components:

1. **Module C Backend API** (port 8080) - The main REST API for turbine management
2. **External Turbine API Mock** (port 4000) - Simulates the external data source for turbine information
3. **MySQL Database** (port 3306) - Data persistence layer
4. **phpMyAdmin** (port 8081) - Database management interface (Docker only)

## Quick Start with Docker

Docker Compose is the recommended way to run the entire system, as it automatically manages all services, networking, and dependencies.

### Prerequisites

- Docker
- Docker Compose

### Starting the Services

1. Navigate to this directory:

   ```bash
   cd assets/module-c-solution
   ```

2. Start all services:

   ```bash
   docker compose up
   ```

   Or run in detached mode:

   ```bash
   docker compose up -d
   ```

3. Wait for all health checks to pass. The services will be available at:
   - **Module C API**: http://localhost:8080
   - **External Turbine API**: http://localhost:4000 (Do not call this from your frontend)
   - **External Turbine API Control Panel**: http://localhost:4000/control
   - **phpMyAdmin**: http://localhost:8081
   - **MySQL**: localhost:3306

### Stopping the Services

```bash
docker compose down
```

To remove volumes (database data) as well:

```bash
docker compose down -v
```

## Running Locally without Docker

If you prefer to run the services locally without Docker, follow these instructions.

### Prerequisites

- Node.js (LTS version recommended)
- npm
- MySQL 8.0 or higher

### 1. Set Up MySQL Database

First, ensure MySQL is installed and running on your system.

Create the database:

```bash
mysql -u root -p
```

Then execute:

```sql
CREATE DATABASE windfarm;
```

Import the seed data:

```bash
mysql -u root -p windfarm < backend/seed.sql
```

### 2. Set Up the External Turbine API

The external turbine API is a mock service that simulates an external data source.

1. Navigate to the external-turbine-api directory:

   ```bash
   cd external-turbine-api
   ```

2. Install dependencies:

   ```bash
   npm install
   ```

3. Configure environment variables (optional):

   ```bash
   export PORT=4000
   export BEARER=SECRET_TOKEN_123
   ```

4. Start the service:

   ```bash
   npm start
   ```

5. Verify it's running:
   - API endpoint: http://localhost:4000/turbines
   - Control panel: http://localhost:4000/control

   Test with curl:

   ```bash
   curl -H "Authorization: Bearer SECRET_TOKEN_123" http://localhost:4000/turbines
   ```

### 3. Set Up the Module C Backend API

1. Open a new terminal and navigate to the backend directory:

   ```bash
   cd backend
   ```

2. Install dependencies:

   ```bash
   npm install
   ```

3. Configure environment variables:

   Edit the `.env` file or create one with the following content:

   ```env
   # MySQL connection
   DATABASE_URL="mysql://root:root@localhost:3306/windfarm"

   # Server
   PORT=8080
   JWT_SECRET="superlongrandomjwtsecretchange_me"
   TOKEN_TTL_SECONDS=86400

   # External mock API
   EXTERNAL_API_BASE_URL="http://localhost:4000"
   EXTERNAL_API_TOKEN="SECRET_TOKEN_123"
   EXTERNAL_API_TIMEOUT_MS=3000
   ```

   **Important**: Adjust the `DATABASE_URL` if your MySQL credentials differ. The format is:

   ```
   mysql://username:password@host:port/database
   ```

4. Set up Prisma and generate the client:

   ```bash
   npm run prisma:generate
   ```

5. Push the database schema:

   ```bash
   npm run prisma:push
   ```

6. Start the server:

   ```bash
   npm start
   ```

7. Verify it's running at http://localhost:8080

### Local Setup Verification

Test the Module C API:

```bash
# Health check
curl http://localhost:8080/__health

# Get turbines (public endpoint)
curl http://localhost:8080/turbines

# Login as admin
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"alice","password":"alice12345"}'
```

## API Documentation

For complete API documentation, refer to the OpenAPI specification in `module-c.openapi.yaml`.

## External Turbine API Control Panel

The external turbine API includes a web-based control panel for simulating different scenarios:

http://localhost:4000/control

Use this panel to:

- Change turbine data freshness (live, cached, missing)
- Simulate different turbine states
- Test error scenarios
- Verify your frontend handles all data states correctly
