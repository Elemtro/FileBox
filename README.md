FileBox



Ten dokument zawiera kompleksową dokumentację dla projektu webowego zbudowanego w **Symfony PHP**, szczegółowo opisując jego architekturę, konfigurację, użycie API oraz zaimplementowane funkcje.

**Autorzy Projektu:** Andrii Lykhvar i Dmytro Kobzar

---

## Spis Treści

1.  [Przegląd Projektu](#1-przegląd-projektu)
2.  [Jak Uruchomić Aplikację](#2-jak-uruchomić-aplikację)
    * [Wymagania Wstępne](#wymagania-wstępne)
    * [Instrukcje Konfiguracji](#instrukcje-konfiguracji)
3.  [Użycie API](#3-użycie-api)
    * [Punkty Końcowe Autoryzacji](#punkty-końcowe-autoryzacji)
    * [Punkty Końcowe Zarządzania Plikami](#punkty-końcowe-zarządzania-plikami)
    * [Kolekcja Postman/Insomnia](#kolekcja-postmaninsomnia)
4.  [Struktura i Projekt Aplikacji](#4-struktura-i-projekt-aplikacji)
    * [Wzorzec Model-View-Controller (MVC)](#wzorzec-model-view-controller-mvc)
    * [Obiekty Transferu Danych (DTOs)](#obiekty-transferu-danych-dtos)
    * [Serwisy](#serwisy)
    * [Encje](#encje)
    * [Repozytoria](#repozytoria)
5.  [Schemat Bazy Danych](#5-schemat-bazy-danych)
6.  [Fixtures Danych](#6-fixtures-danych)
7.  [Użycie Query Buildera](#7-użycie-query-buildera)
8.  [Zarządzanie Użytkownikami, Autoryzacja i Uprawnienia](#8-zarządzanie-użytkownikami-autoryzacja-i-uprawnienia)
9.  [Niestandardowe Komendy CLI](#9-niestandardowe-komendy-cli)
10. [Testy Jednostkowe (PHPUnit)](#10-testy-jednostkowe-phpunit)
11. [Niestandardowa Obsługa Błędów](#11-niestandardowa-obsługa-błędów)

---

## 1. Przegląd Projektu

Projekt to aplikacja webowa stworzona przy użyciu **Symfony** i **PHP 8.x**, prezentująca kluczowe aspekty nowoczesnej aplikacji PHP, takie jak uwierzytelnianie użytkowników, zarządzanie plikami oraz ustrukturyzowane podejście oparte na wzorcu **MVC**. Aplikacja wykorzystuje **PostgreSQL** jako główną bazę danych relacyjnych i jest zintegrowana z **Dockerem** dla łatwego środowiska deweloperskiego i produkcyjnego.

---

## 2. Jak Uruchomić Aplikację

Aplikacja została skonfigurowana do uruchamiania za pomocą **Docker Compose** i z użyciem **dunglas/symfony-docker**, co zapewnia spójne środowisko deweloperskie i produkcyjne.

### Wymagania Wstępne

* **Docker** zainstalowany na Twoim systemie.
* **git** zainstalowany na twoim systemie.

### Instrukcje Konfiguracji

1.  **Sklonuj Repozytorium:**
    ```bash
    git clone https://github.com/Elemtro/FileBox.git
    ```

2.  **Skonfiguruj Zmienne Środowiskowe:**
    Utwórz plik `.env.local` w katalogu głównym projektu, bazując na przykładzie `.env`.
    ```dotenv
    # .env.local
    ###> symfony/framework-bundle ###
    APP_ENV=prod
    APP_DEBUG=0
    POSTGRES_DB=app
    POSTGRES_USER=app
    POSTGRES_PASSWORD=!ChangeMe! # ZMIEŃ TO NA SILNE HASŁO!
    DATABASE_URL="postgresql://app:!ChangeMe!@database:5432/app?serverVersion=16&charset=utf8"
    ###< symfony/framework-bundle ###

    # Możesz nadpisać inne zmienne z .env tutaj, jeśli to konieczne
    HTTP_PORT=8080
    HTTPS_PORT=4434
    HTTP3_PORT=4434
    ```
    **Ważne:** Zmień `POSTGRES_PASSWORD` na silne i unikalne hasło, szczególnie w środowisku produkcyjnym.

3.  **Zbuduj i Uruchom Kontenery Docker:**
    ```bash
    docker compose up --build -d
    ```
    Ta komenda zbuduje obrazy Docker (aplikacja PHP, baza danych PostgreSQL i Adminer) i uruchomi je w trybie odłączonym.

4.  **Dostęp do Aplikacji:**
    * **Aplikacja Webowa:** Otwórz przeglądarkę i przejdź do `http://localhost:8080` (lub portu `HTTP_PORT`, który skonfigurowałeś). Powinieneś zostać przekierowany na stronę logowania.
    * **Adminer (Zarządzanie Bazą Danych):** Dostęp do Adminera pod adresem `http://localhost:8081`.
        * **System:** `PostgreSQL`
        * **Serwer:** `database`
        * **Nazwa użytkownika:** `app` (lub Twój `POSTGRES_USER`)
        * **Hasło:** `!ChangeMe!` (lub Twoje `POSTGRES_PASSWORD`)
        * **Baza danych:** `app` (lub Twoja `POSTGRES_DB`)

---

## 3. Użycie API

Aplikacja udostępnia interfejs API RESTful do uwierzytelniania użytkowników i zarządzania plikami.

### Punkty Końcowe Autoryzacji

* **Formularz Logowania (GET):** `/login`
    * Wyświetla formularz logowania HTML + CSS.
* **Formularz Rejestracji (GET):** `/register`
    * Wyświetla formularz rejestracji HTML + CSS.
* **API Logowanie (POST):** `/api/auth/login`
    * **Metoda:** `POST`
    * **Treść Żądania (JSON):**
        ```json
        {
            "email": "user@example.com",
            "password": "twojehaslo"
        }
        ```
    * **Odpowiedź Sukcesu (200 OK):** Zwraca `user_uuid`, `email` i `roles`.
    * **Odpowiedź Błędu (400 Bad Request/401 Unauthorized):** Zwraca komunikaty o błędach walidacji lub nieudanym uwierzytelnieniu.
* **API Rejestracja (POST):** `/api/auth/register`
    * **Metoda:** `POST`
    * **Treść Żądania (JSON):**
        ```json
        {
            "email": "nowyuser@example.com",
            "password": "bezpiecznehaslo123"
        }
        ```
    * **Odpowiedź Sukcesu (201 Created):** Informuje o pomyślnej rejestracji.
    * **Odpowiedź Błędu (400 Bad Request/500 Internal Server Error):** Zwraca błędy walidacji lub informację o istniejącym użytkowniku.
* **API Wylogowanie (GET):** `/api/auth/logout`
    * **Metoda:** `GET`
    * Unieważnia sesję użytkownika i przekierowuje na formularz logowania.

### Punkty Końcowe Zarządzania Plikami

Te punkty końcowe wymagają zalogowanego użytkownika (uwierzytelnianie oparte na sesji).

* **Strona Przesyłania (GET):** `/file/upload`
    * Wyświetla formularz przesyłania plików HTML. Przekierowuje do logowania, jeśli użytkownik nie jest uwierzytelniony.
* **API Przesyłanie Plików (POST):** `/api/file/upload`
    * **Metoda:** `POST`
    * **Content-Type:** `multipart/form-data`
    * **Form Data:** `file`: plik do przesłania.
    * **Przekierowania:** Przekierowuje z powrotem na stronę przesyłania z komunikatem flash. Wymaga uwierzyteltnienia.
* **API Usuwanie Plików (POST):** `/api/file/delete`
    * **Metoda:** `POST`
    * **Treść Żądania (form-urlencoded):** `storagePath`: ścieżka przechowywania pliku do usunięcia (np. `uuid-uzytkownika/nazwa_pliku.roz`).
    * **Przekierowania:** Przekierowuje na stronę główną (`/home`) po usunięciu. Wymaga uwierzytelnienia.
* **API Pobieranie Plików (POST):** `/api/file/download`
    * **Metoda:** `POST`
    * **Treść Żądania (form-urlencoded):** `storagePath`: ścieżka przechowywania pliku do pobrania.
    * **Odpowiedź:** Plik binarny do pobrania.
    * **Odpowiedź Błędu (404 Not Found):** Jeśli plik nie zostanie znaleziony lub użytkownik nie ma do niego dostępu. Wymaga uwierzyteltnienia.

### Kolekcja Postman/Insomnia

Zaleca się importowanie gotowej kolekcji Postman lub Insomnia, aby ułatwić testowanie i interakcję z API. Ale bardziej polecam skorzystać się z prostego frontendu tej aplikacji.

**Instrukcje:**
1.  Importuj dostarczony plik kolekcji Postman/Insomnia (`symfony_project_api.json`).
2.  Upewnij się, że aplikacja działa (`docker ps`).
3.  Skonfiguruj zmienne środowiskowe (np. `baseUrl` na `http://localhost:8080`).
4.  Wykorzystaj przykłady żądań w kolekcji do testowania punktów końcowych.

---

## 4. Struktura i Projekt Aplikacji

Projekt ściśle przestrzega wzorca architektonicznego **Model-View-Controller (MVC)**, z wyraźnym oddzieleniem odpowiedzialności.

### Wzorzec Model-View-Controller (MVC)

* **Kontrolery (`src/Api/Controller`):** Obsługują żądania HTTP, delegują logikę do serwisów i zwracają odpowiednie odpowiedzi (JSON dla API, HTML dla widoków).
* **Widoki (`templates/`):** Bardzo proste szablony Twig odpowiedzialne za generowanie interfejsu użytkownika.
* **Modele (Encje i Repozytoria):**
    * **Encje (`src/Storage/Entity/`):** Reprezentują strukturę danych i mapowanie na tabele bazy danych (np. `User`, `File`).
    * **Repozytoria (`src/Storage/Repository/`):** Hermetyzują logikę dostępu do bazy danych dla poszczególnych encji.

### Obiekty Transferu Danych (DTOs)

**DTOs** (np. `LoginRequest`, `RegistrationRequest` w `src/Api/Dto/`) są wykorzystywane do definiowania struktury danych dla przychodzących żądań API. `DtoResolver` automatycznie deserializuje dane JSON do tych obiektów, zapewniając walidację i ułatwiając pracę kontrolerom.

### Serwisy

**Serwisy** (np. `AuthService`, `FileService`, `UserService` w `src/Api/Service/`) zawierają logikę biznesową aplikacji. Są modułowe, co ułatwia ich ponowne wykorzystanie i testowanie, oraz są wstrzykiwane do kontrolerów.

### Encje

* **`User`**: Reprezentuje użytkownika systemu z unikalnym `uuid`, `email`, zahashowanym `password` i `role`. Implementuje interfejsy Symfony Security.
* **`File`**: Reprezentuje przesłany plik z metadanymi takimi jak `fileUuid`, `user` (powiązanie z `User`), `originalFilename`, `size`, `mimeType`, `storagePath` i `uploadedAt`.

Obie encje wykorzystują `Symfony\Component\Uid\Uuid` do generowania unikalnych identyfikatorów UUID jako kluczy podstawowych.

### Repozytoria

* **`UserRepository`**: Dostarcza metody do zarządzania encją `User`, takie jak wyszukiwanie po adresie e-mail czy UUID oraz zapisywanie użytkowników.
* **`FileRepository`**: Zarządza encjami `File`, oferując metody do znajdowania wszystkich plików użytkownika, wyszukiwania po UUID użytkownika i ścieżce pliku oraz usuwania plików.

---

## 5. Schemat Bazy Danych

Projekt wykorzystuje **PostgreSQL**. Schemat bazy danych jest definiowany przez encje Doctrine ORM.

Pola `uuid` i `file_uuid` stanowią strukturę klucz-wartość, służącą jako unikalne identyfikatory rekordów, umożliwiając efektywne wyszukiwanie i zarządzanie danymi.

---

## 6. Fixtures Danych

Projekt wykorzystuje **fixtures danych** do ładowania początkowych danych do bazy, co jest przydatne przede wszystkim w środowiskach deweloperskich i testowych.

* **Klasa Fixture:** `src/DataFixtures/AppFixtures.php`
* **Cel:** Tworzy 10 fikcyjnych użytkowników (`user1@gmail.com` do `user10@gmail.com`) z hasłem `password123` i rolą `ROLE_USER`.

### Ważna uwaga dla środowiska produkcyjnego:

W celu zachowania czystego środowiska produkcyjnego i uniknięcia niepotrzebnych zależności, pakiet `doctrine/fixtures-bundle` został domyślnie usunięty z zależności deweloperskich (`require-dev`) w pliku `composer.json`.

Jeśli chcesz użyć fixtures danych w swoim lokalnym środowisku deweloperskim, musisz wykonać następujące kroki:

1.  **Upewnij się, że Twoja aplikacja działa w trybie deweloperskim (`APP_ENV=dev` w `.env.local`).**
2.  **Zainstaluj pakiet Doctrine Fixtures Bundle:**
    ```bash
    docker compose exec php composer require --dev doctrine/fixtures-bundle
    ```
    Ta komenda doda pakiet do sekcji `require-dev` w `composer.json`.
3.  **Włącz Fixtures Bundle w konfiguracji:**
    Upewnij się, że linia `Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true],` (lub `['all' => true]` jeśli wolisz) znajduje się w pliku `config/bundles.php`. Sprawdź, czy `config/bundles.php` zawiera:
    ```php
    // config/bundles.php
    return [
        // ... inne bundy
        Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true],
    ];
    ```
    Jeśli nie ma, dodaj ją ręcznie.

Po wykonaniu powyższych kroków, możesz załadować fixtures:

```bash
docker compose exec php bin/console doctrine:fixtures:load
```

---

## 7. Użycie Query Buildera

**Query Builder Doctrine** jest wykorzystywany w repozytoriach do konstruowania złożonych zapytań do bazy danych. Metody takie jak `findBy` czy `findOneBy` domyślnie korzystają z QB, a dla bardziej zaawansowanych scenariuszy można go jawnie użyć.

---

## 8. Zarządzanie Użytkownikami, Autoryzacja i Uprawnienia

* **Użytkownicy:** Reprezentowani przez encję `User`.
* **Uwierzytelnianie:**
    * **Logowanie:** Obsługiwane przez `AuthService::login`, weryfikujące dane i ustanawiające sesję.
    * **Rejestracja:** `AuthService::register` tworzy nowe konta z zahashowanym hasłem.
    * **Wylogowanie:** `AuthService::logout` czyści sesję.
* **Autoryzacja i Uprawnienia:**
    * **Role:** Zdefiniowane w enumie `UserRole` (`ROLE_USER`).
    * **Kontrola Dostępu:** Kontrolery sprawdzają sesję użytkownika do podstawowej kontroli dostępu. Operacje na plikach są powiązane z UUID użytkownika, zapewniając dostęp tylko do własnych plików.

---

## 9. Niestandardowe Komendy CLI

Zaimplementowano niestandardową komendę CLI do zarządzania użytkownikami.

* **Nazwa Komendy:** `app:create-user`
* **Lokalizacja:** `src/Command/CreateUserCommand.php`
* **Opis:** Pozwala tworzyć nowych użytkowników z poziomu terminala.
* **Użycie:**
    ```bash
    docker compose exec php bin/console app:create-user <email> <password> [role]
    ```
    * `<email>` (wymagane): Adres e-mail.
    * `<password>` (wymagane): Hasło.
    * `[role]` (opcjonalne): Rola (`USER` lub `ADMIN`, domyślnie `USER`).
* **Przykład:**
    ```bash
    docker compose exec php bin/console app:create-user admin@example.com mySecureAdminPass ADMIN
    ```

---
## 10. Testy Jednostkowe (PHPUnit)

Projekt jest przygotowany do testowania jednostkowego za pomocą PHPUnit, co zapewnia niezawodność i poprawność działania poszczególnych komponentów aplikacji.

  * **Lokalizacja testów:** Wszystkie testy jednostkowe znajdują się w katalogu tests/.
  * **Zakres:** Testy obejmują kluczową logikę biznesową, w tym serwisy (AuthService, FileService), interakcje repozytoriów, walidację obiektów DTO (Data Transfer Objects) oraz funkcjonalność niestandardowych komend CLI.

**Jak uruchomić testy jednostkowe:**

Aby uruchomić testy jednostkowe, upewnij się, że Twoje kontenery Docker są uruchomione, a następnie wykonaj poniższą komendę w terminalu:

    docker compose exec php bin/phpunit

Ta komenda uruchomi wszystkie testy zdefiniowane w katalogu tests/ i wyświetli ich wyniki.
