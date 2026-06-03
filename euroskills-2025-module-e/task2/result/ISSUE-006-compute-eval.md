**Title:** Code execution via eval in compute endpoint

**Location in code**
```js
const result = eval(expr);
```

**Description**
User input is executed with eval.

**Why this is a security issue**
Attackers can run arbitrary JavaScript on the server.

**OWASP category**
- A03 - Injection

**Minimal fix**
Remove eval and use a safe expression parser.
