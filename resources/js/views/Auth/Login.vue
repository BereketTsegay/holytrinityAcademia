<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div>
        <div class="mx-auto h-12 w-12 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-sm">SM</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Sign in to your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link to="/register" class="font-medium text-blue-600 hover:text-blue-500">
            create a new account
          </router-link>
        </p>
      </div>

      <!-- Login Form -->
      <form class="mt-8 space-y-6" @submit.prevent="login">
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
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Email address"
              :class="{ 'border-red-300': errors.email }"
            >
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Password"
              :class="{ 'border-red-300': errors.password }"
            >
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password[0] }}
            </p>
          </div>
        </div>

       <!-- Add this in the login form, after the remember me checkbox -->
        <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input
            id="remember-me"
            v-model="form.remember"
            name="remember-me"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
            Remember me
            </label>
        </div>

        <div class="text-sm">
            <router-link to="/forgot-password" class="font-medium text-blue-600 hover:text-blue-500">
            Forgot your password?
            </router-link>
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
            <span v-else>Sign in</span>
          </button>
        </div>

        <!-- Demo Credentials -->
        <div class="mt-6 p-4 bg-gray-100 rounded-lg">
          <h3 class="text-sm font-medium text-gray-900 mb-2">Demo Credentials:</h3>
          <div class="text-xs text-gray-600 space-y-1">
            <p><strong>Admin:</strong> admin@school.com / password</p>
            <p><strong>Teacher:</strong> teacher@school.com / password</p>
            <p><strong>Student:</strong> student@school.com / password</p>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Login',
  data() {
    return {
      form: {
        email: '',
        password: '',
        remember: false
      },
      errors: {},
      loading: false
    }
  },
  methods: {
    async login() {
      this.loading = true
      this.errors = {}

      try {
        await this.$store.dispatch('login', this.form)
        this.$router.push('/')
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Login Failed',
            text: error.response?.data?.message || 'Invalid credentials'
          })
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
