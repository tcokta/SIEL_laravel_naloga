# Task Tracker API

Preprost REST API za upravljanje taskov, zgrajen z Laravel 12 in SQLite.

## Zahteve

- PHP 8.3+
- Composer
- SQLite (vključen v PHP)

## Zagon projekta

```bash
# 1. Kloniraj repozitorij
git clone https://github.com/tcokta/SIEL_laravel_naloga.git
cd SIEL_laravel_naloga

# 2. Namesti odvisnosti
composer install

# 3. Ustvari .env datoteko
cp .env.example .env
php artisan key:generate

# 4. Ustvari SQLite bazo
touch database/database.sqlite

# 5. Zaženi migracije
php artisan migrate

# 6. Naloži testne podatke
php artisan db:seed

# 7. Zaženi razvojni strežnik
php artisan serve
```

API je dostopen na `http://localhost:8000/api`.

## Zagon z Dockerjem

```bash
docker compose up
```

API je dostopen na `http://localhost:8000/api`. Docker samodejno namesti odvisnosti, požene migracije in seed-e.

## Migracije in seed-i

```bash
# Samo migracije
php artisan migrate

# Migracije + seed-i skupaj
php artisan migrate --seed

# Samo seed-i (baza mora že obstajati)
php artisan db:seed

# Ponastavi bazo in naloži seed-e znova
php artisan migrate:fresh --seed
```

## Testi

```bash
php artisan test
```

Za zagon samo feature testov:

```bash
php artisan test --filter=TaskApiTest
```

## API endpointi

### GET /api/tasks

Vrne vse taske. Podpira filtriranje z query parametri.

```bash
# Vsi taski
curl http://localhost:8000/api/tasks

# Filtriraj po statusu
curl "http://localhost:8000/api/tasks?status=todo"
curl "http://localhost:8000/api/tasks?status=in_progress"
curl "http://localhost:8000/api/tasks?status=done"

# Filtriraj po prioriteti
curl "http://localhost:8000/api/tasks?priority=high"

# Kombiniraj filtre
curl "http://localhost:8000/api/tasks?status=todo&priority=high"
```

### GET /api/tasks/{id}

Vrne posamezen task.

```bash
curl http://localhost:8000/api/tasks/1
```

### POST /api/tasks

Ustvari nov task.

```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Implementirati avtentikacijo",
    "description": "Dodati JWT avtentikacijo za API",
    "status": "todo",
    "priority": "high",
    "due_date": "2025-12-31"
  }'
```

**Obvezna polja:** `title`

**Opcijska polja:** `description`, `status` (privzeto: `todo`), `priority` (privzeto: `medium`), `due_date`

**Dovoljene vrednosti:**
- `status`: `todo`, `in_progress`, `done`
- `priority`: `low`, `medium`, `high`

### PUT /api/tasks/{id}

Posodobi obstoječi task. Pošlješ samo polja, ki jih želiš spremeniti.

```bash
curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "in_progress"
  }'
```

### DELETE /api/tasks/{id}

Izbriše task.

```bash
curl -X DELETE http://localhost:8000/api/tasks/1
```

## Poslovno pravilo

Task s statusom `done` **ne sme imeti `due_date` v prihodnosti**. V primeru kršitve API vrne:

```json
{
  "message": "Validation failed.",
  "errors": {
    "due_date": [
      "Task with status \"done\" cannot have a due date in the future."
    ]
  }
}
```

Primer napačnega zahtevka:

```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title": "Test", "status": "done", "due_date": "2099-01-01"}'
# → 422 Unprocessable Entity
```

## HTTP status kode

| Koda | Pomen |
|------|-------|
| 200  | Uspešno |
| 201  | Uspešno ustvarjeno |
| 204  | Uspešno izbrisano |
| 404  | Task ni najden |
| 422  | Validacijska napaka |

## Struktura projekta

```
app/
  Http/
    Controllers/
      TaskController.php     # CRUD logika
    Requests/
      StoreTaskRequest.php   # Validacija za POST
      UpdateTaskRequest.php  # Validacija za PUT
  Models/
    Task.php
database/
  migrations/
    ..._create_tasks_table.php
  seeders/
    TaskSeeder.php           # 5 testnih taskov
tests/
  Feature/
    TaskApiTest.php          # 18 feature testov
```
