**Title:** Insecure custom auth token and hardcoded master key

**Location in code**
```js
MASTER_KEY: 'secret123'
```

**Description**
Tokens are predictable and rely on a hardcoded secret.

**Why this is a security issue**
Anyone who knows the key can forge tokens.

**OWASP category**
- A02 - Cryptographic Failures
- A07 - Identification & Authentication Failures

**Minimal fix**
Use signed tokens and store secrets in environment variables.
