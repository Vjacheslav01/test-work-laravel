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
  },

  // Получение данных о продажах
  async getSalesData(page = 1, filters = {}) {
    const params = {
      page,
      per_page: 10,
      ...filters
    }
    
    const response = await axios.get('/api/sales', { params })
      .then(response => response.data)
      .catch(error => error.response?.data || { status: error.response?.status })
    return response
  },

  // Получение данных для графика
  async getChartData(filters = {}) {
    const response = await axios.get('/api/sales/chart', { params: filters })
      .then(response => response.data)
      .catch(error => error.response?.data || { status: error.response?.status })
    return response
  },

  // Импорт Excel файла с продажами
  async importExcelFile(file, onUploadProgress = null) {
    await this.getCsrfToken()
    
    const formData = new FormData()
    formData.append('file', file)

    const config = {
      headers: {
        'Content-Type': 'multipart/form-data',
      }
    }

    if (onUploadProgress) {
      config.onUploadProgress = onUploadProgress
    }

    const response = await axios.post('/api/sales/import', formData, config)
      .then(response => response.data)
      .catch(error => error.response?.data || { success: false, message: 'Ошибка сети' })
    
    return response
  },

  // Валидация Excel файла
  async validateExcelFile(file) {
    await this.getCsrfToken()
    
    const formData = new FormData()
    formData.append('file', file)

    const response = await axios.post('/api/sales/validate-excel', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      }
    })
      .then(response => response.data)
      .catch(error => error.response?.data || { success: false, message: 'Ошибка сети' })
    
    return response
  },

  // Скачивание шаблона Excel
  async downloadExcelTemplate() {
    const response = await axios.get('/api/sales/template', {
      responseType: 'blob'
    })
    
    // Создаем ссылку для скачивания
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'sales_import_template.xlsx')
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    
    return { success: true, message: 'Шаблон загружен' }
  }
}

export default api 