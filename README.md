# Gestio Partits

Aplicació web Laravel per a la gestió de partits d’un club de bàsquet amb:

- usuaris, rols i autenticació
- temporades, clubs, equips, partits i vestidors
- importació FBIB amb Guzzle i fallback Panther
- auditoria i historial d’importacions
- API protegida amb Sanctum
- cues amb driver `database`

## Requisits

- PHP 8.3
- Composer
- Node.js
- MySQL 8 local

## Instal·lació

1. Crear la base de dades MySQL:

```sql
CREATE DATABASE gestio_partits;
```

2. Configurar `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestio_partits
DB_USERNAME=root
DB_PASSWORD=
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

3. Executar:

```bash
composer install
npm install
npm run build
php artisan migrate --seed
php artisan serve
```

## Accés inicial

- usuari: `admin`
- contrasenya: `admin`

## Importació FBIB

- Configura clubs a `Clubs`
- Executa imports des de `Importar`
- Primer es prova HTTP amb Guzzle
- Si la pàgina no retorna dades útils, s’intenta amb Symfony Panther

## API

Generar token:

```bash
POST /api/tokens/create
{
  "login": "admin",
  "password": "admin",
  "device_name": "local"
}
```

Endpoints protegits amb `Bearer token`:

- `GET /api/users`
- `GET /api/seasons`
- `GET /api/teams`
- `GET /api/matches`
- `GET /api/imports`

Admeten `search`, filtres específics i `per_page`.

## Cues

La configuració demanada és amb base de dades:

```bash
php artisan queue:work
```

## Tests

```bash
php artisan test
```
