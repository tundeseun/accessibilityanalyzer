<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 px-4 text-center">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="display-4 text-white font-extrabold mb-2 shadow-lg">
        HTML <span class="text-warning">Accessibility</span> Analyzer
      </h1>
      <p class="text-lg text-yellow-200 font-medium">
        Upload your HTML file and analyze for compliance and accessibility issues!
      </p>
    </div>

   <!-- Centered Upload Form -->
<div
  class="d-flex justify-content-center align-items-center "
  style="background-color: #f8f9fa;"
>
  <div class="card shadow-lg p-4" style="width: 30rem;">
    <div class="card-body">
      <form @submit.prevent="uploadFile" class="text-center">
        <div class="mb-3">
          <label for="htmlFile" class="form-label text-gray-700 fw-bold">
            Upload HTML File
          </label>
          <input
            type="file"
            id="htmlFile"
            @change="handleFileInput"
            accept=".html,.htm"
            class="form-control"
          />
        </div>
        <button
          type="submit"
          :disabled="!selectedFile"
          class="btn btn-gradient-primary w-100 py-2 fw-bold"
        >
          Analyze File
        </button>
      </form>
    </div>
  </div>
</div>

    <!-- Loading Animation -->
    <div v-if="loading" class="mt-5">
      <div class="spinner-border text-light mb-3" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="text-light lead">Analyzing your file... Please wait.</p>
    </div>

    <!-- Results Section -->
    <div
      v-if="results"
      class="card shadow-lg mt-5 p-4"
      style="width: 75rem; background: linear-gradient(to right, #fff, #f8f9fa);"
    >
      <div class="card-body">
        <h2 class="h5 fw-bold text-secondary mb-4">Analysis Results</h2>
        <p class="mb-3">
          <strong>Compliance Score:</strong>
          <span :class="complianceClass">{{ results.compliance_score }}%</span>
        </p>
        <ul class="list-group">
          <li
            v-for="(issue, index) in results.issues"
            :key="index"
            class="list-group-item bg-light border-secondary rounded my-2 shadow-sm"
          >
            <strong class="text-primary">{{ issue.type }}:</strong>
            <pre class="bg-light p-3 rounded text-muted mt-2 overflow-auto">
{{ issue.element }}
            </pre>
            <small class="text-muted fst-italic">{{ issue.suggestion }}</small>
          </li>
        </ul>
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="error"
      class="alert alert-danger mt-4"
      role="alert"
    >
      {{ error }}
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      selectedFile: null,
      loading: false,
      results: null,
      error: null,
    };
  },
  methods: {
    handleFileInput(event) {
      this.selectedFile = event.target.files[0];
      this.results = null;
      this.error = null;
    },
    async uploadFile() {
      if (!this.selectedFile) {
        this.error = "Please select a file to upload.";
        return;
      }

      const formData = new FormData();
      formData.append("file", this.selectedFile);

      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post("/api/analyze", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });
        this.results = response.data;
      } catch (err) {
        this.error =
          err.response?.data?.message || "An error occurred while analyzing the file.";
      } finally {
        this.loading = false;
      }
    },
  },
  computed: {
    complianceClass() {
      if (!this.results) return "";
      return this.results.compliance_score >= 80
        ? "text-success fw-bold"
        : this.results.compliance_score >= 50
        ? "text-warning fw-bold"
        : "text-danger fw-bold";
    },
  },
};
</script>

<style scoped>
/* Add a gradient for the button */
.btn-gradient-primary {
  background: linear-gradient(to right, #6a11cb, #2575fc);
  border: none;
  color: white;
}

.btn-gradient-primary:hover {
  background: linear-gradient(to right, #4b0db2, #1d5ed0);
}

/* Pre-wrap for better text display */
pre {
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
