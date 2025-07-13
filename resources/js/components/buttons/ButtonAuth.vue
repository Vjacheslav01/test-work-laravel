<template>
    <button 
      @click="login" 
      class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-lg font-medium cursor-pointer"
    >
      Авторизация
    </button>
</template>

<script setup>
const emit = defineEmits(['open-login'])

const login = () => {
  emit('open-login')
}
</script>