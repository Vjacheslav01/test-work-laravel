#!/bin/bash

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функция для вывода сообщений
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
    echo -e "${BLUE}  Laravel + Vue.js 3 + Pinia${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Проверка, что мы в правильной директории
if [ ! -f "composer.json" ] || [ ! -f "artisan" ]; then
    print_error "Этот скрипт должен запускаться из корневой директории Laravel проекта!"
    exit 1
fi

print_header

# Проверка, установлен ли Docker
if ! command -v docker &> /dev/null; then
    print_error "Docker не установлен. Пожалуйста, установите Docker и Docker Compose."
    exit 1
fi

# Проверка, запущен ли Docker
if ! docker info &> /dev/null; then
    print_error "Docker не запущен. Пожалуйста, запустите Docker."
    exit 1
fi

print_message "Останавливаем предыдущие контейнеры (если они запущены)..."
./vendor/bin/sail down

print_message "Запускаем Laravel Sail..."
./vendor/bin/sail up -d

print_message "Ожидаем запуска контейнеров..."
sleep 10

# Проверяем, что контейнеры запустились
if ! ./vendor/bin/sail ps | grep -q "Up"; then
    print_error "Контейнеры не запустились. Проверьте логи: ./vendor/bin/sail logs"
    exit 1
fi

print_message "Устанавливаем/обновляем зависимости Composer..."
./vendor/bin/sail composer install

print_message "Устанавливаем/обновляем зависимости NPM..."
./vendor/bin/sail npm install

print_message "Применяем миграции базы данных..."
./vendor/bin/sail artisan migrate

print_message "Генерируем ключ приложения (если нужно)..."
./vendor/bin/sail artisan key:generate

print_message "Очищаем кэш..."
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan view:clear

print_message "Собираем фронтенд для production..."
./vendor/bin/sail npm run build

print_header
print_message "🚀 Проект успешно запущен!"
echo ""
print_message "📱 Приложение доступно по адресу: ${GREEN}http://localhost:8080${NC}"
print_message "🛠️  Для разработки запустите: ${YELLOW}./vendor/bin/sail npm run dev${NC}"
print_message "📧 Почтовый клиент Mailpit: ${GREEN}http://localhost:8025${NC}"
print_message "🔍 Meilisearch: ${GREEN}http://localhost:7700${NC}"
echo ""
print_message "Полезные команды:"
echo "  • Остановить: ${YELLOW}./vendor/bin/sail down${NC}"
echo "  • Перезапустить: ${YELLOW}./vendor/bin/sail restart${NC}"
echo "  • Логи: ${YELLOW}./vendor/bin/sail logs${NC}"
echo "  • Войти в контейнер: ${YELLOW}./vendor/bin/sail bash${NC}"
echo "  • Запустить тесты: ${YELLOW}./vendor/bin/sail test${NC}"
echo ""

# Спрашиваем, хочет ли пользователь запустить dev server
while true; do
    read -p "Хотите запустить Vite dev server для разработки? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[YyДд]$ ]]; then
        print_message "Запускаем Vite dev server..."
        print_warning "Для остановки нажмите Ctrl+C"
        ./vendor/bin/sail npm run dev
        break
    elif [[ $REPLY =~ ^[NnНн]$ ]]; then
        print_message "Vite dev server не запущен. Для запуска используйте: ${YELLOW}./dev.sh${NC}"
        break
    else
        print_error "Пожалуйста, введите 'y' для да или 'n' для нет"
    fi
done 