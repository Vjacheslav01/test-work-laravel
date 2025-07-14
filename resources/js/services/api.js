import axios from 'axios'

// Настройка axios для работы с CSRF токенами и куками
axios.defaults.withCredentials = true
axios.defaults.withXSRFToken = true

const api = {
  // Получение CSRF токена
  async getCsrfToken() {
    await axios.get('/api/sanctum/csrf-cookie')
  },

  // Авторизация
  async login(credentials) {
    await this.getCsrfToken()
    const response = await axios.post('/api/auth/login', credentials)
      .then(response => response.data)
      .catch(error => error.response.data)
    return response
  },

  // Регистрация
  async register(userData) {
    await this.getCsrfToken()
    const response = await axios.post('/api/auth/register', userData)
      .then(response => response.data)
      .catch(error => error.response.data)
    return response
  },

  // Выход
  async logout() {
    const response = await axios.post('/api/auth/logout')
    return response.data
  },

  // Получение данных пользователя
  async getUser() {
    const response = await axios.get('/api/auth/user')
    return response.data
  },

  // Тестирование аутентификации
  async checkAuth() {
    const response = await axios.get('/api/check-auth')
    return response.data
  }
}

export default api 