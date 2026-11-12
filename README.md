# WS skautIS

Webová aplikace pro testování webových služeb skautIS a podávání žádostí o povolení externích aplikací.

Aplikace je postavena na PHP, frameworku Nette a knihovnách Skautis.

## Technické požadavky

- PHP 8.4
- Composer 2
- Nette 3 a Latte 3
- Přístup k API skautIS

Pro lokální vývoj používáme Docker s Apache a Caddy pro HTTPS.

## Lokální spuštění

1. Naklonovat repozitář.
2. Spustit Docker Desktop.
3. Do souboru `/etc/hosts` přidat:

   `127.0.0.1 wsskautis.loc`

4. Pomocí `mkcert` vytvořit lokální HTTPS certifikát a klíč v adresáři `docker/certs` pod názvy `wsskautis.loc.pem` a `wsskautis.loc-key.pem`.
5. Spustit prostředí:

   `docker compose -f docker-compose.php84.yml up -d --build`

6. Nainstalovat PHP závislosti:

   `docker compose -f docker-compose.php84.yml exec app composer install`

7. Vytvořit lokální konfiguraci `app/config/config.local.neon` podle souboru `config.local.example.neon`.

8. V lokální konfiguraci nastavit testovací AppID, `testMode: true`, lokální FileMailer a důvěryhodnou HTTPS proxy Caddy. IP adresu Caddy lze zjistit příkazem:

   `docker compose -f docker-compose.php84.yml exec app getent hosts caddy`

9. Zajistit zápisová oprávnění pro adresáře `temp-nette` a `log-nette`.

## Lokální adresy

- HTTP: `http://localhost:8091`
- HTTPS: `https://wsskautis.loc/`

HTTPS je potřeba zejména pro testování přihlášení přes skautIS.

## Přihlášení přes skautIS

Lokální aplikace využívá samostatné AppID registrované v testovacím skautIS.

Návratové adresy pro přihlášení a odhlášení:

- `https://wsskautis.loc/sign/skautis`
- `https://wsskautis.loc/sign/skautis-logout`

Lokální doména funguje pouze na počítači, kde je nakonfigurovaná.

## Bezpečnost

- Produkční AppID a přístupové údaje neukládat do Gitu.
- Soubor `config.local.neon` a lokální certifikáty musí zůstat mimo repozitář.
- Debug režim zapínat pouze v lokálním vývojovém prostředí.
- Před produkčním nasazením ověřit konfiguraci maileru, zálohy a možnost návratu k původní verzi.

## Nasazení na LebedaHosting

Produkční nasazení zatím není automatizované ani zdokumentované. Postup nasazení, správy Composer závislostí a obnovy bude doplněn po ověření produkčního prostředí.

Lokální Docker konfigurace není určena k přímému nasazení na LebedaHosting.
