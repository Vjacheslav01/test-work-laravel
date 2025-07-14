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
            class="mr-3 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors cursor-pointer"
          >
            Сбросить
          </button>
          <button
            @click="applyFilters"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors cursor-pointer"
          >
            Применить
          </button>
        </div>
      </div>

      <!-- Excel импорт блок -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Импорт данных из Excel</h3>
            <p class="text-sm text-gray-600">Загрузите файл с тремя листами: Categories, Products, Sales</p>
          </div>
          <button
            @click="downloadTemplate"
            class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors cursor-pointer"
          >
            📥 Скачать шаблон
          </button>
        </div>

        <!-- Drag & Drop зона -->
        <div
          @drop="handleDrop"
          @dragover.prevent
          @dragenter.prevent
          @dragleave="isDragOver = false"
          @dragover="isDragOver = true"
          :class="[
            'border-2 border-dashed rounded-lg p-8 text-center transition-all duration-200',
            isDragOver 
              ? 'border-indigo-500 bg-indigo-50' 
              : 'border-gray-300 hover:border-indigo-400 hover:bg-gray-50'
          ]"
        >
          <div v-if="!selectedFile" class="space-y-4">
            <div class="mx-auto w-12 h-12 text-gray-400">
              <svg fill="none" stroke="currentColor" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div>
              <p class="text-lg font-medium text-gray-700">Перетащите Excel файл сюда</p>
              <p class="text-sm text-gray-500 mt-1">или</p>
              <label class="mt-2 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 cursor-pointer transition-colors">
                <input
                  ref="fileInput"
                  type="file"
                  accept=".xlsx,.xls,.csv"
                  @change="handleFileSelect"
                  class="hidden"
                />
                Выберите файл
              </label>
            </div>
            <p class="text-xs text-gray-400">Поддерживаются форматы: .xlsx, .xls, .csv (макс. 10MB)</p>
          </div>

          <!-- Выбранный файл -->
          <div v-else class="space-y-4">
            <div class="flex items-center justify-center space-x-3">
              <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <div class="text-left">
                <p class="font-medium text-gray-900">{{ selectedFile.name }}</p>
                <p class="text-sm text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
              </div>
            </div>

            <!-- Прогресс бар -->
            <div v-if="uploadProgress > 0" class="w-full">
              <div class="flex justify-between text-sm text-gray-700 mb-1">
                <span>Загрузка...</span>
                <span>{{ uploadProgress }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-indigo-600 h-2 rounded-full transition-all duration-300 ease-out"
                  :style="{ width: uploadProgress + '%' }"
                ></div>
              </div>
            </div>

            <!-- Кнопки действий -->
            <div class="flex justify-center space-x-3">
              <button
                @click="validateFile"
                :disabled="isValidating || isUploading"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <span v-if="isValidating" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Проверка...
                </span>
                <span v-else>Проверить файл</span>
              </button>
              
              <button
                @click="uploadFile"
                :disabled="isUploading || isValidating"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <span v-if="isUploading" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Загрузка...
                </span>
                <span v-else>Импортировать</span>
              </button>
              
              <button
                @click="clearFile"
                :disabled="isUploading || isValidating"
                class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Отменить
              </button>
            </div>
          </div>
        </div>

        <!-- Результаты валидации -->
        <div v-if="validationResult" class="mt-4 p-4 rounded-lg" :class="validationResult.valid ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg v-if="validationResult.valid" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              <svg v-else class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm font-medium" :class="validationResult.valid ? 'text-green-800' : 'text-red-800'">
                {{ validationResult.message }}
              </p>
              
              <!-- Ошибки валидации многолистового файла -->
              <div v-if="!validationResult.valid && validationResult.errors?.length" class="mt-2">
                <p class="text-sm text-red-700 mb-1">Найденные ошибки:</p>
                <ul class="list-disc list-inside text-xs text-red-600 space-y-1">
                  <li v-for="error in validationResult.errors" :key="error">{{ error }}</li>
                </ul>
              </div>

              <!-- Ожидаемая структура файла -->
              <div v-if="!validationResult.valid && validationResult.expected_structure" class="mt-3">
                <p class="text-sm text-red-700 mb-2">Ожидаемая структура файла:</p>
                <div class="space-y-2">
                  <div v-for="(headers, sheetName) in validationResult.expected_structure" :key="sheetName"
                       class="bg-white rounded border p-2">
                    <p class="text-xs font-medium text-gray-800 mb-1">Лист "{{ sheetName }}":</p>
                    <div class="flex flex-wrap gap-1">
                      <span v-for="(description, header) in headers" :key="header"
                            class="text-xs bg-gray-100 px-2 py-1 rounded">
                        {{ header }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Результаты импорта -->
        <div v-if="importResult" class="mt-4 p-4 rounded-lg" :class="importResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg v-if="importResult.success" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              <svg v-else class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm font-medium" :class="importResult.success ? 'text-green-800' : 'text-red-800'">
                {{ importResult.message }}
              </p>
              
              <!-- Статистика импорта -->
              <div v-if="importResult.stats" class="mt-2">
                <!-- Общая статистика -->
                <div v-if="importResult.stats.total" class="grid grid-cols-3 gap-4 text-xs mb-4">
                  <div class="text-center p-2 bg-white rounded">
                    <div class="font-semibold text-gray-900">{{ importResult.stats.total.total_rows }}</div>
                    <div class="text-gray-600">Всего строк</div>
                  </div>
                  <div class="text-center p-2 bg-white rounded">
                    <div class="font-semibold text-green-600">{{ importResult.stats.total.success_count }}</div>
                    <div class="text-gray-600">Успешно</div>
                  </div>
                  <div class="text-center p-2 bg-white rounded">
                    <div class="font-semibold text-red-600">{{ importResult.stats.total.error_count }}</div>
                    <div class="text-gray-600">Ошибок</div>
                  </div>
                </div>

                <!-- Статистика по листам -->
                <div v-if="importResult.stats.sheets" class="space-y-2">
                  <h4 class="text-xs font-medium text-gray-700 mb-2">Статистика по листам:</h4>
                  <div v-for="(sheetStats, sheetName) in importResult.stats.sheets" :key="sheetName" 
                       class="bg-white rounded border p-2">
                    <div class="flex justify-between items-center">
                      <span class="text-xs font-medium text-gray-800 capitalize">{{ sheetName }}</span>
                      <div class="flex space-x-2 text-xs">
                        <span class="text-green-600">{{ sheetStats.success_count }}</span>
                        <span class="text-gray-400">/</span>
                        <span class="text-red-600">{{ sheetStats.error_count }}</span>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-600">{{ sheetStats.total_rows }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Детали ошибок -->
              <div v-if="importResult.stats?.total?.errors?.length" class="mt-3">
                <button 
                  @click="showErrors = !showErrors"
                  class="text-xs text-red-700 hover:text-red-900 font-medium"
                >
                  {{ showErrors ? 'Скрыть' : 'Показать' }} ошибки ({{ importResult.stats.total.errors.length }})
                </button>
                
                <div v-if="showErrors" class="mt-2 max-h-40 overflow-y-auto bg-white rounded border">
                  <div v-for="(error, index) in importResult.stats.total.errors.slice(0, 10)" :key="index" class="p-2 border-b text-xs">
                    <div class="flex justify-between items-start">
                      <span class="font-medium text-red-700">{{ error.sheet }} - Строка {{ error.row }}:</span>
                    </div>
                    <span class="text-red-600">{{ Object.values(error.errors).flat().join(', ') }}</span>
                  </div>
                  <div v-if="importResult.stats.total.errors.length > 10" class="p-2 text-xs text-gray-500 text-center">
                    И еще {{ importResult.stats.total.errors.length - 10 }} ошибок...
                  </div>
                </div>
              </div>
            </div>
          </div>
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
import api from '../services/api'

Chart.register(...registerables)

// Используем Pinia store
const salesStore = useSalesStore()

// График
const chartCanvas = ref(null)
let chart = null

// Excel импорт состояние
const selectedFile = ref(null)
const fileInput = ref(null)
const isDragOver = ref(false)
const isUploading = ref(false)
const isValidating = ref(false)
const uploadProgress = ref(0)
const validationResult = ref(null)
const importResult = ref(null)
const showErrors = ref(false)

// Функции для Excel импорта
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    clearResults()
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragOver.value = false
  
  const files = event.dataTransfer.files
  if (files.length > 0) {
    selectedFile.value = files[0]
    clearResults()
  }
}

const clearFile = () => {
  selectedFile.value = null
  uploadProgress.value = 0
  clearResults()
  
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const clearResults = () => {
  validationResult.value = null
  importResult.value = null
  showErrors.value = false
}

const validateFile = async () => {
  if (!selectedFile.value) return
  
  isValidating.value = true
  clearResults()
  
  try {
    const result = await api.validateExcelFile(selectedFile.value)
    console.log(result)
    validationResult.value = {
      success: result.success,
      message: result.message,
      valid: true
    }
  } catch (error) {
    validationResult.value = {
      success: false,
      message: 'Ошибка при валидации файла: ' + error.message,
      valid: false
    }
  } finally {
    isValidating.value = false
  }
}

const uploadFile = async () => {
  if (!selectedFile.value) return
  
  isUploading.value = true
  uploadProgress.value = 0
  clearResults()
  
  try {
    const result = await api.importExcelFile(selectedFile.value, (progressEvent) => {
      if (progressEvent.lengthComputable) {
        uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
      }
    })
    
    importResult.value = result
    
    if (result.success) {
      // Обновляем данные продаж после успешного импорта
      await salesStore.fetchSalesData()
      await salesStore.fetchChartData()
      updateChart()
      
      // Очищаем файл после успешного импорта
      setTimeout(() => {
        clearFile()
      }, 3000)
    }
  } catch (error) {
    importResult.value = {
      success: false,
      message: 'Ошибка при импорте файла: ' + error.message
    }
  } finally {
    isUploading.value = false
    uploadProgress.value = 0
  }
}

const downloadTemplate = async () => {
  try {
    await api.downloadExcelTemplate()
  } catch (error) {
    console.error('Ошибка при скачивании шаблона:', error)
    alert('Ошибка при скачивании шаблона')
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

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