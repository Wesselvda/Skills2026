# Module C Integration Tests

Automated test suite for validating the Offshore Wind Farm REST API implementation. These tests verify authentication, turbine data retrieval, control actions, alerts, and log parsing functionality.

## Prerequisites

- Node.js (LTS version recommended)
- npm
- Your Module C REST API running locally or deployed
- The External Turbine API mock server running

## Quick Start

1. **Install dependencies**

```bash
npm install
```

2. **Configure environment variables**

Create or edit the `.env` file with your API URLs:

```env
TEST_BASE_URL="http://localhost:8080"
TEST_MOCK_URL="http://localhost:4000"
```

- `TEST_BASE_URL`: The base URL of your Module C REST API
- `TEST_MOCK_URL`: The base URL of the External Turbine API mock server

3. **Run all tests**

```bash
npm test
```

## Running Specific Tests

### Run a specific test file

```bash
npm test -- tests/01_organization.test.js
```

### Run a specific test case

```bash
npm test -- tests/01_organization.test.js -t "test name"
```

## Important Notes

### Sequential Execution

Tests are configured to run sequentially (using the `--runInBand` flag) because they interact with the External Turbine API mock server and depend on its state. Running tests in parallel could cause conflicts.

### Mock Server State

The tests set and reset the mock server state during execution. Ensure the mock server is running and accessible before running the tests.
