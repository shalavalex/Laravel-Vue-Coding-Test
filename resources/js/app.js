import "./bootstrap";
import { createApp } from "vue";
import App from "./App.vue";
import VueAxios from "vue-axios";
import axios from "axios";

createApp(App).use(VueAxios, axios).mount("#app");
