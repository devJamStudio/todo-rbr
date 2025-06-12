Opis zadania:
Stwórz aplikację "To-Do list", która umożliwia użytkownikowi dodawanie, edytowanie, przeglądanie i usuwanie zadań (CRUD) oraz wysyłanie powiadomień e-mail.
-------------------------------------------------------------------------------------
Wymagania funkcjonalne:
1. CRUD:
- Pełne operacje CRUD (Create, Read, Update, Delete) na zadaniach, z następującymi polami:
- Nazwa zadania (max 255 znaków, wymagane)
- Opis (opcjonalnie)
- Priorytet (low/medium/high)
- Status (pending, in_progress, completed)
- Termin wykonania (data, wymagane)
2. Przeglądanie zadań:
- Filtrowanie listy zadań według priorytetów, statusu i terminu.
3. Powiadomienia e-mail:
- Powiadomienie e-mail na 1 dzień przed terminem zadania. Użyj mechanizmów Laravel (Queues i Scheduler).
4. Walidacja:
- Upewnij się, że wszystkie formularze poprawnie walidują dane (np. wymagane pola, odpowiedni format daty, limity znaków dla pól tekstowych).
5. Obsługa wielu użytkowników:
- Każdy użytkownik powinien mieć możliwość zalogowania się i zarządzania własnymi zadaniami (użyj systemu uwierzytelniania Laravela).
6. Udostępnianie zadań bez autoryzacji za pomocą linka z tokenem dostępowym:
- Umożliw użytkownikowi generowanie linków publicznych do zadań z tokenem dostępu. Link ma ograniczony czas działania, po którym dostęp do zadania zostaje zablokowany.
-------------------------------------------------------------------------------------
Dodatkowe funkcje (opcjonalne):
7. Pełna historia edycji notatek:
- Zapisuj każdą zmianę zadań (nazwy, opisy, priorytety, statusy, daty itp.) wraz z możliwością przeglądania poprzednich wersji.
8. Integracja z Google Calendar:
- Umożliw przypięcie zadania do Google Kalendarza za pomocą integracji z biblioteką spatie/laravel-google-calendar. Użytkownik powinien mieć możliwość skojarzenia ważnych zadań z kalendarzem Google.
-------------------------------------------------------------------------------------
Wymagania techniczne:
1. Back-end:
Laravel 11, REST API, Eloquent ORM, MySQL/SQLite, migracje baz danych.
2. Front-end:
Prosty interfejs użytkownika stworzony w Laravel Blade (opcjonalnie AJAX).
3. (Opcjonalnie) Konfiguracja w Dockerze:
Możliwość dostarczenia projektu z konfiguracją Docker (Dockerfile, docker-compose.yml). Dzięki temu uruchomienie aplikacji będzie łatwiejsze.