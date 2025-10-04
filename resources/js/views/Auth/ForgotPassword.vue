<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div>
        <div class="mx-auto h-12 w-12 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-sm">SM</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Reset your password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link to="/login" class="font-medium text-blue-600 hover:text-blue-500">
            back to login
          </router-link>
        </p>
      </div>

      <!-- Success Message -->
      <div v-if="success" class="rounded-md bg-green-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <CheckCircleIcon class="h-5 w-5 text-green-400" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800">
              Check your email
            </h3>
            <div class="mt-2 text-sm text-green-700">
              <p>We've sent a password reset link to <strong>{{ form.email }}</strong></p>
              <p class="mt-2">The link will expire in 60 minutes.</p>
            </div>
            <div class="mt-4">
              <button
                @click="resetForm"
                class="text-sm font-medium text-green-600 hover:text-green-500"
              >
                Try another email address
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Forgot Password Form -->
      <form v-else class="mt-8 space-y-6" @submit.prevent="sendResetLink">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">Email address</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Enter your email address"
              :class="{ 'border-red-300': errors.email }"
            >
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="loading">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            <span v-else>Send reset link</span>
          </button>
        </div>

        <!-- Additional Help -->
        <div class="text-center">
          <p class="text-sm text-gray-600">
            Can't find the email? Check your spam folder or
            <button
              type="button"
              @click="resetForm"
              class="font-medium text-blue-600 hover:text-blue-500"
            >
              try another email address
            </button>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { CheckCircleIcon } from '@vue-hero-icons/outline';

export default {
  name: 'ForgotPassword',
  components: {
    CheckCircleIcon
  },
  data() {
    return {
      form: {
        email: ''
      },
      errors: {},
      loading: false,
      success: false
    }
  },
  methods: {
    async sendResetLink() {
      this.loading = true
      this.errors = {}

      try {
        await this.$http.post('/api/forgot-password', this.form)
        this.success = true

        this.$notify({
          type: 'success',
          title: 'Reset Link Sent',
          text: 'Check your email for password reset instructions'
        })
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to send reset link'
          })
        }
      } finally {
        this.loading = false
      }
    },

    resetForm() {
      this.form.email = ''
      this.errors = {}
      this.success = false
    }
  }
}
</script>
