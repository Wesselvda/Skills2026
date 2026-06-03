**Title:** Command injection in diagnose endpoint

**Location in code**
```js
exec(`ping -c 1 ${ip}`, ...)
```

**Description**
User input is inserted directly into a shell command.

**Why this is a security issue**
Attackers can execute arbitrary system commands.

**OWASP category**
- A03 - Injection

**Minimal fix**
Use execFile and validate the IP.
