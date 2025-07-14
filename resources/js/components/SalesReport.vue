<template>
  <div class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
          Отчеты по продажам
        </h2>
        <p class="mt-4 text-lg text-gray-600">
          Аналитика продаж за последние 30 дней
        </p>
      </div>

      <!-- Фильтры -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Дата от
            </label>
            <input
              v-model="salesStore.filters.dateFrom"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Дата до
            </label>
            <input
              v-model="salesStore.filters.dateTo"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Категория
            </label>
            <select
              v-model="salesStore.filters.category"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Все категории</option>
              <option v-for="category in salesStore.categories" :key="category" :value="category">
                {{ category }}
              </option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="salesStore.resetFilters"
            class="mr-3 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
          >
            Сбросить
          </button>
          <button
            @click="applyFilters"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
          >
            Применить
          </button>
        </div>
      </div>

      <!-- Таблица -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">Данные о продажах</h3>
          <!-- Индикатор загрузки пагинации -->
          <div v-if="salesStore.paginationLoading" class="flex items-center text-indigo-600">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600 mr-2"></div>
            <span class="text-sm">Загрузка...</span>
          </div>
        </div>
        
        <!-- Состояние загрузки (только для первичной загрузки) -->
        <div v-if="salesStore.loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <span class="ml-2 text-gray-600">Загрузка...</span>
        </div>

        <!-- Ошибка -->
        <div v-else-if="salesStore.error" class="p-6 text-center">
          <div class="text-red-600 mb-2">Ошибка загрузки данных</div>
          <button 
            @click="salesStore.fetchSalesData()"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
          >
            Повторить
          </button>
        </div>

        <!-- Данные -->
        <div v-else class="overflow-x-auto">
          <!-- Добавляем прозрачность при загрузке пагинации -->
          <div :class="{ 'opacity-60 pointer-events-none': salesStore.paginationLoading }" 
               class="transition-opacity duration-300">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Товар
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Категория
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Количество
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Цена
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Дата продажи
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="sale in salesStore.salesData" :key="sale.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ sale.product_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="salesStore.getCategoryColor(sale.category)">
                      {{ sale.category }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ sale.quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ salesStore.formatCurrency(sale.price * sale.quantity) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ salesStore.formatDate(sale.sale_date) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Пагинация -->
        <div v-if="!salesStore.loading && !salesStore.error && salesStore.paginationData.total > 0" 
             class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="salesStore.prevPage"
                :disabled="salesStore.currentPage === 1 || salesStore.paginationLoading"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
              >
                Назад
              </button>
              <button
                @click="salesStore.nextPage"
                :disabled="salesStore.currentPage === salesStore.totalPages || salesStore.paginationLoading"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
              >
                Вперед
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Показано <span class="font-medium">{{ salesStore.startIndex }}</span> - 
                  <span class="font-medium">{{ salesStore.endIndex }}</span> из 
                  <span class="font-medium">{{ salesStore.paginationData.total }}</span> записей
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <button
                    @click="salesStore.prevPage"
                    :disabled="salesStore.currentPage === 1 || salesStore.paginationLoading"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                  >
                    ←
                  </button>
                  <button
                    v-for="page in salesStore.visiblePages"
                    :key="page"
                    @click="salesStore.setCurrentPage(page)"
                    :disabled="salesStore.paginationLoading"
                    :class="[
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium disabled:cursor-not-allowed transition-all duration-200',
                      page === salesStore.currentPage
                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <button
                    @click="salesStore.nextPage"
                    :disabled="salesStore.currentPage === salesStore.totalPages || salesStore.paginationLoading"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                  >
                    →
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- График -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Динамика продаж</h3>
        <div class="h-64">
          <canvas ref="chartCanvas"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import { useSalesStore } from '../stores/sales'

Chart.register(...registerables)

// Используем Pinia store
const salesStore = useSalesStore()

// График
const chartCanvas = ref(null)
let chart = null

const updateChart = () => {
  if (!chartCanvas.value) return

  const ctx = chartCanvas.value.getContext('2d')
  const chartData = salesStore.chartData

  if (chart) {
    chart.destroy()
  }

  chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: chartData.labels,
      datasets: [{
        label: 'Продажи (₽)',
        data: chartData.data,
        borderColor: 'rgb(99, 102, 241)',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        tension: 0.1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: 'Динамика продаж по датам'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 0
              }).format(value)
            }
          }
        }
      }
    }
  })
}

const applyFilters = async () => {
  await salesStore.setFilters(salesStore.filters)
  await salesStore.fetchChartData()
  updateChart()
}

// Следим за изменениями данных графика
watch(
  () => salesStore.chartData, () => {
    updateChart()
  },
  { deep: true }
)

// Загружаем данные при монтировании компонента
onMounted(async () => {
  await salesStore.fetchSalesData()
  await salesStore.fetchChartData()
  nextTick(() => {
    updateChart()
  })
})
</script> 