import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { useAuth } from './stores/auth'

const app = createApp(App)
app.use(router)

const { init } = useAuth()
init().finally(() => app.mount('#app'))
