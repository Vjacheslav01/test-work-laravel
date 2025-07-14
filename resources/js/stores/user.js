import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

// Хранилище для работы с пользователем
export const useUserStore = defineStore('user', () => {
  const user = ref(null)

  // Получение текущего пользователя
  const fetchUser = async () => {
    const response = await api.getUser()
    user.value = response.user
  }

  // Выход из системы
  const logout = async () => {
    await api.logout()
    user.value = null
  }

  // Авторизация
  const login = async (form) => {
    const response = await api.login(form)
    user.value = response.user
    return response
  }

  // Регистрация
  const register = async (form) => {
    const response = await api.register(form)
    user.value = response.user
    return response
  }

  return { user, fetchUser, logout, login, register }
})