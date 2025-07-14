<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Overlay -->
    <div class="fixed inset-0 backdrop-blur-sm bg-white/10 transition-opacity" @click="closeModal"></div>
    
    <!-- Modal -->
    <div class="flex min-h-screen items-center justify-center p-4">
      <div class="relative bg-white/95 backdrop-blur-sm rounded-xl shadow-2xl w-full max-w-md transform transition-all">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-xl font-semibold text-gray-900">Регистрация</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="p-6">
          <form @submit.prevent="handleRegister" class="space-y-4">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Имя
              </label>
              <input
                v-model="form.name"
                id="name"
                type="text"
                required
                :class="{ 'border-red-500': form.errors.name }"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="Введите ваше имя"
              />
            </div>
            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                {{ form.errors.name[0] }}
            </div>

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
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="Введите пароль"
              />
            </div>
            <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                {{ form.errors.password[0] }}
            </div>
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Подтверждение пароля
              </label>
              <input
                v-model="form.password_confirmation"
                id="password_confirmation"
                type="password"
                required
                :class="{ 'border-red-500': form.errors.password_confirmation }"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="Подтвердите пароль"
              />
            </div>
            <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">
                {{ form.errors.password_confirmation[0] }}
            </div>

            <div class="flex items-center">
              <input
                v-model="form.terms"
                id="terms"
                type="checkbox"
                required
                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
              />
              <label for="terms" class="ml-2 block text-sm text-gray-700">
                Я согласен с 
                <a href="#" class="text-green-600 hover:text-green-500">условиями использования</a>
              </label>
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-2 px-4 rounded-lg font-medium hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              <span v-if="isLoading" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Регистрация...
              </span>
              <span v-else>Зарегистрироваться</span>
            </button>
          </form>

          <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
              Уже есть аккаунт? 
              <button @click="switchToLogin" class="text-green-600 hover:text-green-500 font-medium">
                Войдите
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

const emit = defineEmits(['close', 'switch-to-login'])

const isLoading = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
  errors: []
})

const closeModal = () => {
  emit('close')
}

const switchToLogin = () => {
  emit('switch-to-login')
}

const handleRegister = async () => {
  if (form.password !== form.password_confirmation) {
    alert('Пароли не совпадают')
    return
  }

  isLoading.value = true
  
  try {
    const result = await userStore.register(form)

    if (result.success) {
      emit('register-success', result.user)
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