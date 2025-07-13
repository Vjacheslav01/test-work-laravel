<template>
    <button 
      @click="register" 
      class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-lg font-medium cursor-pointer"
    >
      Регистрация
    </button>
</template>

<script setup>
const emit = defineEmits(['open-register'])

const register = () => {
  emit('open-register')
}
</script>