<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div>
        <div class="mx-auto h-12 w-12 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-sm">SM</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Set new password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link to="/login" class="font-medium text-blue-600 hover:text-blue-500">
            back to login
          </router-link>
        </p>
      </div>

      <!-- Token Validation Messages -->
      <div v-if="tokenStatus === 'validating'" class="rounded-md bg-blue-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">
              Validating reset link...
            </h3>
          </div>
        </div>
      </div>

      <div v-else-if="tokenStatus === 'invalid'" class="rounded-md bg-red-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <ExclamationCircleIcon class="h-5 w-5 text-red-400" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">
              Invalid or expired reset link
            </h3>
            <div class="mt-2 text-sm text-red-700">
              <p>This password reset link is invalid or has expired.</p>
              <p class="mt-2">
                <router-link to="/forgot-password" class="font-medium text-red-600 hover:text-red-500">
                  Request a new reset link
                </router-link>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Success Message -->
      <div v-else-if="success" class="rounded-md bg-green-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <CheckCircleIcon class="h-5 w-5 text-green-400" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800">
              Password reset successful
            </h3>
            <div class="mt-2 text-sm text-green-700">
              <p>Your password has been reset successfully.</p>
              <p class="mt-2">
                <router-link to="/login" class="font-medium text-green-600 hover:text-green-500">
                  Proceed to login
                </router-link>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Reset Password Form -->
      <form v-else-if="tokenStatus === 'valid'" class="mt-8 space-y-6" @submit.prevent="resetPassword">
        <div class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              readonly
              class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-gray-50 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              New Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': errors.password }"
              placeholder="Enter new password"
            >
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password[0] }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
              Password must be at least 8 characters long
            </p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirm New Password
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="Confirm new password"
            >
          </div>
        </div>

        <!-- Password Strength Indicator -->
        <div v-if="form.password" class="bg-gray-50 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-900 mb-2">Password Strength</h4>
          <div class="space-y-2">
            <div class="flex items-center">
              <CheckCircleIcon
                class="w-4 h-4 mr-2"
                :class="passwordStrength.length ? 'text-green-500' : 'text-gray-300'"
              />
              <span class="text-sm" :class="passwordStrength.length ? 'text-gray-700' : 'text-gray-500'">
                At least 8 characters
              </span>
            </div>
            <div class="flex items-center">
              <CheckCircleIcon
                class="w-4 h-4 mr-2"
                :class="passwordStrength.mixedCase ? 'text-green-500' : 'text-gray-300'"
              />
              <span class="text-sm" :class="passwordStrength.mixedCase ? 'text-gray-700' : 'text-gray-500'">
                Upper & lower case letters
              </span>
            </div>
            <div class="flex items-center">
              <CheckCircleIcon
                class="w-4 h-4 mr-2"
                :class="passwordStrength.numbers ? 'text-green-500' : 'text-gray-300'"
              />
              <span class="text-sm" :class="passwordStrength.numbers ? 'text-gray-700' : 'text-gray-500'">
                Contains numbers
              </span>
            </div>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading || !isFormValid"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            <span v-else>Reset Password</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { CheckCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/outline'

export default {
  name: 'ResetPassword',
  components: {
    CheckCircleIcon,
    ExclamationCircleIcon
  },
  data() {
    return {
      form: {
        token: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      errors: {},
      loading: false,
      success: false,
      tokenStatus: 'validating' // validating, valid, invalid
    }
  },
  computed: {
    isFormValid() {
      return this.form.password &&
             this.form.password_confirmation &&
             this.form.password === this.form.password_confirmation &&
             this.form.password.length >= 8
    },
    passwordStrength() {
      const password = this.form.password
      return {
        length: password.length >= 8,
        mixedCase: /[a-z]/.test(password) && /[A-Z]/.test(password),
        numbers: /\d/.test(password)
      }
    }
  },
  async mounted() {
    // Get token and email from URL parameters
    const urlParams = new URLSearchParams(window.location.search)
    this.form.token = urlParams.get('token') || this.$route.query.token
    this.form.email = urlParams.get('email') || this.$route.query.email

    if (!this.form.token || !this.form.email) {
      this.tokenStatus = 'invalid'
      return
    }

    await this.validateToken()
  },
  methods: {
    async validateToken() {
      try {
        const response = await this.$http.post('/api/validate-reset-token', {
          token: this.form.token,
          email: this.form.email
        })

        this.tokenStatus = response.data.valid ? 'valid' : 'invalid'
      } catch (error) {
        this.tokenStatus = 'invalid'
        console.error('Token validation failed:', error)
      }
    },

    async resetPassword() {
      this.loading = true
      this.errors = {}

      try {
        await this.$http.post('/api/reset-password', this.form)
        this.success = true

        this.$notify({
          type: 'success',
          title: 'Password Reset',
          text: 'Your password has been reset successfully'
        })

        // Auto-redirect to login after 3 seconds
        setTimeout(() => {
          this.$router.push('/login')
        }, 3000)

      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Reset Failed',
            text: error.response?.data?.message || 'Failed to reset password'
          })
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
