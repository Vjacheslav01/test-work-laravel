<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Overlay -->
    <div class="fixed inset-0 backdrop-blur-sm bg-white/10 transition-opacity" @click="closeModal"></div>
    
    <!-- Modal -->
    <div class="flex min-h-screen items-center justify-center p-4">
      <div class="relative bg-white/95 backdrop-blur-sm rounded-xl shadow-2xl w-full max-w-md transform transition-all">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-xl font-semibold text-gray-900">Авторизация</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="p-6">
          <form @submit.prevent="handleLogin" class="space-y-4">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email
              </label>
              <input
                v-model="form.email"
                id="email"
                type="email"
                required
                :class="{ 'border-red-500': form.errors.email }"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="Введите ваш email"
              />
            </div>
            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
              {{ form.errors.email[0] }}
            </div>

            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Пароль
              </label>
              <input
                v-model="form.password"
                id="password"
                type="password"
                required
                :class="{ 'border-red-500': form.errors.password }"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="Введите ваш пароль"
              />
              <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                {{ form.errors.password[0] }}
              </div>
            </div>

            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input
                  v-model="form.remember"
                  id="remember"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label for="remember" class="ml-2 block text-sm text-gray-700">
                  Запомнить меня
                </label>
              </div>
              <button type="button" class="text-sm text-indigo-600 hover:text-indigo-500">
                Забыли пароль?
              </button>
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-2 px-4 rounded-lg font-medium hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              <span v-if="isLoading" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Вход...
              </span>
              <span v-else>Войти</span>
            </button>
          </form>

          <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
              Нет аккаунта? 
              <button @click="switchToRegister" class="text-indigo-600 hover:text-indigo-500 font-medium">
                Зарегистрируйтесь
              </button>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useUserStore } from '../../stores/user'

const userStore = useUserStore()

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'switch-to-register'])

const isLoading = ref(false)

const form = reactive({
  email: '',
  password: '',
  remember: false,
  errors: []
})

const closeModal = () => {
  emit('close')
}

const switchToRegister = () => {
  emit('switch-to-register')
}

const handleLogin = async () => {
  isLoading.value = true
  
  try {
    const result = await userStore.login(form)
    if (result.success) {
      emit('login-success', result.user)
      form.errors = []
      closeModal()
    } else {
      form.errors = result.errors
    }
  } finally {
    isLoading.value = false
  }
}
</script> 