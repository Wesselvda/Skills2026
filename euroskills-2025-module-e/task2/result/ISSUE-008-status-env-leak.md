**Title:** Sensitive data exposure in status endpoint

**Location in code**
```js
env: process.env,
trace: e.stack
```

**Description**
The endpoint exposes environment variables and stack traces.

**Why this is a security issue**
Secrets may leak.

**OWASP category**
- A02 - Cryptographic Failures
- A05 - Security Misconfiguration

**Minimal fix**
Return only a simple health status.
