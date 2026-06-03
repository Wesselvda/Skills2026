**Title:** Reflected XSS in welcome page

**Location in code**
```js
<h1>Hello ${name}</h1>
```

**Description**
User input is inserted into HTML without escaping.

**Why this is a security issue**
Attackers can inject JavaScript.

**OWASP category**
- A03 - Injection

**Minimal fix**
HTML-escape the name parameter.
