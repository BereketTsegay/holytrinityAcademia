<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-3 border-b">
          <h3 class="text-lg font-medium text-gray-900">
            {{ department ? 'Edit Department' : 'Create New Department' }}
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-500"
          >
            <XIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="saveDepartment" class="mt-4 space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
              Department Name *
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-red-300': errors.name }"
              placeholder="Enter department name"
            >
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
              {{ errors.name[0] }}
            </p>
          </div>

          <div>
            <label for="head_of_department" class="block text-sm font-medium text-gray-700">
              Head of Department
            </label>
            <input
              id="head_of_department"
              v-model="form.head_of_department"
              type="text"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter head of department name"
            >
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="contact_email" class="block text-sm font-medium text-gray-700">
                Contact Email
              </label>
              <input
                id="contact_email"
                v-model="form.contact_email"
                type="email"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="department@school.edu"
              >
            </div>

            <div>
              <label for="contact_phone" class="block text-sm font-medium text-gray-700">
                Contact Phone
              </label>
              <input
                id="contact_phone"
                v-model="form.contact_phone"
                type="tel"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="+1 (555) 123-4567"
              >
            </div>
          </div>

          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">
              Description
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter department description..."
            ></textarea>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <span v-if="loading">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              {{ department ? 'Update Department' : 'Create Department' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { XIcon } from '@heroicons/vue/outline'

export default {
  name: 'DepartmentModal',
  components: {
    XIcon
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    department: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      form: {
        name: '',
        description: '',
        head_of_department: '',
        contact_email: '',
        contact_phone: ''
      },
      errors: {},
      loading: false
    }
  },
  watch: {
    department: {
      handler(newVal) {
        if (newVal) {
          this.form = { ...newVal }
        } else {
          this.resetForm()
        }
      },
      immediate: true
    },
    show: {
      handler(newVal) {
        if (!newVal) {
          this.resetForm()
          this.errors = {}
        }
      }
    }
  },
  methods: {
    resetForm() {
      this.form = {
        name: '',
        description: '',
        head_of_department: '',
        contact_email: '',
        contact_phone: ''
      }
    },
    async saveDepartment() {
      this.loading = true
      this.errors = {}

      try {
        const url = this.department ? `/api/departments/${this.department.id}` : '/api/departments'
        const method = this.department ? 'put' : 'post'

        await this.$http[method](url, this.form)
        
        this.$notify({
          type: 'success',
          title: 'Success',
          text: `Department ${this.department ? 'updated' : 'created'} successfully`
        })

        this.$emit('saved')
        this.$emit('close')

      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: error.response?.data?.message || `Failed to ${this.department ? 'update' : 'create'} department`
          })
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>