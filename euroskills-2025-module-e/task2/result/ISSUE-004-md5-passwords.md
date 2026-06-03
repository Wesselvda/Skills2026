**Title:** Passwords hashed with unsalted MD5

**Location in code**
```js
crypto.createHash('md5').update(data.key || '').digest('hex');
```

**Description**
Passwords are hashed with plain MD5.

**Why this is a security issue**
MD5 is weak and easily cracked.

**OWASP category**
- A02 - Cryptographic Failures

**Minimal fix**
Use bcrypt, scrypt, or Argon2.
