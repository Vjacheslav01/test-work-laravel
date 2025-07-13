<template>
  <div id="app">
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50">
      <!-- Навигация -->
      <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex items-center">
              <div class="flex-shrink-0 flex items-center">
                <div class="h-8 w-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                  <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                </div>
                <span class="ml-2 text-xl font-bold text-gray-900">Laravel + Vue</span>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <ButtonAuth @open-login="openLoginModal" />
              <ButtonRegister @open-register="openRegisterModal" />
            </div>
          </div>
        </div>
      </nav>

      <!-- Основной контент -->
      <main>
        <!-- Герой секция -->
        <div class="relative overflow-hidden">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
              <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                <span class="block">Добро пожаловать в</span>
                <span class="block text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-purple-600">
                  Laravel + Vue.js 3
                </span>
              </h1>
              <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Современный стек разработки с Laravel Sail, Vue.js 3, Pinia и Tailwind CSS.
                Готов к разработке из коробки!
              </p>
            </div>
          </div>

          <!-- Декоративные элементы -->
          <div class="absolute inset-y-0 right-0 -z-10 opacity-20">
            <svg class="h-full w-full text-indigo-500" fill="none" viewBox="0 0 100 100" preserveAspectRatio="none">
              <polygon fill="currentColor" points="50,0 100,0 50,100 0,100"/>
            </svg>
          </div>
        </div>

        <!-- Тестовое задание -->
        <div class="py-12 bg-white/50">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
              <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
               Встречайте, готовое тестовое задание!
              </p>
            </div>

            <div class="mt-10">
              <div class="grid grid-cols-2 gap-8 md:grid-cols-6">
                
              </div>
            </div>
          </div>
        </div>

        <!-- Отчет по продажам -->
        <SalesReport />

        <!-- Технологии -->
        <div class="py-12 bg-white/50">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
              <h2 class="text-base text-indigo-600 font-semibold tracking-wide uppercase">Технологии</h2>
              <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Технологии, которые используются в тестовом задании
              </p>
            </div>

            <div class="mt-10">
              <div class="grid grid-cols-2 gap-8 md:grid-cols-6">
                <div v-for="tech in technologies" :key="tech.name" class="col-span-1 flex justify-center md:col-span-1">
                  <div class="flex flex-col items-center p-4 rounded-lg hover:bg-white/70 transition-colors">
                    <div class="text-3xl mb-2">{{ tech.icon }}</div>
                    <span class="text-sm font-medium text-gray-600">{{ tech.name }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>  

        <!-- Полезные ссылки -->
        <div class="bg-gray-50 py-16">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
              <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Полезные ссылки
              </h2>
              <p class="mt-4 text-lg text-gray-600">
                Документация и ресурсы для разработки
              </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
              <div v-for="link in links" :key="link.title" class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <a :href="link.url" target="_blank" class="block p-6">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="text-2xl">{{ link.icon }}</div>
                    </div>
                    <div class="ml-4">
                      <h3 class="text-lg font-medium text-gray-900">{{ link.title }}</h3>
                      <p class="mt-2 text-sm text-gray-500">{{ link.description }}</p>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- Подвал -->
      <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
          <div class="text-center">
            <p class="text-sm text-gray-500">
              Создано с ❤️ используя Laravel, Vue.js 3, Pinia и Tailwind CSS
            </p>
            <p class="mt-2 text-xs text-gray-400">
              Готов к разработке • Laravel Sail • Docker • Vite
            </p>
          </div>
        </div>
      </footer>
    </div>

    <!-- Модальные окна -->
    <Suspense>
      <template #default>
        <LoginModal 
          :isOpen="modals.login" 
          @close="closeModals" 
          @switch-to-register="switchToRegister"
        />
      </template>
      <template #fallback>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div class="text-white">Загрузка...</div>
        </div>
      </template>
    </Suspense>

    <Suspense>
      <template #default>
        <RegisterModal 
          :isOpen="modals.register" 
          @close="closeModals" 
          @switch-to-login="switchToLogin"
        />
      </template>
      <template #fallback>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div class="text-white">Загрузка...</div>
        </div>
      </template>
    </Suspense>
  </div>
</template>

<script setup>
import { ref, reactive, defineAsyncComponent } from 'vue'
import ButtonAuth from './components/buttons/ButtonAuth.vue'
import ButtonRegister from './components/buttons/ButtonRegister.vue'
import SalesReport from './components/SalesReport.vue'

// Асинхронная загрузка модалок
const LoginModal = defineAsyncComponent(() => import('./components/modals/LoginModal.vue'))
const RegisterModal = defineAsyncComponent(() => import('./components/modals/RegisterModal.vue'))

// Состояние модалок
const modals = reactive({
  login: false,
  register: false
})

// Методы для управления модалками
const openLoginModal = () => {
  closeModals()
  modals.login = true
}

const openRegisterModal = () => {
  closeModals()
  modals.register = true
}

const closeModals = () => {
  modals.login = false
  modals.register = false
}

const switchToRegister = () => {
  modals.login = false
  modals.register = true
}

const switchToLogin = () => {
  modals.register = false
  modals.login = true
}

// Технологии
const technologies = ref([
  { name: 'Laravel', icon: '🚀' },
  { name: 'Vue.js 3', icon: '💚' },
  { name: 'Pinia', icon: '🍍' },
  { name: 'Tailwind', icon: '🎨' },
  { name: 'Vite', icon: '⚡' },
  { name: 'Docker', icon: '🐳' }
])

// Полезные ссылки
const links = ref([
  {
    title: 'Laravel Документация',
    description: 'Официальная документация Laravel',
    icon: '📚',
    url: 'https://laravel.com/docs'
  },
  {
    title: 'Vue.js Руководство',
    description: 'Изучите Vue.js 3 и Composition API',
    icon: '📖',
    url: 'https://vuejs.org/guide/'
  },
  {
    title: 'Pinia Store',
    description: 'Управление состоянием для Vue.js',
    icon: '🗄️',
    url: 'https://pinia.vuejs.org/'
  },
  {
    title: 'Tailwind CSS',
    description: 'Utility-first CSS фреймворк',
    icon: '🎨',
    url: 'https://tailwindcss.com/docs'
  },
  {
    title: 'Laravel Sail',
    description: 'Docker окружение для Laravel',
    icon: '⛵',
    url: 'https://laravel.com/docs/sail'
  },
  {
    title: 'Vite',
    description: 'Быстрый сборщик модулей',
    icon: '⚡',
    url: 'https://vitejs.dev/'
  }
])
</script> 