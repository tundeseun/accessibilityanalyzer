import { createApp } from "vue";
import "bootstrap/dist/css/bootstrap.min.css";
import "toastr/build/toastr.min.css";

import App from "./Pages/AccessibilityAnalyzer.vue";
import router from "./router";

const app = createApp(App);

app.use(router); // Use the router instance
app.mount("#app");

          


