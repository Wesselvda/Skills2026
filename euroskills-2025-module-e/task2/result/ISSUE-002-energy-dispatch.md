**Title:** Unauthorized account manipulation in energy dispatch

**Location in code**
```js
const fromId = tokenId(req) || data.from || 'guest';
```

**Description**
If no token is provided, the caller may choose any `from` account.

**Why this is a security issue**
Attackers can move credits from other users accounts.

**OWASP category**
- A01 - Broken Access Control

**Minimal fix**
Only allow transfers from the authenticated users own account.
