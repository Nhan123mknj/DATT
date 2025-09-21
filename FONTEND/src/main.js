import { createApp } from 'vue'
import './style.css'
import router from './router/index.js'
import App from './App.vue'
/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

/* import specific icons */
import { fas } from '@fortawesome/free-solid-svg-icons'

/* add icons to the library */
library.add(fas)
const app = createApp(App)
app.use(router)
app.component('font-awesome-icon', FontAwesomeIcon).mount('#app')
