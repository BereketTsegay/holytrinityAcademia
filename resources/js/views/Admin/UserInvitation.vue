<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Invite User</h1>
            <p class="text-gray-600 mt-2">Invite new students or teachers to join the school management system</p>
          </div>
          <router-link
            to="/users"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            <ArrowLeftIcon class="w-4 h-4 mr-2" />
            Back to Users
          </router-link>
        </div>
      </div>

      <!-- Success Message -->
      <div v-if="success" class="mb-6 rounded-md bg-green-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <CheckCircleIcon class="h-5 w-5 text-green-400" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800">
              Invitation Sent Successfully
            </h3>
            <div class="mt-2 text-sm text-green-700">
              <p>An invitation email has been sent to <strong>{{ invitedUserEmail }}</strong> with login instructions.</p>
              <p class="mt-1">The user will receive a temporary password and instructions to complete their registration.</p>
            </div>
            <div class="mt-4 flex space-x-3">
              <button
                @click="resetForm"
                class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
              >
                Invite Another User
              </button>
              <router-link
                to="/users"
                class="bg-white text-green-600 px-4 py-2 border border-green-600 rounded-md text-sm font-medium hover:bg-green-50"
              >
                View All Users
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Invitation Form -->
      <div v-else class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">User Information</h3>
          <p class="text-sm text-gray-600 mt-1">Enter the user's details to send an invitation</p>
        </div>

        <form @submit.prevent="sendInvitation" class="p-6 space-y-6">
          <!-- Role Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-4">
              User Role *
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <button
                type="button"
                @click="form.role = 'student'"
                :class="[
                  'p-4 border-2 rounded-lg text-left transition-all duration-200',
                  form.role === 'student'
                    ? 'border-blue-500 bg-blue-50 shadow-sm'
                    : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'
                ]"
              >
                <div class="flex items-start">
                  <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <AcademicCapIcon class="w-6 h-6 text-blue-600" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Student</p>
                    <p class="text-sm text-gray-500 mt-1">For students who will attend classes and access learning materials</p>
                    <ul class="text-xs text-gray-500 mt-2 space-y-1">
                      <li>• Can view classes and attendance</li>
                      <li>• Can borrow library books</li>
                      <li>• Access to personal dashboard</li>
                    </ul>
                  </div>
                </div>
              </button>

              <button
                type="button"
                @click="form.role = 'teacher'"
                :class="[
                  'p-4 border-2 rounded-lg text-left transition-all duration-200',
                  form.role === 'teacher'
                    ? 'border-green-500 bg-green-50 shadow-sm'
                    : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'
                ]"
              >
                <div class="flex items-start">
                  <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <UserGroupIcon class="w-6 h-6 text-green-600" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Teacher</p>
                    <p class="text-sm text-gray-500 mt-1">For teachers who will manage classes and student progress</p>
                    <ul class="text-xs text-gray-500 mt-2 space-y-1">
                      <li>• Can manage classes and subjects</li>
                      <li>• Can take attendance</li>
                      <li>• Access to teacher dashboard</li>
                    </ul>
                  </div>
                </div>
              </button>
            </div>
            <p v-if="errors.role" class="mt-2 text-sm text-red-600">
              {{ errors.role[0] }}
            </p>
          </div>

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
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                :class="{ 'border-red-300': errors.first_name }"
                placeholder="Enter first name"
              >
              <p v-if="errors.first_name" class="mt-2 text-sm text-red-600">
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
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                :class="{ 'border-red-300': errors.last_name }"
                placeholder="Enter last name"
              >
              <p v-if="errors.last_name" class="mt-2 text-sm text-red-600">
                {{ errors.last_name[0] }}
              </p>
            </div>
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
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': errors.email }"
              placeholder="Enter email address"
            >
            <p v-if="errors.email" class="mt-2 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>

          <!-- Additional Fields Based on Role -->
          <div v-if="form.role === 'teacher'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="qualification" class="block text-sm font-medium text-gray-700">
                Qualification
              </label>
              <input
                id="qualification"
                v-model="form.qualification"
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="e.g., M.Ed, B.Sc, PhD"
              >
            </div>

            <div>
              <label for="specialization" class="block text-sm font-medium text-gray-700">
                Specialization
              </label>
              <input
                id="specialization"
                v-model="form.specialization"
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="e.g., Mathematics, Science, English"
              >
            </div>
          </div>

          <!-- Student-specific Fields -->
          <div v-if="form.role === 'student'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="date_of_birth" class="block text-sm font-medium text-gray-700">
                Date of Birth
              </label>
              <input
                id="date_of_birth"
                v-model="form.date_of_birth"
                type="date"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
            </div>

            <div>
              <label for="gender" class="block text-sm font-medium text-gray-700">
                Gender
              </label>
              <select
                id="gender"
                v-model="form.gender"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>

          <!-- Contact Information -->
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">
              Phone Number
            </label>
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="+1 (555) 123-4567"
            >
          </div>

          <div>
            <label for="address" class="block text-sm font-medium text-gray-700">
              Address
            </label>
            <textarea
              id="address"
              v-model="form.address"
              rows="3"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="Enter complete address..."
            ></textarea>
          </div>

          <!-- Additional Information -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">
              Additional Notes (Optional)
            </label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="Any additional information about the user, special requirements, or notes for the invitation..."
            ></textarea>
          </div>

          <!-- Invitation Preview -->
          <div v-if="form.first_name && form.email" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
              <EyeIcon class="w-4 h-4 mr-2" />
              Invitation Preview
            </h4>
            <div class="text-sm text-gray-600 space-y-2">
              <div class="flex">
                <span class="font-medium w-24">Name:</span>
                <span>{{ form.first_name }} {{ form.last_name }}</span>
              </div>
              <div class="flex">
                <span class="font-medium w-24">Email:</span>
                <span>{{ form.email }}</span>
              </div>
              <div class="flex">
                <span class="font-medium w-24">Role:</span>
                <span class="capitalize">{{ form.role }}</span>
              </div>
              <div class="flex">
                <span class="font-medium w-24">Status:</span>
                <span class="text-green-600">Pending Invitation</span>
              </div>
            </div>
            <div class="mt-3 p-3 bg-blue-50 rounded border border-blue-200">
              <p class="text-xs text-blue-700">
                <InformationCircleIcon class="w-4 h-4 inline mr-1" />
                The user will receive an email with a temporary password and instructions to complete their registration.
                They will be required to set up their profile and change their password on first login.
              </p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-500">
              All fields marked with * are required
            </div>

            <div class="flex space-x-3">
              <button
                type="button"
                @click="resetForm"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <RefreshIcon class="w-4 h-4 mr-2" />
                Reset Form
              </button>
              <button
                type="submit"
                :disabled="loading || !form.role"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <template v-if="loading">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Sending Invitation...
                </template>
                <template v-else>
                  <EnvelopeIcon class="w-4 h-4 mr-2" />
                  Send Invitation
                </template>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Recent Invitations -->
      <div v-if="recentInvitations.length > 0" class="mt-12">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Invitations</h3>
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  User
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Role
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Invited On
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="invitation in recentInvitations" :key="invitation.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                      <UserIcon class="h-5 w-5 text-gray-500" />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ invitation.first_name }} {{ invitation.last_name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ invitation.email }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize" 
                        :class="getRoleBadgeClass(invitation.role_name)">
                    {{ invitation.role_name }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getStatusBadgeClass(invitation.status)">
                    {{ invitation.status }}
                  </span>
                  <div v-if="!invitation.email_verified_at" class="text-xs text-orange-600 mt-1">
                    Pending Verification
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(invitation.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    v-if="!invitation.email_verified_at"
                    @click="resendInvitation(invitation)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    Resend
                  </button>
                  <router-link
                    :to="`/users/${invitation.id}/edit`"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { 
  CheckCircleIcon, 
  AcademicCapIcon, 
  UserGroupIcon, 
  EnvelopeIcon, 
  ArrowLeftIcon,
  UserIcon,
  EyeIcon,
  InformationCircleIcon,
  RefreshIcon
} from '@vue-hero-icons/outline'

export default {
  name: 'UserInvitation',
  components: {
    CheckCircleIcon,
    AcademicCapIcon,
    UserGroupIcon,
    EnvelopeIcon,
    ArrowLeftIcon,
    UserIcon,
    EyeIcon,
    InformationCircleIcon,
    RefreshIcon
  },
  data() {
    return {
      form: {
        role: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        date_of_birth: '',
        gender: '',
        address: '',
        qualification: '',
        specialization: '',
        notes: '',
        status: 'active',
        send_invitation: true
      },
      errors: {},
      loading: false,
      success: false,
      invitedUserEmail: '',
      recentInvitations: []
    }
  },
  async mounted() {
    await this.fetchRecentInvitations()
  },
  methods: {
    async sendInvitation() {
      this.loading = true
      this.errors = {}

      try {
        const response = await this.$http.post('/api/users', this.form)
        
        this.invitedUserEmail = this.form.email
        this.success = true
        
        this.$notify({
          type: 'success',
          title: 'Invitation Sent!',
          text: `Invitation sent successfully to ${this.form.email}. The user will receive an email with login instructions.`
        })

        // Refresh recent invitations
        await this.fetchRecentInvitations()

      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Invitation Failed',
            text: error.response?.data?.message || 'Failed to send invitation. Please try again.'
          })
        }
      } finally {
        this.loading = false
      }
    },

    resetForm() {
      this.form = {
        role: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        date_of_birth: '',
        gender: '',
        address: '',
        qualification: '',
        specialization: '',
        notes: '',
        status: 'active',
        send_invitation: true
      }
      this.errors = {}
      this.success = false
      this.invitedUserEmail = ''
    },

    async fetchRecentInvitations() {
      try {
        const response = await this.$http.get('/api/users?recent_invitations=true&limit=5')
        this.recentInvitations = response.data.data || []
      } catch (error) {
        console.error('Error fetching recent invitations:', error)
      }
    },

    async resendInvitation(user) {
      try {
        await this.$http.post(`/api/users/${user.id}/send-invitation`)
        
        this.$notify({
          type: 'success',
          title: 'Invitation Resent',
          text: `Invitation has been resent to ${user.email}`
        })

        await this.fetchRecentInvitations()

      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to resend invitation'
        })
      }
    },

    getRoleBadgeClass(role) {
      const classes = {
        student: 'bg-blue-100 text-blue-800',
        teacher: 'bg-green-100 text-green-800',
        admin: 'bg-purple-100 text-purple-800'
      }
      return classes[role] || 'bg-gray-100 text-gray-800'
    },

    getStatusBadgeClass(status) {
      const classes = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-yellow-100 text-yellow-800',
        suspended: 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>