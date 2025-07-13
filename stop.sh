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

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}  Остановка Laravel Sail${NC}"
    echo -e "${BLUE}================================${NC}"
}

print_header

print_message "Останавливаем Laravel Sail контейнеры..."
./vendor/bin/sail down

print_message "Контейнеры остановлены и удалены."
print_message "Для полного удаления volumes используйте: ${YELLOW}./vendor/bin/sail down -v${NC}" 