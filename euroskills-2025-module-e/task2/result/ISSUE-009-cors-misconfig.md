**Title:** Overly permissive CORS and unnecessary headers

**Location in code**
```js
'Access-Control-Allow-Origin': '*',
'X-Powered-By': 'WindFarm/Raw'
```

**Description**
CORS allows all origins and exposes implementation details.

**Why this is a security issue**
Enables cross-site abuse and fingerprinting.

**OWASP category**
- A05 - Security Misconfiguration

**Minimal fix**
Restrict CORS and remove X-Powered-By.
