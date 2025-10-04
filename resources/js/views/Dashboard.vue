<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Dashboard</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in stats" :key="stat.name"
           class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100 text-blue-600">
            <component :is="stat.icon" class="w-6 h-6" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">{{ stat.name }}</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Recent Attendance</h3>
        </div>
        <div class="p-6">
          <!-- Attendance chart/table will go here -->
        </div>
      </div>

      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Upcoming Events</h3>
        </div>
        <div class="p-6">
          <!-- Calendar events will go here -->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { UsersIcon, BookOpenIcon, AcademicCapIcon, CalendarIcon } from '@heroicons/vue/outline';

export default {
  name: 'Dashboard',
  data() {
    return {
      stats: []
    }
  },
  components: {
    UsersIcon,
    BookOpenIcon,
    AcademicCapIcon,
    CalendarIcon
  },
  async mounted() {
    await this.fetchStats();
  },
  methods: {
    async fetchStats() {
      try {
        const response = await this.$http.get('/api/dashboard/stats');
        this.stats = [
          { name: 'Total Students', value: response.data.total_students, icon: 'UsersIcon' },
          { name: 'Total Teachers', value: response.data.total_teachers, icon: 'AcademicCapIcon' },
          { name: 'Total Classes', value: response.data.total_classes, icon: 'BookOpenIcon' },
          { name: "Today's Attendance", value: response.data.today_attendance, icon: 'CalendarIcon' }
        ];
      } catch (error) {
        console.error('Error fetching stats:', error);
      }
    }
  }
}
</script>
