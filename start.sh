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

# === ПЕРВОНАЧАЛЬНАЯ НАСТРОЙКА ПРОЕКТА ===
print_message "Проверяем первоначальную настройку проекта..."

# Проверка и создание .env файла
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        print_message "Создаем .env файл из .env.example..."
        cp .env.example .env
        
        print_message "Настраиваем переменные окружения для Docker..."
        
        # Настройка базы данных для MySQL
        sed -i.bak 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
        
        # Добавляем/обновляем переменные базы данных
        if ! grep -q "^DB_HOST=" .env; then
            echo "DB_HOST=mysql" >> .env
        else
            sed -i.bak 's/^# DB_HOST=.*/DB_HOST=mysql/' .env
            sed -i.bak 's/^DB_HOST=.*/DB_HOST=mysql/' .env
        fi
        
        if ! grep -q "^DB_PORT=" .env; then
            echo "DB_PORT=3306" >> .env
        else
            sed -i.bak 's/^# DB_PORT=.*/DB_PORT=3306/' .env
            sed -i.bak 's/^DB_PORT=.*/DB_PORT=3306/' .env
        fi
        
        if ! grep -q "^DB_DATABASE=" .env; then
            echo "DB_DATABASE=laravel" >> .env
        else
            sed -i.bak 's/^# DB_DATABASE=.*/DB_DATABASE=laravel/' .env
            sed -i.bak 's/^DB_DATABASE=.*/DB_DATABASE=laravel/' .env
        fi
        
        if ! grep -q "^DB_USERNAME=" .env; then
            echo "DB_USERNAME=sail" >> .env
        else
            sed -i.bak 's/^# DB_USERNAME=.*/DB_USERNAME=sail/' .env
            sed -i.bak 's/^DB_USERNAME=.*/DB_USERNAME=sail/' .env
        fi
        
        if ! grep -q "^DB_PASSWORD=" .env; then
            echo "DB_PASSWORD=password" >> .env
        else
            sed -i.bak 's/^# DB_PASSWORD=.*/DB_PASSWORD=password/' .env
            sed -i.bak 's/^DB_PASSWORD=.*/DB_PASSWORD=password/' .env
        fi
        
        # Добавляем переменные для Docker пользователей если их нет
        if ! grep -q "^WWWUSER=" .env; then
            echo "WWWUSER=$(id -u)" >> .env
        fi
        
        if ! grep -q "^WWWGROUP=" .env; then
            echo "WWWGROUP=$(id -g)" >> .env
        fi
        
        # Удаляем backup файл
        rm -f .env.bak
        
        print_message "✓ .env файл настроен для работы с Docker (MySQL, Sail)"
    else
        print_error ".env.example файл не найден! Невозможно создать .env"
        exit 1
    fi
else
    print_message "✓ .env файл уже существует"
    
    # Проверяем, настроен ли .env для Docker
    if grep -q "DB_CONNECTION=sqlite" .env; then
        print_warning "Обнаружена настройка SQLite. Обновляем для работы с Docker MySQL..."
        
        # Обновляем существующий .env для Docker
        sed -i.bak 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
        
        # Обновляем переменные базы данных
        sed -i.bak 's/^# DB_HOST=.*/DB_HOST=mysql/' .env
        sed -i.bak 's/^DB_HOST=.*/DB_HOST=mysql/' .env
        sed -i.bak 's/^# DB_PORT=.*/DB_PORT=3306/' .env  
        sed -i.bak 's/^DB_PORT=.*/DB_PORT=3306/' .env
        sed -i.bak 's/^# DB_DATABASE=.*/DB_DATABASE=laravel/' .env
        sed -i.bak 's/^DB_DATABASE=.*/DB_DATABASE=laravel/' .env
        sed -i.bak 's/^# DB_USERNAME=.*/DB_USERNAME=sail/' .env
        sed -i.bak 's/^DB_USERNAME=.*/DB_USERNAME=sail/' .env
        sed -i.bak 's/^# DB_PASSWORD=.*/DB_PASSWORD=password/' .env
        sed -i.bak 's/^DB_PASSWORD=.*/DB_PASSWORD=password/' .env
        
        # Добавляем переменные для Docker если их нет
        if ! grep -q "^WWWUSER=" .env; then
            echo "WWWUSER=$(id -u)" >> .env
        fi
        
        if ! grep -q "^WWWGROUP=" .env; then
            echo "WWWGROUP=$(id -g)" >> .env
        fi
        
        rm -f .env.bak
        print_message "✓ .env файл обновлен для работы с Docker"
    fi
fi

# Проверка vendor директории и первоначальная установка зависимостей
if [ ! -d "vendor" ] || [ ! -f "vendor/bin/sail" ]; then
    print_warning "Папка vendor не найдена или неполная. Выполняем первоначальную установку..."
    
    # Проверяем, установлен ли Composer локально
    if command -v composer &> /dev/null; then
        print_message "Устанавливаем зависимости Composer локально..."
        composer install --no-interaction
    else
        print_message "Composer не найден локально. Используем Docker для установки зависимостей..."
        
        # Используем Docker для установки composer зависимостей
        if command -v docker &> /dev/null; then
            docker run --rm -v "$(pwd)":/app -w /app composer:latest composer install --no-interaction --ignore-platform-reqs
        else
            print_error "Ни Composer, ни Docker не доступны для установки зависимостей!"
            print_error "Установите Composer локально или убедитесь, что Docker работает."
            exit 1
        fi
    fi
    
    # Проверяем, что sail теперь доступен
    if [ ! -f "vendor/bin/sail" ]; then
        print_error "Laravel Sail не установлен! Проверьте установку зависимостей."
        exit 1
    fi
else
    print_message "✓ Зависимости Composer уже установлены"
fi

# Проверка node_modules (для информации)
if [ ! -d "node_modules" ]; then
    print_warning "Node.js зависимости будут установлены позже через Sail"
else
    print_message "✓ Node.js зависимости найдены"
fi

print_message "Останавливаем предыдущие контейнеры (если они запущены)..."
./vendor/bin/sail down -v

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

# Спрашиваем, хочет ли пользователь запустить dev server
while true; do
    read -p "Хотите заполнить базу тестовыми продуктами? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[YyДд]$ ]]; then
        print_message "Заполняем тестовыми продуктами..."
        ./vendor/bin/sail artisan db:seed --class=ProductsSeeder
        break
    elif [[ $REPLY =~ ^[NnНн]$ ]]; then
        print_message "Тестовые данные не заполнены."
        break
    else
        print_error "Пожалуйста, введите 'y' для да или 'n' для нет"
    fi
done 

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