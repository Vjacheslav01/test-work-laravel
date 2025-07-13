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
    echo -e "${BLUE}  Запуск Dev Server${NC}"
    echo -e "${BLUE}================================${NC}"
}

print_header

# Проверяем, что контейнеры запущены
if ! ./vendor/bin/sail ps | grep -q "Up"; then
    print_error "Laravel Sail не запущен. Сначала запустите: ./start.sh"
    exit 1
fi

print_message "Запускаем Vite dev server..."
print_message "Приложение будет доступно по адресу: ${GREEN}http://localhost:8080${NC}"
print_message "Vite dev server: ${GREEN}http://localhost:5173${NC}"
print_warning "Для остановки нажмите Ctrl+C"
echo ""

./vendor/bin/sail npm run dev 