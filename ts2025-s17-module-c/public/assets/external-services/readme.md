# External services

## Development

To work on this solution, run the following command:

```bash
# Start the external services
docker compose up -d
```

This will start the following components:

- `solution`: A solution of this module, for providing the `/external-services/` endpoints [http://localhost:3000](http://localhost:3000)
- `oauth`: A service implementing the basic OAuth 2.0 Authorization Code Flow and UserInfo (OpenID Connect) model. [http://localhost:7000](http://localhost:7000)
- `db`: MariaDB listening on port 3306, password of the root user is `root`
- `phpmyadmin`: PhpMyAdmin instance listening on [http://localhost:8080](http://localhost:8080)
