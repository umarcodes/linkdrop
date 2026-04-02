<template>
  <RouterView />
  <ToastList />
</template>

<script setup>
import ToastList from './components/ToastList.vue'
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap');

*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html, body, #app {
  height: 100%;
}

body {
  font-family: 'DM Sans', sans-serif;
  background: #0a0a0f;
  color: #e8e8f0;
  -webkit-font-smoothing: antialiased;
}

::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #0a0a0f; }
::-webkit-scrollbar-thumb { background: #1e1e2e; border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: #7c6af7; }
</style>
