<template>
    <div class="container mt-5">
      <div class="card shadow p-4">
        <h2 class="text-center text-primary mb-4">Accessibility Analyzer</h2>
        <form @submit.prevent="uploadFile" class="mb-4">
          <div class="mb-3">
            <input 
              type="file" 
              class="form-control" 
              @change="onFileChange" 
              accept=".html" 
            />
          </div>
          <button 
            type="submit" 
            class="btn btn-primary w-100" 
            :disabled="!file"
          >
            Analyze
          </button>
        </form>
  
        <div v-if="loading" class="d-flex justify-content-center align-items-center">
          <div class="spinner-border text-primary" role="status"></div>
          <span class="ms-2">Analyzing...</span>
        </div>
  
        <div v-if="response" class="mt-4">
          <h3 class="text-success text-center">Compliance Score: {{ response.compliance_score }}%</h3>
          <div v-if="response.issues.length === 0" class="alert alert-success mt-3">
            No accessibility issues detected. Great job!
          </div>
          <div v-else>
            <h4 class="mt-4">Issues Found:</h4>
            <ul class="list-group mt-3">
              <li 
                v-for="(issue, index) in response.issues" 
                :key="index" 
                class="list-group-item"
              >
                <strong>{{ issue.type }}</strong>: {{ issue.suggestion }}
                <pre class="bg-light p-2 mt-2">{{ issue.element }}</pre>
                <button 
                  class="btn btn-outline-info btn-sm mt-2" 
                  @click="openModal(issue)"
                >
                  Highlight
                </button>
              </li>
            </ul>
          </div>
        </div>
  
        <div v-if="error" class="alert alert-danger mt-4">
          <p>Error: {{ error }}</p>
        </div>
      </div>
  
      <!-- Modal -->
      <div v-if="isModalVisible" class="modal d-block" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel"><b>Issues Found</b></h5>
              <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <h5 class="modal-title" >{{ modalIssue.type }}</h5>
              <pre class="bg-light p-2 mt-2">{{ modalIssue.element }}</pre>
              <h5 class="mt-3"><b>Suggestions:</b></h5>
              <p>{{ modalIssue.suggestion || 'No detailed solution provided.' }}</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    data() {
      return {
        file: null,
        response: null,
        error: null,
        loading: false,
        isModalVisible: false,
        modalIssue: null,
      };
    },
    methods: {
      onFileChange(event) {
        this.file = event.target.files[0];
      },
      async uploadFile() {
        if (!this.file) {
          this.error = "No file selected.";
          return;
        }
  
        const formData = new FormData();
        formData.append("file", this.file);
  
        this.loading = true;
        this.error = null;
        this.response = null;
  
        try {
          const res = await axios.post("http://localhost:8000/api/analyze", formData, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          });
          this.response = res.data;
        } catch (err) {
          this.error = err.response?.data?.message || "Failed to analyze the file.";
        } finally {
          this.loading = false;
        }
      },
      openModal(issue) {
        this.modalIssue = issue;
        this.isModalVisible = true;
      },
      closeModal() {
        this.isModalVisible = false;
        this.modalIssue = null;
      },
    },
  };
  </script>
  
  <style scoped>
  .card {
    border-radius: 10px;
    background-color: #ffffff;
  }
  pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
  }
  .spinner-border {
    width: 2rem;
    height: 2rem;
  }
  .modal {
    display: block;
    z-index: 1050;
  }
  .modal-dialog {
    max-width: 600px;
    margin: 30px auto;
  }
  </style>
  