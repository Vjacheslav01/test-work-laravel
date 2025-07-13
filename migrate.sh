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
    echo -e "${BLUE}  Управление миграциями${NC}"
    echo -e "${BLUE}================================${NC}"
}

show_usage() {
    echo "Использование:"
    echo "  $0 run              - Применить миграции"
    echo "  $0 rollback         - Откатить последнюю миграцию"
    echo "  $0 reset            - Откатить все миграции"
    echo "  $0 fresh            - Удалить все таблицы и применить миграции заново"
    echo "  $0 status           - Показать статус миграций"
    echo "  $0 make <name>      - Создать новую миграцию"
}

# Проверяем, что контейнеры запущены
check_sail() {
    if ! ./vendor/bin/sail ps | grep -q "Up"; then
        print_error "Laravel Sail не запущен. Сначала запустите: ./start.sh"
        exit 1
    fi
}

print_header

case "$1" in
    run)
        check_sail
        print_message "Применяем миграции..."
        ./vendor/bin/sail artisan migrate
        ;;
    rollback)
        check_sail
        print_warning "Откатываем последнюю миграцию..."
        ./vendor/bin/sail artisan migrate:rollback
        ;;
    reset)
        check_sail
        print_warning "Откатываем все миграции..."
        ./vendor/bin/sail artisan migrate:reset
        ;;
    fresh)
        check_sail
        print_warning "Удаляем все таблицы и применяем миграции заново..."
        ./vendor/bin/sail artisan migrate:fresh
        ;;
    status)
        check_sail
        print_message "Статус миграций:"
        ./vendor/bin/sail artisan migrate:status
        ;;
    make)
        check_sail
        if [ -z "$2" ]; then
            print_error "Укажите название миграции"
            echo "Пример: $0 make create_users_table"
            exit 1
        fi
        print_message "Создаем миграцию: $2"
        ./vendor/bin/sail artisan make:migration $2
        ;;
    *)
        show_usage
        exit 1
        ;;
esac 