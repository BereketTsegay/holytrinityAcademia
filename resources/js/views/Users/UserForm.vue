<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
          {{ isEdit ? 'Edit User' : 'Create User' }}
        </h1>
        <p class="text-gray-600 mt-2">
          {{ isEdit ? 'Update user information and permissions' : 'Create a new user account' }}
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">User Information</h3>
        </div>

        <div class="p-6 space-y-6">
          <!-- Personal Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700">
                First Name *
              </label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                required
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
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
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.last_name }"
              >
              <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">
                {{ errors.last_name[0] }}
              </p>
            </div>

            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">
                Email Address *
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                :readonly="isEdit"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                :class="[{ 'border-red-300': errors.email }, isEdit ? 'bg-gray-50' : '']"
              >
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                {{ errors.email[0] }}
              </p>
            </div>

            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700">
                Phone Number
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
            </div>

            <div>
              <label for="date_of_birth" class="block text-sm font-medium text-gray-700">
                Date of Birth
              </label>
              <input
                id="date_of_birth"
                v-model="form.date_of_birth"
                type="date"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
            </div>

            <div>
              <label for="gender" class="block text-sm font-medium text-gray-700">
                Gender
              </label>
              <select
                id="gender"
                v-model="form.gender"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>

          <!-- Role and Status -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="role" class="block text-sm font-medium text-gray-700">
                Role *
              </label>
              <select
                id="role"
                v-model="form.role"
                required
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.role }"
              >
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
              </select>
              <p v-if="errors.role" class="mt-1 text-sm text-red-600">
                {{ errors.role[0] }}
              </p>
            </div>

            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">
                Status *
              </label>
              <select
                id="status"
                v-model="form.status"
                required
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-if="form.role === 'teacher'">
              <label for="qualification" class="block text-sm font-medium text-gray-700">
                Qualification
              </label>
              <input
                id="qualification"
                v-model="form.qualification"
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., M.Ed, B.Sc"
              >
            </div>

            <div v-if="form.role === 'teacher'">
              <label for="specialization" class="block text-sm font-medium text-gray-700">
                Specialization
              </label>
              <input
                id="specialization"
                v-model="form.specialization"
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., Mathematics, Science"
              >
            </div>
          </div>

          <div>
            <label for="address" class="block text-sm font-medium text-gray-700">
              Address
            </label>
            <textarea
              id="address"
              v-model="form.address"
              rows="3"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>

          <!-- Invitation Option (for create only) -->
          <div v-if="!isEdit" class="flex items-center">
            <input
              id="send_invitation"
              v-model="form.send_invitation"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label for="send_invitation" class="ml-2 block text-sm text-gray-900">
              Send invitation email to this user
            </label>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
          <router-link
            to="/users"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancel
          </router-link>
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
            {{ isEdit ? 'Update User' : 'Create User' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserForm',
  props: {
    userId: {
      type: [String, Number],
      default: null
    }
  },
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        role: '',
        status: 'active',
        date_of_birth: '',
        gender: '',
        address: '',
        qualification: '',
        specialization: '',
        send_invitation: true
      },
      errors: {},
      loading: false
    }
  },
  computed: {
    isEdit() {
      return !!this.userId
    }
  },
  async mounted() {
    if (this.isEdit) {
      await this.fetchUser()
    }
  },
  methods: {
    async fetchUser() {
      try {
        const response = await this.$http.get(`/api/users/${this.userId}`)
        const user = response.data
        
        this.form = {
          first_name: user.first_name,
          last_name: user.last_name,
          email: user.email,
          phone: user.phone || '',
          role: user.roles[0]?.name || '',
          status: user.status,
          date_of_birth: user.date_of_birth || '',
          gender: user.gender || '',
          address: user.address || '',
          qualification: user.qualification || '',
          specialization: user.specialization || '',
          send_invitation: false
        }
      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to load user data'
        })
        this.$router.push('/users')
      }
    },

    async submitForm() {
      this.loading = true
      this.errors = {}

      try {
        const url = this.isEdit ? `/api/users/${this.userId}` : '/api/users'
        const method = this.isEdit ? 'put' : 'post'

        await this.$http[method](url, this.form)
        
        this.$notify({
          type: 'success',
          title: 'Success',
          text: `User ${this.isEdit ? 'updated' : 'created'} successfully`
        })

        this.$router.push('/users')

      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: error.response?.data?.message || `Failed to ${this.isEdit ? 'update' : 'create'} user`
          })
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>