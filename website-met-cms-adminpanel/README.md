# Authenticatie

Gegevens voor gebruiker:
```
email: admin@example.com
wachtwoord: Skills2026!
```

Om naar het adminpaneel te gaan moet je naar `/login`

In de opdracht stond bij de pagina afbeelding upload geen restricties, maar ik heb dezelfde restricties als Build background toegepast.


# Installatie

Om de applicatie op te starten moet je dit doen:
1. Installeer de dependencies (`composer install`) 
2. kopieer .env.example naar .env en stel deze in zodat je een database hebt
3. voer `php artisan key:gen` uit
4. voer `php artisan migrate --seed` uit
5. voer `php artisan storage:link` uit


# Opstarten

Voer `php artisan serve` uit