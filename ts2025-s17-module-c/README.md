# To seed the database, run the given [sql file](public/assets/database/ts2025-module-b.sql)

## For testing:

Create a application in the external oauth page (as seen in project description) and update [AuthController](app/Http/Controllers/AuthController.php) to have the correct application id and secret:

```php
private $clientId = 'cid-3f7b64fd';
private $clientSecret = 'csec-b9c0064b';
```