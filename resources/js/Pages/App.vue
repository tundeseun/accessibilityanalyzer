<template>
    <div id="app">
      <AnalyzeAccessibility />
    </div>
  </template>
  
  <script>
  import AnalyzeAccessibility from "./Pages/AccessibilityAnalyzer.vue";
  
  export default {
    components: {
      AnalyzeAccessibility,
    },
  };
  </script>
  
  <style>
  body {
    font-family: "Arial", sans-serif;
    background-color: #f4f4f4;
  }
  </style>
  