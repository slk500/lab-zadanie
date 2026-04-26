## Uruchomienie Projektu

### Wymagania

- Docker >= 24
- Docker Compose >= 2

### Szybki start

```bash
# 1. Sklonuj repozytorium
git clone https://github.com/slk500/lab-zadanie
cd zadanie

# 2. Uruchom wszystkie kontenery
docker compose up -d --build

# 3. Poczekaj aż baza danych będzie gotowa (~15 s), następnie uruchom migracje i seedery
docker compose exec backend php artisan migrate:fresh --seed

# 5. Otwórz przeglądarkę
#    Frontend: http://localhost:3000
#    Backend API: http://localhost:8080/api
```

### Dane testowe (po imporcie CSV)

| Login | Hasło |
|---|---|
| `PiotrKowalski` | `1983-04-12` |
| `AnnaJabłońska` | `2002-12-12` |
| `AndrzejKowalski` | `2020-01-31` |
| `BożenaWiśniewska` | `2021-11-21` |

### Uruchomienie testów (wymaga działającej bazy MySQL)

```bash
docker compose exec backend php -d xdebug.mode=off vendor/bin/phpunit
```

---

### Pipeline CI/CD (GitLab)

Plik `.gitlab-ci.yml` zawiera trzy etapy:

| Stage | Job | Opis |
|---|---|---|
| `test` | `backend:test` | Uruchamia testy Feature z MySQL |
| `build` | `frontend:build` | Buduje aplikację Vue.js |
| `push` | `docker:backend`, `docker:frontend` | Buduje i wypycha obrazy Docker (tylko na `main`/`master`) |

Zmienne środowiskowe wymagane w GitLab CI:
- `CI_REGISTRY_USER` / `CI_REGISTRY_PASSWORD` / `CI_REGISTRY` — GitLab Container Registry (ustawiane automatycznie)

---

## Opis Zadania

### 1. Command do Importu Danych

Zaimplementuj polecenie konsolowe w Laravel, które wczyta dane pacjentów i wyniki ich badań z pliku CSV (results.csv) o następującym formacie:

| patientId | patientName | patientSurname | patientSex | patientBirthDate | orderId | testName | testValue | testReference |
|-----------|-------------|----------------|------------|------------------|---------|----------|-----------|---------------|

- [x] Importowane dane mają zostać zapisane w bazie danych (tabele pacjentów, zamówień i wyników badań).
- **Wymagania techniczne:**
    - [x] Obsługa błędów w przypadku niekompletnego lub wadliwego pliku CSV.
    - [x] Logowanie poprawnie zaimportowanych rekordów oraz błędów do pliku.

---

### 2. Stworzenie API

- **Endpointy:**
    - [x] `POST /api/login` – logowanie użytkownika na podstawie loginu (połączenie imienia i nazwiska pacjenta, np. `PiotrKowalski`) i hasła (data urodzenia pacjenta, np. `1983-04-12`). Zwraca token JWT.
    - [x] `GET /api/results` – zwraca dane zalogowanego pacjenta oraz wyniki jego badań na podstawie tokenu JWT.
      Endpoint powinien zwrócić dane w następującej postaci:
```json
{
  "patient": {
    "id": 10,
    "name": "John",
    "surname": "Smith",
    "sex": "m",
    "birthDate": "2021-01-01"
  },
  "orders": [
    {
      "orderId": "20",
      "results": [
        {
          "name": "foo",
          "value": "1",
          "reference": "1-2"
        },
        {
          "name": "bar",
          "value": "2",
          "reference": "1-2"
        }
      ]
    },
    {
      "orderId": "21",
      "results": [
        {
          "name": "foo",
          "value": "1",
          "reference": "1-2"
        },
        {
          "name": "bar",
          "value": "2",
          "reference": "1-2"
        }
      ]
    }
  ]
}
```
- **Dodatkowe wymagania:**
    - [x] Autoryzacja za pomocą tokenu JWT.
    - [x] Obsługa błędów (401 dla nieautoryzowanych żądań, 404 dla braku danych).

---

### 3. Frontend (Vue.js)

- **Funkcjonalności:**
    - **Logowanie użytkownika:**
        - [x] Formularz logowania (login: imię + nazwisko pacjenta, hasło: data urodzenia).
        - [x] Po pomyślnym zalogowaniu, użytkownik zostaje przekierowany do widoku z wynikami badań.
    - **Podgląd danych pacjenta i wyników badań:**
        - [x] Wyświetlanie szczegółowych informacji o pacjencie.
        - [x] Lista wyników badań (nazwa badania, wartość, wartość referencyjna).

- **Wymagania techniczne:**
    - [x] Przechowywanie tokenu JWT w LocalStorage.
    - [x] Automatyczne wylogowanie po wygaśnięciu tokenu (nice to have).

---

### 4. Baza Danych

- Przygotuj schemat bazy danych (PostgreSQL lub MySQL), który obsłuży:
    - [x] Pacjentów.
    - [x] Zamówienia (orderId) i wyniki badań.
- [x] Zaimplementuj migracje w Laravel.

---

### 5. CI/CD

- Przygotuj plik konfiguracyjny dla GitLab CI/CD, który:
    - [x] Uruchamia testy jednostkowe i integracyjne dla API.
    - [x] Buduje aplikację frontendową (nice to have).
    - [x] Buduje i wypycha obraz Docker (nice to have).

---

### 6. Docker

- [x] Opracuj plik `docker-compose.yml`, który umożliwi lokalne uruchomienie aplikacji z backendem, frontendem i bazą danych.

---

## Czas Realizacji

Zadanie należy wykonać w ciągu **7 dni** od momentu jego otrzymania.

---

## Wynik Końcowy

Kandydat powinien dostarczyć repozytorium GIT (np. link do GitHub/GitLab/Bitbucket), które zawiera:
- Kod źródłowy backendu i frontendu.
- Pliki konfiguracyjne Docker, CI/CD i migracji.
- Plik README.md z instrukcjami uruchomienia projektu i pipeline’a CI/CD.
