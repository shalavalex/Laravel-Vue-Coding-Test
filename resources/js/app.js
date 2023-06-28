import "./bootstrap";
import "primevue/resources/themes/saga-blue/theme.css";
import "primevue/resources/primevue.min.css";
import "primeicons/primeicons.css";
import "../sass/app.scss";

import { createApp } from "vue";
import VueAxios from "vue-axios";
import axios from "axios";
import PrimeVue from "primevue/config";

import App from "./App.vue";

const app = createApp(App);

app.use(VueAxios, axios);
app.use(PrimeVue);

app.mount("#app");
