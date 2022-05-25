import '@/plugins/vue-composition-api'
import '@resources/sass/styles/styles.scss'
import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import router from './router'
import store from './store'
import { initializeApp } from 'firebase/app'
import { getAnalytics } from 'firebase/analytics'

Vue.config.productionTip = false

const firebaseConfig = {
  apiKey: "AIzaSyAE06u1IcBQzSudLmFlcE0PmanlT-AT4Q8",
  authDomain: "temporalcms.firebaseapp.com",
  projectId: "temporalcms",
  storageBucket: "temporalcms.appspot.com",
  messagingSenderId: "655250966417",
  appId: "1:655250966417:web:b7b17dfcf299053a767c30",
  measurementId: "G-PP6SBYTRLW"
};

const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);

new Vue({
  router,
  store,
  vuetify,
  render: h => h(App),
}).$mount('#app')

