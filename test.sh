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
    echo -e "${BLUE}  Запуск тестов${NC}"
    echo -e "${BLUE}================================${NC}"
}

show_usage() {
    echo "Использование:"
    echo "  $0                  - Запустить все тесты"
    echo "  $0 unit             - Запустить только Unit тесты"
    echo "  $0 feature          - Запустить только Feature тесты"
    echo "  $0 coverage         - Запустить тесты с покрытием кода"
    echo "  $0 <test_name>      - Запустить конкретный тест"
}

print_header

# Проверяем, что контейнеры запущены
if ! ./vendor/bin/sail ps | grep -q "Up"; then
    print_error "Laravel Sail не запущен. Сначала запустите: ./start.sh"
    exit 1
fi

case "$1" in
    "")
        print_message "Запускаем все тесты..."
        ./vendor/bin/sail test
        ;;
    unit)
        print_message "Запускаем Unit тесты..."
        ./vendor/bin/sail test --testsuite=Unit
        ;;
    feature)
        print_message "Запускаем Feature тесты..."
        ./vendor/bin/sail test --testsuite=Feature
        ;;
    coverage)
        print_message "Запускаем тесты с покрытием кода..."
        ./vendor/bin/sail test --coverage
        ;;
    help)
        show_usage
        ;;
    *)
        print_message "Запускаем тест: $1"
        ./vendor/bin/sail test $1
        ;;
esac 