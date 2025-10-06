<template>
  <div class="container mx-auto px-4 py-8 pt-16">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Library Management</h1>
        <p class="text-gray-600 mt-2">Manage books, track borrowings, and monitor library inventory</p>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-3">
        <button
          @click="showBookModal = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Add Book
        </button>
        <router-link
          to="/reports/books"
          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center"
        >
          <ChartBarIcon class="w-4 h-4 mr-2" />
          Reports
        </router-link>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100">
            <BookOpenIcon class="w-6 h-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Books</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_books || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100">
            <CheckCircleIcon class="w-6 h-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Available</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.available_books || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-orange-100">
            <UsersIcon class="w-6 h-6 text-orange-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Borrowed</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.borrowed_books || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-red-100">
            <ExclamationIcon class="w-6 h-6 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Overdue</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.overdue_books || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-900">Quick Borrow</h3>
            <p class="text-sm text-gray-500 mt-1">Issue a book to student</p>
          </div>
          <button
            @click="showBorrowModal = true"
            class="bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700"
          >
            <UserAddIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-900">Quick Return</h3>
            <p class="text-sm text-gray-500 mt-1">Return a borrowed book</p>
          </div>
          <button
            @click="showReturnModal = true"
            class="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700"
          >
            <ArrowLeftIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-900">Categories</h3>
            <p class="text-sm text-gray-500 mt-1">Manage book categories</p>
          </div>
          <button
            @click="showCategoriesModal = true"
            class="bg-purple-600 text-white p-2 rounded-lg hover:bg-purple-700"
          >
            <TagIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-64">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search Books</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search by title, author, or ISBN..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
        </div>
        
        <div class="min-w-48">
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select v-model="filters.category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
        </div>

        <div class="min-w-48">
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Status</option>
            <option value="available">Available</option>
            <option value="borrowed">Borrowed</option>
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

    <!-- Books Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Book
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Author
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ISBN
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Category
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Available
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="book in books.data" :key="book.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <BookOpenIcon class="h-5 w-5 text-blue-600" />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ book.title }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ book.publisher }} ({{ book.published_year }})
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ book.author }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ book.isbn }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  {{ book.category }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ book.available }} / {{ book.quantity }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="book.available > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                >
                  {{ book.available > 0 ? 'Available' : 'Out of Stock' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    @click="viewBook(book)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View
                  </button>
                  <button
                    @click="editBook(book)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </button>
                  <button
                    v-if="book.available > 0"
                    @click="borrowBook(book)"
                    class="text-green-600 hover:text-green-900"
                  >
                    Borrow
                  </button>
                  <button
                    @click="deleteBook(book)"
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
        <p class="mt-2 text-gray-600">Loading books...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="books.data.length === 0" class="p-8 text-center">
        <BookOpenIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No books found</h3>
        <p class="mt-1 text-sm text-gray-500">
          Get started by adding your first book to the library.
        </p>
        <div class="mt-6">
          <button
            @click="showBookModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Add Book
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="books.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <Pagination :data="books" @pagination-change-page="onPageChange" />
      </div>
    </div>

    <!-- Active Borrowings Section -->
    <div class="mt-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Active Borrowings</h2>
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Book
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Student
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Borrowed Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Due Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="borrowing in activeBorrowings" :key="borrowing.id">
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
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getBorrowingStatusClass(borrowing)"
                  >
                    {{ getBorrowingStatus(borrowing) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="returnBook(borrowing)"
                    class="text-green-600 hover:text-green-900 mr-3"
                  >
                    Return
                  </button>
                  <button
                    @click="viewBorrowing(borrowing)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Details
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="activeBorrowings.length === 0" class="p-8 text-center">
          <CheckCircleIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No active borrowings</h3>
          <p class="mt-1 text-sm text-gray-500">All books are currently available in the library.</p>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <BookModal
      :show="showBookModal"
      :book="selectedBook"
      @close="closeBookModal"
      @saved="handleBookSaved"
    />

    <BorrowModal
      :show="showBorrowModal"
      :book="selectedBook"
      @close="closeBorrowModal"
      @saved="handleBorrowSaved"
    />

    <ReturnModal
      :show="showReturnModal"
      @close="showReturnModal = false"
      @returned="handleBookReturned"
    />

    <CategoriesModal
      :show="showCategoriesModal"
      :categories="categories"
      @close="showCategoriesModal = false"
      @updated="fetchCategories"
    />

    <BookDetailsModal
      :show="showBookDetails"
      :book="selectedBook"
      @close="closeBookDetails"
      @edit="editBook"
    />

    <BorrowingDetailsModal
      :show="showBorrowingDetails"
      :borrowing="selectedBorrowing"
      @close="closeBorrowingDetails"
      @return="returnBook"
    />
  </div>
</template>

<script>
import {
  PlusIcon,
  BookOpenIcon,
  ChartBarIcon,
  CheckCircleIcon,
  UsersIcon,
  ExclamationIcon,
  UserAddIcon,
  ArrowLeftIcon,
  TagIcon
} from '@vue-hero-icons/outline'

import BookModal from './BookModal.vue'
import BorrowModal from './BorrowModal.vue'
import ReturnModal from './ReturnModal.vue'
import CategoriesModal from './CategoriesModal.vue'
import BookDetailsModal from './BookDetailsModal.vue'
import BorrowingDetailsModal from './BorrowingDetailsModal.vue'
import Pagination from '../../components/Pagination.vue'

export default {
  name: 'LibraryIndex',
  components: {
    PlusIcon,
    BookOpenIcon,
    ChartBarIcon,
    CheckCircleIcon,
    UsersIcon,
    ExclamationIcon,
    UserAddIcon,
    ArrowLeftIcon,
    TagIcon,
    BookModal,
    BorrowModal,
    ReturnModal,
    CategoriesModal,
    BookDetailsModal,
    BorrowingDetailsModal,
    Pagination
  },
  data() {
    return {
      books: { data: [] },
      activeBorrowings: [],
      stats: {},
      categories: [],
      filters: {
        search: '',
        category: '',
        status: ''
      },
      loading: false,
      showBookModal: false,
      showBorrowModal: false,
      showReturnModal: false,
      showCategoriesModal: false,
      showBookDetails: false,
      showBorrowingDetails: false,
      selectedBook: null,
      selectedBorrowing: null
    }
  },
  async mounted() {
    await this.fetchBooks()
    await this.fetchActiveBorrowings()
    await this.fetchStats()
    await this.fetchCategories()
  },
  methods: {
    async fetchBooks() {
      this.loading = true
      try {
        const params = {
          ...this.filters
        }
        
        const response = await this.$http.get('/api/books', { params })
        this.books = response.data
      } catch (error) {
        console.error('Error fetching books:', error)
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to load books'
        })
      } finally {
        this.loading = false
      }
    },
    async fetchActiveBorrowings() {
      try {
        const response = await this.$http.get('/api/books/active-borrowings')
        this.activeBorrowings = response.data
      } catch (error) {
        console.error('Error fetching active borrowings:', error)
      }
    },
    async fetchStats() {
      try {
        const response = await this.$http.get('/api/books/stats')
        this.stats = response.data
      } catch (error) {
        console.error('Error fetching stats:', error)
      }
    },
    async fetchCategories() {
      try {
        const response = await this.$http.get('/api/books/categories')
        this.categories = response.data
      } catch (error) {
        console.error('Error fetching categories:', error)
      }
    },
    applyFilters() {
      this.fetchBooks()
    },
    resetFilters() {
      this.filters = {
        search: '',
        category: '',
        status: ''
      }
      this.fetchBooks()
    },
    viewBook(book) {
      this.selectedBook = book
      this.showBookDetails = true
    },
    editBook(book) {
      this.selectedBook = book
      this.showBookModal = true
    },
    borrowBook(book) {
      this.selectedBook = book
      this.showBorrowModal = true
    },
    async deleteBook(book) {
      if (!confirm(`Are you sure you want to delete "${book.title}"? This action cannot be undone.`)) {
        return
      }

      try {
        await this.$http.delete(`/api/books/${book.id}`)
        
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Book deleted successfully'
        })

        await this.fetchBooks()
        await this.fetchStats()

      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to delete book'
        })
      }
    },
    async returnBook(borrowing) {
      try {
        await this.$http.post(`/api/books/${borrowing.book_id}/return`, {
          student_id: borrowing.student_id
        })
        
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Book returned successfully'
        })

        await this.fetchBooks()
        await this.fetchActiveBorrowings()
        await this.fetchStats()

        if (this.showBorrowingDetails) {
          this.closeBorrowingDetails()
        }

      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to return book'
        })
      }
    },
    viewBorrowing(borrowing) {
      this.selectedBorrowing = borrowing
      this.showBorrowingDetails = true
    },
    closeBookModal() {
      this.showBookModal = false
      this.selectedBook = null
    },
    closeBorrowModal() {
      this.showBorrowModal = false
      this.selectedBook = null
    },
    closeBookDetails() {
      this.showBookDetails = false
      this.selectedBook = null
    },
    closeBorrowingDetails() {
      this.showBorrowingDetails = false
      this.selectedBorrowing = null
    },
    handleBookSaved() {
      this.closeBookModal()
      this.fetchBooks()
      this.fetchStats()
    },
    handleBorrowSaved() {
      this.closeBorrowModal()
      this.fetchBooks()
      this.fetchActiveBorrowings()
      this.fetchStats()
    },
    handleBookReturned() {
      this.showReturnModal = false
      this.fetchBooks()
      this.fetchActiveBorrowings()
      this.fetchStats()
    },
    getBorrowingStatus(borrowing) {
      const dueDate = new Date(borrowing.due_date)
      const today = new Date()
      
      if (borrowing.status === 'returned') {
        return 'Returned'
      } else if (dueDate < today) {
        return 'Overdue'
      } else {
        return 'Borrowed'
      }
    },
    getBorrowingStatusClass(borrowing) {
      const dueDate = new Date(borrowing.due_date)
      const today = new Date()
      
      if (borrowing.status === 'returned') {
        return 'bg-green-100 text-green-800'
      } else if (dueDate < today) {
        return 'bg-red-100 text-red-800'
      } else {
        return 'bg-blue-100 text-blue-800'
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },
    onPageChange(page) {
      this.fetchBooks(page)
    }
  }
}
</script>