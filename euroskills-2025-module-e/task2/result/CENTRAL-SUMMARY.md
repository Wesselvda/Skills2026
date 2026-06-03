This project contains several OWASP Top 10 vulnerabilities.

Issues:

- ISSUE-001 - Broken access control in `/control/overview`
- ISSUE-002 - Unauthorized account manipulation in `/energy/dispatch`
- ISSUE-003 - Insecure custom auth token + hardcoded master key
- ISSUE-004 - MD5 password hashing
- ISSUE-005 - Command injection in `/diagnose`
- ISSUE-006 - Code injection via `eval` in `/compute`
- ISSUE-007 - Reflected XSS in `/welcome`
- ISSUE-008 - Sensitive data exposure in `/status`
- ISSUE-009 - Security misconfiguration (CORS + headers)

Grouped by OWASP category:

- A01 Broken Access Control: 001, 002
- A02 Cryptographic Failures: 003, 004, 008
- A03 Injection: 005, 006, 007
- A05 Security Misconfiguration: 008, 009
- A07 Identification & Authentication Failures: 003
