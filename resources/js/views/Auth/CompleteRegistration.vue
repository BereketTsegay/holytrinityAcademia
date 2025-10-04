<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <div class="mx-auto h-12 w-12 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-sm">SM</span>
        </div>
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
          Complete Your Registration
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Please complete your profile information to continue
        </p>
      </div>

      <!-- Registration Form -->
      <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow" @submit.prevent="completeRegistration">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Personal Information -->
          <div class="md:col-span-2">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
          </div>

          <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">
              First Name *
            </label>
            <input
              id="first_name"
              v-model="form.first_name"
              type="text"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': errors.first_name }"
            >
            <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">
              {{ errors.first_name[0] }}
            </p>
          </div>

          <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">
              Last Name *
            </label>
            <input
              id="last_name"
              v-model="form.last_name"
              type="text"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': errors.last_name }"
            >
            <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">
              {{ errors.last_name[0] }}
            </p>
          </div>

          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">
              Phone Number *
            </label>
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': errors.phone }"
            >
            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">
              {{ errors.phone[0] }}
            </p>
          </div>

          <div>
            <label for="date_of_birth" class="block text-sm font-medium text-gray-700">
              Date of Birth *
            </label>
            <input
              id="date_of_birth"
              v-model="form.date_of_birth"
              type="date"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': errors.date_of_birth }"
            >
            <p v-if="errors.date_of_birth" class="mt-1 text-sm text-red-600">
              {{ errors.date_of_birth[0] }}
            </p>
          </div>

          <div>
            <label for="gender" class="block text-sm font-medium text-gray-700">
              Gender *
            </label>
            <select
              id="gender"
              v-model="form.gender"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': errors.gender }"
            >
              <option value="">Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
            <p v-if="errors.gender" class="mt-1 text-sm text-red-600">
              {{ errors.gender[0] }}
            </p>
          </div>

          <div class="md:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700">
              Address *
            </label>
            <textarea
              id="address"
              v-model="form.address"
              rows="3"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': errors.address }"
            ></textarea>
            <p v-if="errors.address" class="mt-1 text-sm text-red-600">
              {{ errors.address[0] }}
            </p>
          </div>

          <!-- Password Section -->
          <div class="md:col-span-2 border-t pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Set Your Password</h3>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              New Password *
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': errors.password }"
            >
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password[0] }}
            </p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirm Password *
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="text-sm">
            <router-link to="/login" class="font-medium text-blue-600 hover:text-blue-500">
              Back to login
            </router-link>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="loading">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            <span v-else>Complete Registration</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CompleteRegistration',
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        phone: '',
        date_of_birth: '',
        gender: '',
        address: '',
        password: '',
        password_confirmation: ''
      },
      errors: {},
      loading: false
    }
  },
  methods: {
    async completeRegistration() {
      this.loading = true
      this.errors = {}

      try {
        await this.$http.post('/api/complete-registration', this.form)

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Registration completed successfully!'
        })

        this.$router.push('/')
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'Failed to complete registration'
          })
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
