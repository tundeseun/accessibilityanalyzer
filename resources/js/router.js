import { createRouter, createWebHistory } from "vue-router";
import AccessibilityAnalyzer from "./Pages/AccessibilityAnalyzer.vue";

// Define routes
const routes = [
  {
    path: "/",
    name: "AccessibilityAnalyzer",
    component: AccessibilityAnalyzer, // Component to render for this route
  },
];

// Create and configure router instance
const router = createRouter({
  history: createWebHistory(), // Uses the browser's history API for clean URLs
  routes, // Register routes
});

export default router; // Export the router for use in the main app
