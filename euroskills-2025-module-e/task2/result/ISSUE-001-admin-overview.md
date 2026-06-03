**Title:** Unauthenticated access to admin overview exposes internal state

**Location in code**

`handlers/admin.js`:
```js
return send(res, 200, { turbines: state.turbines, accounts: state.energyAccounts });
```

**Description**
The `/control/overview` endpoint is accessible without authentication.

**Why this is a security issue**
Anyone can read turbine IDs and account balances.

**OWASP category**
- A01 - Broken Access Control

**Minimal fix**
Require a valid authenticated token and an admin role.
