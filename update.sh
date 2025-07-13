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

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}  Обновление зависимостей${NC}"
    echo -e "${BLUE}================================${NC}"
}

print_header

# Проверяем, что контейнеры запущены
if ! ./vendor/bin/sail ps | grep -q "Up"; then
    print_error "Laravel Sail не запущен. Сначала запустите: ./start.sh"
    exit 1
fi

print_message "Обновляем зависимости Composer..."
./vendor/bin/sail composer update

print_message "Обновляем зависимости NPM..."
./vendor/bin/sail npm update

print_message "Применяем новые миграции (если есть)..."
./vendor/bin/sail artisan migrate

print_message "Очищаем кэш..."
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan view:clear

print_message "Пересобираем фронтенд..."
./vendor/bin/sail npm run build

print_message "🎉 Зависимости обновлены!"
print_warning "Не забудьте перезапустить dev server если он был запущен" 