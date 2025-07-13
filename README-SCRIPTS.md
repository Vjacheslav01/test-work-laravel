# 🚀 Bash скрипты для Laravel + Vue.js 3 + Pinia

Этот проект содержит удобные bash скрипты для автоматизации работы с Laravel Sail.

## 📋 Доступные скрипты

### 🌟 `./start.sh` - Запуск проекта

Основной скрипт для запуска всего проекта:
- Останавливает предыдущие контейнеры
- Запускает Laravel Sail
- Устанавливает/обновляет зависимости
- Применяет миграции БД
- Генерирует ключ приложения
- Очищает кэш
- Собирает фронтенд
- Опционально запускает dev server

```bash
./start.sh
```

### 🛑 `./stop.sh` - Остановка проекта

Останавливает все контейнеры Docker:

```bash
./stop.sh
```

### 🔄 `./update.sh` - Обновление зависимостей

Обновляет все зависимости проекта:
- Composer packages
- NPM packages
- Применяет новые миграции
- Очищает кэш
- Пересобирает фронтенд

```bash
./update.sh
```

### 🗃️ `./migrate.sh` - Управление миграциями

Управление миграциями базы данных:

```bash
./migrate.sh run              # Применить миграции
./migrate.sh rollback         # Откатить последнюю миграцию
./migrate.sh reset            # Откатить все миграции
./migrate.sh fresh            # Удалить все таблицы и применить миграции заново
./migrate.sh status           # Показать статус миграций
./migrate.sh make <name>      # Создать новую миграцию
```

**Примеры:**
```bash
./migrate.sh run
./migrate.sh make create_products_table
./migrate.sh status
```

### 🧹 `./clear.sh` - Очистка кэша

Очищает все виды кэша Laravel:
- Кэш конфигурации
- Кэш приложения
- Кэш представлений
- Кэш маршрутов
- Кэш событий
- Скомпилированные классы
- Оптимизирует автозагрузку

```bash
./clear.sh
```

### 🧪 `./test.sh` - Запуск тестов

Запуск различных типов тестов:

```bash
./test.sh                     # Запустить все тесты
./test.sh unit                # Запустить только Unit тесты
./test.sh feature             # Запустить только Feature тесты
./test.sh coverage            # Запустить тесты с покрытием кода
./test.sh tests/Unit/ExampleTest.php  # Запустить конкретный тест
```

### 🛠️ `./dev.sh` - Запуск dev сервера

Запускает Vite dev server для фронтенд разработки:

```bash
./dev.sh
```

## 🌐 Доступ к приложению

После запуска `./start.sh` приложение будет доступно по адресам:

- **Laravel приложение**: http://localhost:8080
- **Vite dev server**: http://localhost:5173 (при запуске `./dev.sh`)
- **Mailpit (почта)**: http://localhost:8025
- **Meilisearch**: http://localhost:7700

## 🔧 Полезные команды Laravel Sail

Если вам нужно выполнить другие команды Laravel Sail:

```bash
# Войти в контейнер
./vendor/bin/sail bash

# Выполнить artisan команду
./vendor/bin/sail artisan <command>

# Выполнить composer команду
./vendor/bin/sail composer <command>

# Выполнить npm команду
./vendor/bin/sail npm <command>

# Посмотреть логи
./vendor/bin/sail logs

# Перезапустить контейнеры
./vendor/bin/sail restart
```

## 📝 Примеры использования

### Первый запуск проекта:
```bash
./start.sh
```

### Ежедневная разработка:
```bash
# Запуск проекта (если контейнеры остановлены)
./start.sh

# Запуск dev сервера для фронтенд разработки
./dev.sh
```

### Обновление после git pull:
```bash
./update.sh
```

### Работа с миграциями:
```bash
# Создать миграцию
./migrate.sh make create_products_table

# Применить миграции
./migrate.sh run

# Посмотреть статус
./migrate.sh status
```

### Запуск тестов:
```bash
./test.sh
```

### Очистка кэша при проблемах:
```bash
./clear.sh
```

### Остановка работы:
```bash
./stop.sh
```

## 🎯 Технологии

- **Laravel 12** - PHP фреймворк
- **Laravel Sail** - Docker окружение
- **Vue.js 3** - JavaScript фреймворк
- **Pinia** - Управление состоянием
- **Vite** - Сборщик модулей
- **Tailwind CSS** - CSS фреймворк
- **MySQL** - База данных
- **Redis** - Кэш и очереди
- **Mailpit** - Почтовый клиент для разработки
- **Meilisearch** - Поисковый движок 