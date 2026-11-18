# WS skautIS

Webová aplikace pro testování webových služeb skautIS a podávání žádostí o povolení externích aplikací.

Aplikace je postavena na PHP, frameworku Nette a knihovnách Skautis.

## Technické požadavky

- PHP 8.4 (lokální vývoj a testovací hosting); na produkci je od 18. 11. 2026 nasazeno PHP 8.5
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

## Prostředí

| Prostředí | Adresa | Verze PHP |
| --- | --- | --- |
| Lokální vývoj (Docker) | `https://wsskautis.loc/` | 8.4 |
| Testovací LebedaHosting | `https://ws-php84-test.skauting.cz/` | 8.4 |
| Produkční LebedaHosting | `https://ws.skautis.cz/` | 8.5 |

Produkční hosting je v administraci Lebedy veden pod `ws.skauting.cz`; veřejná aplikace běží na aliasu `ws.skautis.cz`.

Produkční aktualizace proběhla **18. 11. 2026**. Aplikace byla připravena a testována na PHP 8.4, při nasazení byla produkce přepnuta na PHP 8.5. Ověřeno bylo načtení webu, přihlášení a odhlášení přes skautIS, volání `OrganizationUnit/unitAll` a odeslání formuláře. Při následné kontrole nebyly nalezeny nové chyby v aplikačních logách. Samotné přijetí formuláře ještě nezaručuje doručení e-mailu; doručení je třeba kontrolovat samostatně.

## Nasazení na LebedaHosting

**Aktuální stav:** nasazování je manuální přes FTPS (např. Cyberduck). Push do GitHubu produkci **neaktualizuje**. Dřívější webhook DeployHQ je deaktivovaný. Automatické nasazování zatím není zprovozněné.

Lokální Docker konfigurace není určena k přímému nasazení na LebedaHosting.

### 1. Příprava

1. Zkontrolovat testy na lokálním a testovacím prostředí a určit přesný commit určený k nasazení.
2. Stáhnout aktuální **zálohu souborů i databáze** z produkce a ověřit, že je dostupná pro případnou obnovu.
3. Z repozitáře připravit samostatný balíček zdrojových souborů a Composer závislostí, bez vývojových balíčků:

   ```bash
   mkdir -p ../ws-skautis-deploy-php84-prod
   git archive HEAD app www composer.json composer.lock \
     | tar -x -C ../ws-skautis-deploy-php84-prod

   docker compose -f docker-compose.php84.yml run --rm --no-deps \
     -v "$(cd ../ws-skautis-deploy-php84-prod && pwd):/deploy" \
     -w /deploy app \
     composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-progress
   ```

4. Ověřit přítomnost `app/`, `vendor/autoload.php`, `www/` a statických souborů. Do balíčku **nevkládat** lokální `app/config/config.local.neon` ani vývojové certifikáty.
5. Připravit stránku údržby `/www/udrzba.html` a uložit původní produkční `/www/.htaccess` **mimo webový prostor**.

### 2. Příprava souborů na serveru

1. Přes FTPS nahrát nové soubory do pomocných adresářů v kořeni hostingu (mimo veřejné `www/`): `app-php84-new/`, `vendor-php84-new/`, `www-php84-new/`.
2. Z původní produkční aplikace zkopírovat `app/config/config.local.neon` do nové `app-php84-new/config/config.local.neon`. Zachovat produkční AppID, nastavení skautIS a maileru; **nepoužívat testovací konfiguraci**.
3. Před úpravami ověřit, že produkční záloha umožňuje obnovit rovněž původní `www/`, konfiguraci a nastavení PHP.

### 3. Přepnutí aplikace

1. Zapnout v produkčním `/www/.htaccess` dočasnou údržbu s HTTP **503** a výjimkou pro `/udrzba.html`. Ověřit, že `/` vrací `503` a `/udrzba.html` vrací `200`.
2. Přejmenovat původní `app/` a `vendor/` na záložní adresáře (např. `app-php80-backup/` a `vendor-php80-backup/`) a nové `app-php84-new/` a `vendor-php84-new/` přejmenovat na `app/` a `vendor/`.
3. Aktualizovat **obsah** produkčního `www/` z `www-php84-new/`. Existující soubory přepsat a adresáře sloučit, ale **nemazat** produkční soubory mimo repozitář (např. uživatelské uploady). Nepřepsat aktivní produkční `.htaccess`.
4. V administraci LebedaHostingu nastavit cílovou verzi PHP. Při nasazení 18. 11. 2026 byla použita **8.5**.
5. Vymazat obsah `/temp-nette/cache/` (ponechat samotný adresář). Nezasahovat do `/session/` ani `/log-nette/`.
6. Na co nejkratší dobu vypnout pravidla údržby a provést funkční testy. V případě chyby znovu zapnout údržbu, dokud nebude problém odstraněn.

### 4. Ověření a dokončení

- Web vrací HTTP `200`, přesměrování domén a HTTPS fungují.
- Funguje přihlášení a odhlášení přes skautIS.
- SkautIS API úspěšně vrací data (např. `OrganizationUnit/unitAll`).
- Funguje formulář a **ověří se také doručení skutečných e-mailů**.
- V `/log-nette/` a serverových logách nevznikají nové relevantní chyby.
- Z `/www/.htaccess` se odstraní dočasná pravidla pro HTTP 503; ověří se veřejná dostupnost aplikace.
- Pomocné adresáře se smažou až po kontrole nasazení. Původní aplikaci a knihovny je vhodné krátkodobě ponechat jako rollback zálohu a poté odstranit podle interní retenční dohody; dlouhodobá záloha má být uložená mimo server.

### Návrat k předchozí verzi (rollback)

Při neúspěšném nasazení znovu aktivovat režim údržby, vrátit původní `app/` a `vendor/`, obnovit případné změněné soubory `www/` ze zálohy a nastavit původní PHP verzi. Zachovat správný `config.local.neon`, vyčistit Nette cache a otestovat aplikaci před vypnutím údržby. **Pouhé vrácení složek `app/` a `vendor/` nemusí stačit**, pokud se změnily soubory v `www/`.

### Plánované automatické nasazování

Cílem je nahradit manuální FTPS nasazování automatizovaným postupem z GitHubu. Teprve po jeho zavedení doplnit konkrétní workflow, pravidla nasazování větví, práci s produkční konfigurací, Composer závislostmi, zálohami, kontrolami a rollbackem. Do té doby jsou výše uvedené kroky platným manuálním postupem.
