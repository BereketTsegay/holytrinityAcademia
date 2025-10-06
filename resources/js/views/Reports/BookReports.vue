<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Library Reports</h1>
        <p class="text-gray-600 mt-2">Generate and view library statistics and reports</p>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-3">
        <router-link
          to="/library"
          class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center"
        >
          <ArrowLeftIcon class="w-4 h-4 mr-2" />
          Back to Library
        </router-link>
      </div>
    </div>

    <!-- Report Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Report Parameters</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
          <select v-model="filters.report_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <option value="borrowings">Borrowings Overview</option>
            <option value="overdue">Overdue Books</option>
            <option value="popular">Popular Books</option>
            <option value="categories">Category Analysis</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            v-model="filters.start_date"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            v-model="filters.end_date"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
        </div>
        
        <div class="flex items-end">
          <button
            @click="generateReport"
            :disabled="generating"
            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            <span v-if="generating">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            Generate Report
          </button>
        </div>
      </div>
    </div>

    <!-- Report Results -->
    <div v-if="reportData" class="space-y-8">
      <!-- Borrowings Report -->
      <div v-if="filters.report_type === 'borrowings'" class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Borrowings Overview</h3>
          <p class="text-sm text-gray-600">
            {{ formatDate(filters.start_date) }} to {{ formatDate(filters.end_date) }}
          </p>
        </div>
        <div class="p-6">
          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-blue-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-blue-600">{{ reportData.summary.total_borrowings }}</div>
              <div class="text-sm text-blue-700">Total Borrowings</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-green-600">{{ reportData.summary.returned_books }}</div>
              <div class="text-sm text-green-700">Books Returned</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-orange-600">{{ reportData.summary.active_borrowings }}</div>
              <div class="text-sm text-orange-700">Active Borrowings</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-red-600">{{ reportData.summary.overdue_books }}</div>
              <div class="text-sm text-red-700">Overdue Books</div>
            </div>
          </div>

          <!-- Daily Borrowings Chart -->
          <div class="border rounded-lg p-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Daily Borrowings Trend</h4>
            <div class="h-64">
              <!-- Chart would go here - using a simple table for now -->
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Returned</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Overdue</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="day in reportData.borrowings" :key="day.date">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(day.date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ day.total_borrowings }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ day.active_borrowings }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ day.returned_books }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ day.overdue_books }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Overdue Books Report -->
      <div v-if="filters.report_type === 'overdue'" class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Overdue Books Report</h3>
          <p class="text-sm text-gray-600">
            Generated on {{ formatDate(reportData.generated_at) }}
          </p>
        </div>
        <div class="p-6">
          <!-- Summary -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-red-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-red-600">{{ reportData.summary.total_overdue }}</div>
              <div class="text-sm text-red-700">Total Overdue Books</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-orange-600">${{ reportData.summary.total_due_amount || '0.00' }}</div>
              <div class="text-sm text-orange-700">Total Due Amount</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-yellow-600">{{ Math.round(reportData.summary.average_overdue_days) }}</div>
              <div class="text-sm text-yellow-700">Avg. Overdue Days</div>
            </div>
          </div>

          <!-- Overdue Books List -->
          <div class="border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrowed Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Overdue Days</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="borrowing in reportData.overdue_books" :key="borrowing.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ borrowing.book.title }}</div>
                    <div class="text-sm text-gray-500">by {{ borrowing.book.author }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ borrowing.student.first_name }} {{ borrowing.student.last_name }}</div>
                    <div class="text-sm text-gray-500">{{ borrowing.student.student_id }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(borrowing.borrowed_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(borrowing.due_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      {{ Math.floor((new Date() - new Date(borrowing.due_date)) / (1000 * 60 * 60 * 24)) }} days
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Export Button -->
      <div class="flex justify-end">
        <button
          @click="exportReport"
          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center"
        >
          <DocumentDownloadIcon class="w-4 h-4 mr-2" />
          Export Report
        </button>
      </div>
    </div>

    <!-- No Report Generated -->
    <div v-else class="bg-white shadow rounded-lg">
      <div class="text-center py-12">
        <ChartBarIcon class="mx-auto h-16 w-16 text-gray-400" />
        <h3 class="mt-4 text-lg font-medium text-gray-900">No report generated</h3>
        <p class="mt-2 text-sm text-gray-500">
          Select report parameters and click "Generate Report" to view library statistics.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import {
  ArrowLeftIcon,
  ChartBarIcon,
  DocumentDownloadIcon
} from '@heroicons/vue/outline'

export default {
  name: 'BookReports',
  components: {
    ArrowLeftIcon,
    ChartBarIcon,
    DocumentDownloadIcon
  },
  data() {
    const today = new Date().toISOString().split('T')[0]
    const oneMonthAgo = new Date()
    oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1)
    const oneMonthAgoStr = oneMonthAgo.toISOString().split('T')[0]

    return {
      filters: {
        report_type: 'borrowings',
        start_date: oneMonthAgoStr,
        end_date: today
      },
      reportData: null,
      generating: false
    }
  },
  methods: {
    async generateReport() {
      this.generating = true
      try {
        const response = await this.$http.get('/api/books/reports', {
          params: this.filters
        })
        this.reportData = response.data
      } catch (error) {
        console.error('Error generating report:', error)
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to generate report'
        })
      } finally {
        this.generating = false
      }
    },
    exportReport() {
      // Implement export functionality
      this.$notify({
        type: 'info',
        title: 'Export',
        text: 'Export functionality will be implemented soon'
      })
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
  }
}
</script>