import { createApp } from 'vue'
import './style.css'
import router from './router/index.js'
import App from './App.vue'

// Import vue-toastification
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

/* import specific icons */
import { fas } from '@fortawesome/free-solid-svg-icons'
import 'flowbite';
/* add icons to the library */
library.add(fas)

const app = createApp(App)

// Setup router
app.use(router)

// Setup toastification
app.use(Toast, {
  transition: "Vue-Toastification__bounce",
  maxToasts: 20,
  newestOnTop: true,
  position: "top-right",
  timeout: 5000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: "button",
  icon: true,
  rtl: false
})

app.component('font-awesome-icon', FontAwesomeIcon)
app.mount('#app')
