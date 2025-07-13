#!/bin/bash

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}  Очистка кэша${NC}"
    echo -e "${BLUE}================================${NC}"
}

print_header

# Проверяем, что контейнеры запущены
if ! ./vendor/bin/sail ps | grep -q "Up"; then
    print_error "Laravel Sail не запущен. Сначала запустите: ./start.sh"
    exit 1
fi

print_message "Очищаем кэш конфигурации..."
./vendor/bin/sail artisan config:clear

print_message "Очищаем кэш приложения..."
./vendor/bin/sail artisan cache:clear

print_message "Очищаем кэш представлений..."
./vendor/bin/sail artisan view:clear

print_message "Очищаем кэш маршрутов..."
./vendor/bin/sail artisan route:clear

print_message "Очищаем кэш событий..."
./vendor/bin/sail artisan event:clear

print_message "Очищаем скомпилированные классы..."
./vendor/bin/sail artisan clear-compiled

print_message "Оптимизируем автозагрузку Composer..."
./vendor/bin/sail composer dump-autoload --optimize

print_message "�� Все кэши очищены!" 