<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Classes</h1>
      <button @click="showCreateModal = true"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        Add Class
      </button>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <input v-model="filters.search"
               type="text"
               placeholder="Search classes..."
               class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-md">
        <select v-model="filters.department" class="px-3 py-2 border border-gray-300 rounded-md">
          <option value="">All Departments</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">
            {{ dept.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Classes Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Class Name
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Department
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Teacher
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Capacity
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="classItem in filteredClasses" :key="classItem.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ classItem.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ classItem.department.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ classItem.teacher.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ classItem.capacity }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button @click="editClass(classItem)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                Edit
              </button>
              <button @click="deleteClass(classItem.id)" class="text-red-600 hover:text-red-900">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <ClassModal
      :show="showCreateModal"
      :class-data="editingClass"
      @close="showCreateModal = false"
      @saved="handleClassSaved" />
  </div>
</template>

<script>
import ClassModal from './ClassModal.vue';

export default {
  name: 'ClassesIndex',
  components: {
    ClassModal
  },
  data() {
    return {
      classes: [],
      departments: [],
      filters: {
        search: '',
        department: ''
      },
      showCreateModal: false,
      editingClass: null
    }
  },
  computed: {
    filteredClasses() {
      return this.classes.filter(classItem => {
        const matchesSearch = classItem.name.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                            classItem.teacher.name.toLowerCase().includes(this.filters.search.toLowerCase());
        const matchesDepartment = !this.filters.department ||
                                 classItem.department_id == this.filters.department;

        return matchesSearch && matchesDepartment;
      });
    }
  },
  async mounted() {
    await this.fetchClasses();
    await this.fetchDepartments();
  },
  methods: {
    async fetchClasses() {
      try {
        const response = await this.$http.get('/api/classes');
        this.classes = response.data;
      } catch (error) {
        console.error('Error fetching classes:', error);
      }
    },
    async fetchDepartments() {
      try {
        const response = await this.$http.get('/api/departments');
        this.departments = response.data;
      } catch (error) {
        console.error('Error fetching departments:', error);
      }
    },
    editClass(classItem) {
      this.editingClass = { ...classItem };
      this.showCreateModal = true;
    },
    async deleteClass(classId) {
      if (confirm('Are you sure you want to delete this class?')) {
        try {
          await this.$http.delete(`/api/classes/${classId}`);
          await this.fetchClasses();
        } catch (error) {
          console.error('Error deleting class:', error);
        }
      }
    },
    handleClassSaved() {
      this.showCreateModal = false;
      this.editingClass = null;
      this.fetchClasses();
    }
  }
}
</script>
