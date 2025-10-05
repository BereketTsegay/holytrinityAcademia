<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
        <p class="text-gray-600 mt-2">Manage users, roles, and permissions</p>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-3">
        <router-link
          to="/users/invite"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
          <UserAddIcon class="w-4 h-4 mr-2" />
          Invite User
        </router-link>
        <router-link
          to="/users/roles"
          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center"
        >
          <ShieldCheckIcon class="w-4 h-4 mr-2" />
          Manage Roles
        </router-link>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in stats" :key="stat.name" 
           class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full" :class="stat.bgColor">
            <component :is="stat.icon" class="w-6 h-6" :class="stat.iconColor" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">{{ stat.name }}</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-64">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search by name, email, or ID..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
        </div>
        
        <div class="min-w-48">
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select v-model="filters.role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Roles</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="min-w-48">
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>

        <div class="flex space-x-2">
          <button
            @click="resetFilters"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Reset
          </button>
          <button
            @click="applyFilters"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Apply
          </button>
        </div>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedUsers.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <span class="text-blue-700 font-medium">
            {{ selectedUsers.length }} user(s) selected
          </span>
        </div>
        <div class="flex space-x-2">
          <select v-model="bulkAction" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
            <option value="">Bulk Actions</option>
            <option value="activate">Activate</option>
            <option value="deactivate">Deactivate</option>
            <option value="suspend">Suspend</option>
            <option value="delete">Delete</option>
          </select>
          <button
            @click="executeBulkAction"
            :disabled="!bulkAction"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            Apply
          </button>
          <button
            @click="clearSelection"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Clear
          </button>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  @change="toggleSelectAll"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
              </th>
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
                Last Login
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in users.data" :key="user.id" 
                :class="selectedUsers.includes(user.id) ? 'bg-blue-50' : ''">
              <td class="px-6 py-4 whitespace-nowrap">
                <input
                  type="checkbox"
                  :value="user.id"
                  v-model="selectedUsers"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img
                      :src="user.photo_url"
                      :alt="user.full_name"
                      class="h-10 w-10 rounded-full object-cover"
                    >
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ user.first_name }} {{ user.last_name }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ user.email }}
                    </div>
                    <div class="text-xs text-gray-400">
                      {{ user.student_id || user.employee_id }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  v-for="role in user.roles"
                  :key="role.id"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize mr-1"
                  :class="getRoleBadgeClass(role.name)"
                >
                  {{ role.name }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                  :class="getStatusBadgeClass(user.status)"
                >
                  {{ user.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(user.last_login_at) || 'Never' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <router-link
                    :to="`/users/${user.id}`"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View
                  </router-link>
                  <router-link
                    :to="`/users/${user.id}/edit`"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </router-link>
                  <button
                    v-if="!user.email_verified_at"
                    @click="sendInvitation(user)"
                    class="text-green-600 hover:text-green-900"
                  >
                    Resend Invite
                  </button>
                  <button
                    v-if="user.id !== currentUser.id"
                    @click="deleteUser(user)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Loading users...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="users.data.length === 0" class="p-8 text-center">
        <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
        <p class="mt-1 text-sm text-gray-500">
          Get started by inviting a new user.
        </p>
        <div class="mt-6">
          <router-link
            to="/users/invite"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            <UserAddIcon class="w-4 h-4 mr-2" />
            Invite User
          </router-link>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="users.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="flex-1 flex justify-between items-center">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ users.from }}</span>
                to
                <span class="font-medium">{{ users.to }}</span>
                of
                <span class="font-medium">{{ users.total }}</span>
                results
              </p>
            </div>
            <div class="flex space-x-2">
              <button
                @click="previousPage"
                :disabled="!users.prev_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                Previous
              </button>
              <button
                @click="nextPage"
                :disabled="!users.next_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  UserGroupIcon,
  UserAddIcon,
  ShieldCheckIcon,
  AcademicCapIcon,
  UserIcon
} from '@heroicons/vue/outline'

export default {
  name: 'UsersIndex',
  components: {
    UserGroupIcon,
    UserAddIcon,
    ShieldCheckIcon,
    AcademicCapIcon,
    UserIcon
  },
  data() {
    return {
      users: { data: [] },
      stats: [],
      filters: {
        search: '',
        role: '',
        status: ''
      },
      selectedUsers: [],
      bulkAction: '',
      loading: false,
      currentPage: 1
    }
  },
  computed: {
    currentUser() {
      return this.$store.getters.user
    },
    allSelected() {
      return this.users.data.length > 0 && 
             this.selectedUsers.length === this.users.data.length
    }
  },
  async mounted() {
    await this.fetchUsers()
    await this.fetchStats()
  },
  methods: {
    async fetchUsers() {
      this.loading = true
      try {
        const params = {
          page: this.currentPage,
          ...this.filters
        }
        
        const response = await this.$http.get('/api/users', { params })
        this.users = response.data
      } catch (error) {
        console.error('Error fetching users:', error)
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to load users'
        })
      } finally {
        this.loading = false
      }
    },

    async fetchStats() {
      try {
        const response = await this.$http.get('/api/user-stats')
        this.stats = [
          {
            name: 'Total Users',
            value: response.data.total_users,
            icon: UserGroupIcon,
            bgColor: 'bg-blue-100',
            iconColor: 'text-blue-600'
          },
          {
            name: 'Students',
            value: response.data.total_students,
            icon: AcademicCapIcon,
            bgColor: 'bg-green-100',
            iconColor: 'text-green-600'
          },
          {
            name: 'Teachers',
            value: response.data.total_teachers,
            icon: UserIcon,
            bgColor: 'bg-purple-100',
            iconColor: 'text-purple-600'
          },
          {
            name: 'Active Users',
            value: response.data.active_users,
            icon: ShieldCheckIcon,
            bgColor: 'bg-green-100',
            iconColor: 'text-green-600'
          }
        ]
      } catch (error) {
        console.error('Error fetching stats:', error)
      }
    },

    applyFilters() {
      this.currentPage = 1
      this.fetchUsers()
    },

    resetFilters() {
      this.filters = {
        search: '',
        role: '',
        status: ''
      }
      this.currentPage = 1
      this.fetchUsers()
    },

    toggleSelectAll() {
      if (this.allSelected) {
        this.selectedUsers = []
      } else {
        this.selectedUsers = this.users.data.map(user => user.id)
      }
    },

    clearSelection() {
      this.selectedUsers = []
      this.bulkAction = ''
    },

    async executeBulkAction() {
      if (!this.bulkAction || this.selectedUsers.length === 0) return

      try {
        await this.$http.post('/api/users/bulk-action', {
          user_ids: this.selectedUsers,
          action: this.bulkAction
        })

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Bulk action completed successfully'
        })

        this.clearSelection()
        await this.fetchUsers()
        await this.fetchStats()

      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to execute bulk action'
        })
      }
    },

    async sendInvitation(user) {
      try {
        await this.$http.post(`/api/users/${user.id}/send-invitation`)
        
        this.$notify({
          type: 'success',
          title: 'Invitation Sent',
          text: `Invitation sent to ${user.email}`
        })

      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to send invitation'
        })
      }
    },

    async deleteUser(user) {
      if (!confirm(`Are you sure you want to delete ${user.first_name} ${user.last_name}?`)) {
        return
      }

      try {
        await this.$http.delete(`/api/users/${user.id}`)
        
        this.$notify({
          type: 'success',
          title: 'User Deleted',
          text: 'User deleted successfully'
        })

        await this.fetchUsers()
        await this.fetchStats()

      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to delete user'
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
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    nextPage() {
      if (this.users.next_page_url) {
        this.currentPage++
        this.fetchUsers()
      }
    },

    previousPage() {
      if (this.users.prev_page_url) {
        this.currentPage--
        this.fetchUsers()
      }
    }
  }
}
</script>