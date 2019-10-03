# ws.skautis.cz
Webová aplikace pro vývojáře chtějící pracovat s informačním systémem [SkautIS](https://is.skaut.cz/).
Aplikace je dostupná na [ws.skautis.cz](https://ws.skautis.cz/)

Aplikace poskytuje:
 - Základní informace pro vývojáře.
 - Testovací rozhraní pro zkoušení API callů.
 - Formulář pro žádost o povolení aplikace (získání ``ID_Application`` / ``appId``) 

## Lokální environment
Pro lokální spuštění je potřeba [docker](https://www.docker.com/) a [docker-compose](https://docs.docker.com/compose/). 

```bash
# Spustí container
docker-compose up -d
```

Aplikace je poté dostupná na [http://127.0.0.1/](http://127.0.0.1/).