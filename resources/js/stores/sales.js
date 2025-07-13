import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useSalesStore = defineStore('sales', () => {
  // Состояние
  const salesData = ref([
    { id: 1, product_name: 'iPhone 14', category: 'Электроника', quantity: 2, price: 65000, sale_date: '2024-01-15' },
    { id: 2, product_name: 'Samsung Galaxy S23', category: 'Электроника', quantity: 1, price: 55000, sale_date: '2024-01-14' },
    { id: 3, product_name: 'Nike Air Force', category: 'Одежда', quantity: 3, price: 8500, sale_date: '2024-01-13' },
    { id: 4, product_name: 'MacBook Pro', category: 'Электроника', quantity: 1, price: 120000, sale_date: '2024-01-12' },
    { id: 5, product_name: 'Adidas Ultraboost', category: 'Одежда', quantity: 2, price: 12000, sale_date: '2024-01-11' },
    { id: 6, product_name: 'Sony WH-1000XM4', category: 'Электроника', quantity: 4, price: 25000, sale_date: '2024-01-10' },
    { id: 7, product_name: 'Levi\'s 501', category: 'Одежда', quantity: 5, price: 4500, sale_date: '2024-01-09' },
    { id: 8, product_name: 'iPad Pro', category: 'Электроника', quantity: 2, price: 75000, sale_date: '2024-01-08' },
    { id: 9, product_name: 'Зеркальная камера Canon', category: 'Электроника', quantity: 1, price: 45000, sale_date: '2024-01-07' },
    { id: 10, product_name: 'Куртка зимняя', category: 'Одежда', quantity: 3, price: 6500, sale_date: '2024-01-06' },
    { id: 11, product_name: 'Кроссовки New Balance', category: 'Одежда', quantity: 2, price: 9500, sale_date: '2024-01-05' },
    { id: 12, product_name: 'Умные часы Apple Watch', category: 'Электроника', quantity: 3, price: 32000, sale_date: '2024-01-04' },
    { id: 13, product_name: 'Джинсы Diesel', category: 'Одежда', quantity: 1, price: 8000, sale_date: '2024-01-03' },
    { id: 14, product_name: 'Наушники AirPods Pro', category: 'Электроника', quantity: 5, price: 18000, sale_date: '2024-01-02' },
    { id: 15, product_name: 'Свитер Zara', category: 'Одежда', quantity: 4, price: 3500, sale_date: '2024-01-01' },
    { id: 16, product_name: 'PlayStation 5', category: 'Электроника', quantity: 1, price: 45000, sale_date: '2024-01-16' },
    { id: 17, product_name: 'Кроссовки Puma', category: 'Одежда', quantity: 2, price: 7500, sale_date: '2024-01-17' },
    { id: 18, product_name: 'Xiaomi Mi 11', category: 'Электроника', quantity: 3, price: 35000, sale_date: '2024-01-18' },
    { id: 19, product_name: 'Рубашка Hugo Boss', category: 'Одежда', quantity: 1, price: 12000, sale_date: '2024-01-19' },
    { id: 20, product_name: 'Наушники Bose', category: 'Электроника', quantity: 2, price: 28000, sale_date: '2024-01-20' },
  ])

  // Фильтры
  const filters = ref({
    dateFrom: '',
    dateTo: '',
    category: ''
  })

  // Пагинация
  const currentPage = ref(1)
  const itemsPerPage = ref(10)

  // Геттеры
  const categories = computed(() => {
    return [...new Set(salesData.value.map(sale => sale.category))]
  })

  const filteredSales = computed(() => {
    let filtered = salesData.value

    if (filters.value.dateFrom) {
      filtered = filtered.filter(sale => sale.sale_date >= filters.value.dateFrom)
    }

    if (filters.value.dateTo) {
      filtered = filtered.filter(sale => sale.sale_date <= filters.value.dateTo)
    }

    if (filters.value.category) {
      filtered = filtered.filter(sale => sale.category === filters.value.category)
    }

    return filtered
  })

  const paginatedSales = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredSales.value.slice(start, end)
  })

  const totalPages = computed(() => Math.ceil(filteredSales.value.length / itemsPerPage.value))

  const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value + 1)

  const endIndex = computed(() => Math.min(currentPage.value * itemsPerPage.value, filteredSales.value.length))

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

  // Данные для графика
  const chartData = computed(() => {
    const salesByDate = filteredSales.value.reduce((acc, sale) => {
      const date = sale.sale_date
      if (!acc[date]) {
        acc[date] = 0
      }
      acc[date] += sale.quantity * sale.price
      return acc
    }, {})

    const sortedDates = Object.keys(salesByDate).sort()
    return {
      labels: sortedDates.map(date => formatDate(date)),
      data: sortedDates.map(date => salesByDate[date])
    }
  })

  // Экшены
  const setFilters = (newFilters) => {
    Object.assign(filters.value, newFilters)
    currentPage.value = 1
  }

  const resetFilters = () => {
    filters.value = {
      dateFrom: '',
      dateTo: '',
      category: ''
    }
    currentPage.value = 1
  }

  const setCurrentPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
    }
  }

  const nextPage = () => {
    if (currentPage.value < totalPages.value) {
      currentPage.value++
    }
  }

  const prevPage = () => {
    if (currentPage.value > 1) {
      currentPage.value--
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

  // API для загрузки данных (заглушка)
  const fetchSalesData = async () => {
    // Здесь будет API запрос
    // const response = await fetch('/api/sales')
    // salesData.value = await response.json()
  }

  return {
    // Состояние
    salesData,
    filters,
    currentPage,
    itemsPerPage,
    
    // Геттеры
    categories,
    filteredSales,
    paginatedSales,
    totalPages,
    startIndex,
    endIndex,
    visiblePages,
    chartData,
    
    // Экшены
    setFilters,
    resetFilters,
    setCurrentPage,
    nextPage,
    prevPage,
    fetchSalesData,
    
    // Вспомогательные функции
    formatDate,
    formatCurrency,
    getCategoryColor
  }
}) 