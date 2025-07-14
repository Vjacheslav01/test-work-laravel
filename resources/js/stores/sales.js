import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useSalesStore = defineStore('sales', () => {
  // Состояние
  const salesData = ref([])
  const loading = ref(false)
  const paginationLoading = ref(false) // Отдельный loader для пагинации
  const error = ref(null)

  // Данные пагинации (приходят с сервера)
  const paginationData = ref({
    current_page: 1,
    from: 0,
    last_page: 1,
    per_page: 10,
    to: 0,
    total: 0
  })

  // Фильтры
  const filters = ref({
    dateFrom: '',
    dateTo: '',
    category: ''
  })

  // Данные для графика
  const chartData = ref({
    labels: [],
    data: []
  })

  // Геттеры
  const categories = computed(() => {
    return [...new Set(salesData.value.map(sale => sale.category))]
  })

  const startIndex = computed(() => paginationData.value.from || 0)
  const endIndex = computed(() => paginationData.value.to || 0)
  const totalPages = computed(() => paginationData.value.last_page || 1)
  const currentPage = computed(() => paginationData.value.current_page || 1)

  const visiblePages = computed(() => {
    const pages = []
    const maxVisible = 5
    let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
    let end = Math.min(totalPages.value, start + maxVisible - 1)
    
    if (end - start + 1 < maxVisible) {
      start = Math.max(1, end - maxVisible + 1)
    }
    
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }
    
    return pages
  })

  // Экшены
  const setFilters = async (newFilters) => {
    Object.assign(filters.value, newFilters)
    await fetchSalesData(1, true) // При изменении фильтров это первичная загрузка
  }

  const resetFilters = async () => {
    filters.value = {
      dateFrom: '',
      dateTo: '',
      category: ''
    }
    await fetchSalesData(1, true) // При сбросе фильтров это первичная загрузка
  }

  const setCurrentPage = async (page) => {
    if (page >= 1 && page <= totalPages.value && !paginationLoading.value) {
      await fetchSalesData(page, false) // false = не первичная загрузка
    }
  }

  const nextPage = async () => {
    if (currentPage.value < totalPages.value && !paginationLoading.value) {
      await fetchSalesData(currentPage.value + 1, false)
    }
  }

  const prevPage = async () => {
    if (currentPage.value > 1 && !paginationLoading.value) {
      await fetchSalesData(currentPage.value - 1, false)
    }
  }

  // Вспомогательные функции
  const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU')
  }

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ru-RU', {
      style: 'currency',
      currency: 'RUB'
    }).format(amount)
  }

  const getCategoryColor = (category) => {
    const colors = {
      'Электроника': 'bg-blue-100 text-blue-800',
      'Одежда': 'bg-green-100 text-green-800',
      'Дом': 'bg-yellow-100 text-yellow-800',
      'Спорт': 'bg-red-100 text-red-800'
    }
    return colors[category] || 'bg-gray-100 text-gray-800'
  }

  // API для загрузки данных
  const fetchSalesData = async (page = 1, isInitialFetch = true) => {
    // Используем разные loaders в зависимости от типа загрузки
    if (isInitialFetch) {
      loading.value = true
    } else {
      paginationLoading.value = true
    }
    error.value = null
    
    try {
      const response = await api.getSalesData(page, filters.value)

      if (response.status === 401) {
        error.value = 'Unauthorized'
        return
      }

      if (response.data) {
        salesData.value = response.data
        // Обновляем данные пагинации
        paginationData.value = {
          current_page: response.meta.current_page,
          from: response.meta.from,
          last_page: response.meta.last_page,
          per_page: response.meta.per_page,
          to: response.meta.to,
          total: response.meta.total
        }
      }
    } catch (err) {
      error.value = err.message || 'Ошибка загрузки данных'
      console.error('Ошибка загрузки продаж:', err)
    } finally {
      if (isInitialFetch) {
        loading.value = false
      } else {
        paginationLoading.value = false
      }
    }
  }
  
  // API для загрузки данных для графика
  const fetchChartData = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await api.getChartData(filters.value)

      if (response.status === 401) {
        error.value = 'Unauthorized'
        return
      }

      if (response.chartData) {
        chartData.value = response.chartData
      }
    } catch (err) {
      error.value = err.message || 'Ошибка загрузки данных графика'
      console.error('Ошибка загрузки данных графика:', err)
    } finally {
      loading.value = false
    }
  }

  return {
    // Состояние
    salesData,
    loading,
    paginationLoading,
    error,
    filters,
    paginationData,
    chartData,
    
    // Геттеры
    categories,
    startIndex,
    endIndex,
    totalPages,
    currentPage,
    visiblePages,
    
    // Экшены
    setFilters,
    resetFilters,
    setCurrentPage,
    nextPage,
    prevPage,
    fetchSalesData,
    fetchChartData,
    
    // Вспомогательные функции
    formatDate,
    formatCurrency,
    getCategoryColor
  }
}) 