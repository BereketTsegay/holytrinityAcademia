<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Department Management</h1>
        <p class="text-gray-600 mt-2">Manage academic departments and their resources</p>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-3">
        <button
          @click="showCreateModal = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Add Department
        </button>
        <router-link
          to="/reports/departments"
          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center"
        >
          <ChartBarIcon class="w-4 h-4 mr-2" />
          Department Reports
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
            placeholder="Search departments..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
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

    <!-- Departments Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Department Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Head of Department
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Classes
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Subjects
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Teachers
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="department in departments.data" :key="department.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <BuildingOfficeIcon class="h-5 w-5 text-blue-600" />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ department.name }}
                    </div>
                    <div class="text-sm text-gray-500 truncate max-w-xs">
                      {{ department.description || 'No description' }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ department.head_of_department || 'Not assigned' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ department.classes_count }} classes
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  {{ department.subjects_count }} subjects
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                  {{ department.teachers_count }} teachers
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <router-link
                    :to="`/departments/${department.id}`"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View
                  </router-link>
                  <button
                    @click="editDepartment(department)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteDepartment(department)"
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
        <p class="mt-2 text-gray-600">Loading departments...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="departments.data.length === 0" class="p-8 text-center">
        <BuildingOfficeIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No departments found</h3>
        <p class="mt-1 text-sm text-gray-500">
          Get started by creating a new department.
        </p>
        <div class="mt-6">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Add Department
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="departments.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <Pagination :data="departments" @pagination-change-page="onPageChange" />
      </div>
    </div>

    <!-- Create/Edit Department Modal -->
    <DepartmentModal
      :show="showCreateModal"
      :department="editingDepartment"
      @close="closeModal"
      @saved="handleDepartmentSaved"
    />
  </div>
</template>

<script>
import {
  PlusIcon,
  ChartBarIcon,
  BuildingOfficeIcon,
  AcademicCapIcon,
  BookOpenIcon,
  UserGroupIcon
} from '@heroicons/vue/outline'
import DepartmentModal from './DepartmentModal.vue'
import Pagination from '../../components/Pagination.vue'

export default {
  name: 'DepartmentsIndex',
  components: {
    PlusIcon,
    ChartBarIcon,
    BuildingOfficeIcon,
    AcademicCapIcon,
    BookOpenIcon,
    UserGroupIcon,
    DepartmentModal,
    Pagination
  },
  data() {
    return {
      departments: { data: [] },
      stats: [],
      filters: {
        search: ''
      },
      loading: false,
      showCreateModal: false,
      editingDepartment: null
    }
  },
  async mounted() {
    await this.fetchDepartments()
    await this.fetchStats()
  },
  methods: {
    async fetchDepartments() {
      this.loading = true
      try {
        const params = {
          ...this.filters
        }
        
        const response = await this.$http.get('/api/departments', { params })
        this.departments = response.data
      } catch (error) {
        console.error('Error fetching departments:', error)
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to load departments'
        })
      } finally {
        this.loading = false
      }
    },

    async fetchStats() {
      try {
        const response = await this.$http.get('/api/departments/stats')
        this.stats = [
          {
            name: 'Total Departments',
            value: response.data.total_departments,
            icon: BuildingOfficeIcon,
            bgColor: 'bg-blue-100',
            iconColor: 'text-blue-600'
          },
          {
            name: 'Total Classes',
            value: response.data.total_classes,
            icon: AcademicCapIcon,
            bgColor: 'bg-green-100',
            iconColor: 'text-green-600'
          },
          {
            name: 'Total Subjects',
            value: response.data.total_subjects,
            icon: BookOpenIcon,
            bgColor: 'bg-purple-100',
            iconColor: 'text-purple-600'
          },
          {
            name: 'Total Teachers',
            value: response.data.total_teachers,
            icon: UserGroupIcon,
            bgColor: 'bg-orange-100',
            iconColor: 'text-orange-600'
          }
        ]
      } catch (error) {
        console.error('Error fetching stats:', error)
      }
    },

    applyFilters() {
      this.fetchDepartments()
    },

    resetFilters() {
      this.filters = {
        search: ''
      }
      this.fetchDepartments()
    },

    editDepartment(department) {
      this.editingDepartment = department
      this.showCreateModal = true
    },

    async deleteDepartment(department) {
      if (!confirm(`Are you sure you want to delete "${department.name}"? This action cannot be undone.`)) {
        return
      }

      try {
        await this.$http.delete(`/api/departments/${department.id}`)
        
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Department deleted successfully'
        })

        await this.fetchDepartments()
        await this.fetchStats()

      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to delete department'
        })
      }
    },

    closeModal() {
      this.showCreateModal = false
      this.editingDepartment = null
    },

    handleDepartmentSaved() {
      this.closeModal()
      this.fetchDepartments()
      this.fetchStats()
    },

    onPageChange(page) {
      this.fetchDepartments(page)
    }
  }
}
</script>