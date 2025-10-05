<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Role & Permission Management</h1>
        <p class="text-gray-600 mt-2">Manage user roles and permissions across the system</p>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-3">
        <button
          @click="showCreateRoleModal = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Create Role
        </button>
        <router-link
          to="/users"
          class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center"
        >
          <ArrowLeftIcon class="w-4 h-4 mr-2" />
          Back to Users
        </router-link>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100">
            <ShieldCheckIcon class="w-6 h-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Roles</p>
            <p class="text-2xl font-semibold text-gray-900">{{ roles.length }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100">
            <KeyIcon class="w-6 h-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Permissions</p>
            <p class="text-2xl font-semibold text-gray-900">{{ totalPermissions }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100">
            <UserGroupIcon class="w-6 h-6 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Admin Users</p>
            <p class="text-2xl font-semibold text-gray-900">{{ userCounts.admin || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-orange-100">
            <AcademicCapIcon class="w-6 h-6 text-orange-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Teacher Users</p>
            <p class="text-2xl font-semibold text-gray-900">{{ userCounts.teacher || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Roles List -->
      <div class="lg:col-span-1">
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">System Roles</h3>
            <p class="text-sm text-gray-600 mt-1">Manage user roles and their permissions</p>
          </div>
          
          <div class="p-4">
            <!-- Search Roles -->
            <div class="mb-4">
              <input
                v-model="roleSearch"
                type="text"
                placeholder="Search roles..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
            </div>

            <!-- Roles List -->
            <div class="space-y-2 max-h-96 overflow-y-auto">
              <div
                v-for="role in filteredRoles"
                :key="role.id"
                @click="selectRole(role)"
                :class="[
                  'p-4 border rounded-lg cursor-pointer transition-colors',
                  selectedRole?.id === role.id
                    ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500 ring-opacity-50'
                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                ]"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900 capitalize">
                      {{ role.name }}
                    </h4>
                    <p class="text-sm text-gray-500 mt-1">
                      {{ role.permissions.length }} permissions
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                      {{ getUserCount(role.name) }} users
                    </p>
                  </div>
                  <div class="flex space-x-1">
                    <button
                      @click.stop="editRole(role)"
                      class="text-gray-400 hover:text-blue-600 p-1"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="!isProtectedRole(role.name)"
                      @click.stop="deleteRole(role)"
                      class="text-gray-400 hover:text-red-600 p-1"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </div>
                
                <!-- Permission Preview -->
                <div class="mt-2">
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="permission in role.permissions.slice(0, 3)"
                      :key="permission.id"
                      class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded"
                    >
                      {{ permission.name }}
                    </span>
                    <span
                      v-if="role.permissions.length > 3"
                      class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-500 rounded"
                    >
                      +{{ role.permissions.length - 3 }} more
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredRoles.length === 0" class="text-center py-8">
              <ShieldExclamationIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">No roles found</h3>
              <p class="mt-1 text-sm text-gray-500">
                Get started by creating a new role.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Role Details & Permissions -->
      <div class="lg:col-span-2">
        <div v-if="selectedRole" class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-medium text-gray-900 capitalize">
                  {{ selectedRole.name }} Role
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                  Manage permissions for this role
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getRoleBadgeClass(selectedRole.name)"
                >
                  {{ getUserCount(selectedRole.name) }} users
                </span>
                <button
                  @click="editRole(selectedRole)"
                  class="text-gray-400 hover:text-blue-600 p-1"
                >
                  <PencilIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <div class="p-6">
            <!-- Permission Groups -->
            <div class="space-y-6">
              <div
                v-for="(permissions, group) in groupedPermissions"
                :key="group"
                class="border border-gray-200 rounded-lg"
              >
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                  <div class="flex items-center justify-between">
                    <h4 class="text-sm font-medium text-gray-900 capitalize">
                      {{ group.replace('_', ' ') }}
                    </h4>
                    <div class="flex items-center space-x-2">
                      <span class="text-xs text-gray-500">
                        {{ getSelectedCount(group) }}/{{ permissions.length }} selected
                      </span>
                      <button
                        @click="toggleGroup(group, permissions)"
                        class="text-xs text-blue-600 hover:text-blue-700"
                      >
                        {{ isGroupSelected(group) ? 'Deselect All' : 'Select All' }}
                      </button>
                    </div>
                  </div>
                </div>
                
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                  <label
                    v-for="permission in permissions"
                    :key="permission.id"
                    class="flex items-start space-x-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer"
                    :class="{
                      'border-blue-200 bg-blue-50': hasPermission(permission.name)
                    }"
                  >
                    <input
                      type="checkbox"
                      :checked="hasPermission(permission.name)"
                      @change="togglePermission(permission.name)"
                      class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900">
                        {{ formatPermissionName(permission.name) }}
                      </p>
                      <p class="text-xs text-gray-500 mt-1">
                        {{ permission.name }}
                      </p>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <!-- Save Changes -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end">
              <button
                @click="saveRolePermissions"
                :disabled="!hasChanges || saving"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              >
                <span v-if="saving">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Saving...
                </span>
                <span v-else>Save Changes</span>
              </button>
            </div>
          </div>
        </div>

        <!-- No Role Selected -->
        <div v-else class="bg-white shadow rounded-lg">
          <div class="text-center py-12">
            <ShieldExclamationIcon class="mx-auto h-16 w-16 text-gray-400" />
            <h3 class="mt-4 text-lg font-medium text-gray-900">No role selected</h3>
            <p class="mt-2 text-sm text-gray-500">
              Select a role from the list to manage its permissions.
            </p>
            <div class="mt-6">
              <button
                @click="showCreateRoleModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
              >
                <PlusIcon class="w-4 h-4 mr-2" />
                Create New Role
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Role Modal -->
    <div v-if="showCreateRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <!-- Modal Header -->
          <div class="flex items-center justify-between pb-3 border-b">
            <h3 class="text-lg font-medium text-gray-900">
              {{ editingRole ? 'Edit Role' : 'Create New Role' }}
            </h3>
            <button
              @click="closeModal"
              class="text-gray-400 hover:text-gray-500"
            >
              <XIcon class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Body -->
          <form @submit.prevent="saveRole" class="mt-4 space-y-4">
            <div>
              <label for="roleName" class="block text-sm font-medium text-gray-700">
                Role Name *
              </label>
              <input
                id="roleName"
                v-model="roleForm.name"
                type="text"
                required
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter role name (e.g., moderator, coordinator)"
                :class="{ 'border-red-300': roleErrors.name }"
              >
              <p v-if="roleErrors.name" class="mt-1 text-sm text-red-600">
                {{ roleErrors.name[0] }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                Use lowercase letters and underscores (e.g., course_coordinator)
              </p>
            </div>

            <!-- Quick Permission Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Quick Permission Sets
              </label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  v-for="preset in permissionPresets"
                  :key="preset.name"
                  @click="applyPermissionPreset(preset.permissions)"
                  class="text-left p-3 border border-gray-200 rounded-md hover:bg-gray-50 text-sm"
                >
                  <div class="font-medium text-gray-900">{{ preset.name }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ preset.description }}</div>
                </button>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="savingRole"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
              >
                <span v-if="savingRole">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ editingRole ? 'Update Role' : 'Create Role' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  ShieldCheckIcon,
  KeyIcon,
  UserGroupIcon,
  AcademicCapIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
  ArrowLeftIcon,
  ShieldExclamationIcon,
  XIcon
} from '@heroicons/vue/outline'

export default {
  name: 'RoleManagement',
  components: {
    ShieldCheckIcon,
    KeyIcon,
    UserGroupIcon,
    AcademicCapIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    ArrowLeftIcon,
    ShieldExclamationIcon,
    XIcon
  },
  data() {
    return {
      roles: [],
      permissions: {},
      selectedRole: null,
      selectedPermissions: [],
      originalPermissions: [],
      roleSearch: '',
      userCounts: {},
      showCreateRoleModal: false,
      editingRole: null,
      roleForm: {
        name: '',
        permissions: []
      },
      roleErrors: {},
      saving: false,
      savingRole: false,
      permissionPresets: [
        {
          name: 'View Only',
          description: 'Can view but not modify',
          permissions: ['view users', 'view classes', 'view attendance', 'view library', 'view reports']
        },
        {
          name: 'Content Manager',
          description: 'Can manage content and users',
          permissions: ['view users', 'create users', 'edit users', 'view classes', 'create classes', 'edit classes']
        },
        {
          name: 'Teacher Basic',
          description: 'Basic teacher permissions',
          permissions: ['view classes', 'view attendance', 'take attendance', 'view library', 'view reports']
        },
        {
          name: 'Administrator',
          description: 'Full system access',
          permissions: ['manage users', 'manage roles', 'manage classes', 'manage attendance', 'manage library', 'manage reports']
        }
      ]
    }
  },
  computed: {
    filteredRoles() {
      if (!this.roleSearch) return this.roles
      return this.roles.filter(role =>
        role.name.toLowerCase().includes(this.roleSearch.toLowerCase())
      )
    },
    totalPermissions() {
      return Object.values(this.permissions).reduce((total, group) => total + group.length, 0)
    },
    groupedPermissions() {
      return this.permissions
    },
    hasChanges() {
      if (!this.selectedRole) return false
      
      const current = this.selectedPermissions.sort()
      const original = this.originalPermissions.sort()
      
      return JSON.stringify(current) !== JSON.stringify(original)
    }
  },
  async mounted() {
    await this.fetchRoles()
    await this.fetchPermissions()
    await this.fetchUserCounts()
  },
  methods: {
    async fetchRoles() {
      try {
        const response = await this.$http.get('/api/roles')
        this.roles = response.data
        
        // Select first role by default
        if (this.roles.length > 0 && !this.selectedRole) {
          this.selectRole(this.roles[0])
        }
      } catch (error) {
        console.error('Error fetching roles:', error)
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to load roles'
        })
      }
    },

    async fetchPermissions() {
      try {
        const response = await this.$http.get('/api/permissions')
        this.permissions = response.data
      } catch (error) {
        console.error('Error fetching permissions:', error)
      }
    },

    async fetchUserCounts() {
      try {
        // This would typically come from an API endpoint
        // For now, we'll count users in each role from the roles data
        const counts = {}
        this.roles.forEach(role => {
          // This is a placeholder - you'd need to implement this based on your API
          counts[role.name] = role.users_count || 0
        })
        this.userCounts = counts
      } catch (error) {
        console.error('Error fetching user counts:', error)
      }
    },

    selectRole(role) {
      this.selectedRole = role
      this.selectedPermissions = [...role.permissions.map(p => p.name)]
      this.originalPermissions = [...this.selectedPermissions]
    },

    hasPermission(permissionName) {
      return this.selectedPermissions.includes(permissionName)
    },

    togglePermission(permissionName) {
      const index = this.selectedPermissions.indexOf(permissionName)
      if (index > -1) {
        this.selectedPermissions.splice(index, 1)
      } else {
        this.selectedPermissions.push(permissionName)
      }
    },

    toggleGroup(group, permissions) {
      const allSelected = this.isGroupSelected(group)
      
      if (allSelected) {
        // Remove all permissions from this group
        permissions.forEach(permission => {
          const index = this.selectedPermissions.indexOf(permission.name)
          if (index > -1) {
            this.selectedPermissions.splice(index, 1)
          }
        })
      } else {
        // Add all permissions from this group
        permissions.forEach(permission => {
          if (!this.selectedPermissions.includes(permission.name)) {
            this.selectedPermissions.push(permission.name)
          }
        })
      }
    },

    isGroupSelected(group) {
      const groupPermissions = this.permissions[group] || []
      return groupPermissions.every(permission => 
        this.selectedPermissions.includes(permission.name)
      )
    },

    getSelectedCount(group) {
      const groupPermissions = this.permissions[group] || []
      return groupPermissions.filter(permission => 
        this.selectedPermissions.includes(permission.name)
      ).length
    },

    async saveRolePermissions() {
      if (!this.selectedRole || !this.hasChanges) return

      this.saving = true
      try {
        await this.$http.put(`/api/roles/${this.selectedRole.id}`, {
          permissions: this.selectedPermissions
        })

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Role permissions updated successfully'
        })

        // Refresh roles to get updated data
        await this.fetchRoles()
        
        // Update original permissions
        this.originalPermissions = [...this.selectedPermissions]

      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to update role permissions'
        })
      } finally {
        this.saving = false
      }
    },

    editRole(role) {
      this.editingRole = role
      this.roleForm = {
        name: role.name,
        permissions: role.permissions.map(p => p.name)
      }
      this.showCreateRoleModal = true
    },

    closeModal() {
      this.showCreateRoleModal = false
      this.editingRole = null
      this.roleForm = {
        name: '',
        permissions: []
      }
      this.roleErrors = {}
    },

    async saveRole() {
      this.savingRole = true
      this.roleErrors = {}

      try {
        const url = this.editingRole ? `/api/roles/${this.editingRole.id}` : '/api/roles'
        const method = this.editingRole ? 'put' : 'post'

        const response = await this.$http[method](url, this.roleForm)

        this.$notify({
          type: 'success',
          title: 'Success',
          text: `Role ${this.editingRole ? 'updated' : 'created'} successfully`
        })

        this.closeModal()
        await this.fetchRoles()

      } catch (error) {
        if (error.response?.status === 422) {
          this.roleErrors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: error.response?.data?.message || `Failed to ${this.editingRole ? 'update' : 'create'} role`
          })
        }
      } finally {
        this.savingRole = false
      }
    },

    async deleteRole(role) {
      if (!confirm(`Are you sure you want to delete the "${role.name}" role? This action cannot be undone.`)) {
        return
      }

      try {
        await this.$http.delete(`/api/roles/${role.id}`)

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Role deleted successfully'
        })

        // If deleted role was selected, clear selection
        if (this.selectedRole?.id === role.id) {
          this.selectedRole = null
          this.selectedPermissions = []
          this.originalPermissions = []
        }

        await this.fetchRoles()

      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to delete role'
        })
      }
    },

    applyPermissionPreset(permissions) {
      this.roleForm.permissions = [...permissions]
    },

    getRoleBadgeClass(roleName) {
      const classes = {
        admin: 'bg-purple-100 text-purple-800',
        teacher: 'bg-green-100 text-green-800',
        student: 'bg-blue-100 text-blue-800'
      }
      return classes[roleName] || 'bg-gray-100 text-gray-800'
    },

    getUserCount(roleName) {
      return this.userCounts[roleName] || 0
    },

    isProtectedRole(roleName) {
      return ['admin', 'teacher', 'student'].includes(roleName)
    },

    formatPermissionName(permission) {
      return permission
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
    }
  }
}
</script>