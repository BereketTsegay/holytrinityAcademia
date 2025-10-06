<template>
  <div class="container mx-auto px-4 py-8 pt-16">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Academic Calendar</h1>
        <p class="text-gray-600 mt-2">Manage and view school events, classes, and schedules</p>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-3">
        <button
          @click="showEventModal = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Add Event
        </button>
        <button
          @click="toggleView"
          class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center"
        >
          <ArrowsExpandIcon class="w-4 h-4 mr-2" />
          {{ currentView === 'month' ? 'Week View' : 'Month View' }}
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100">
            <CalendarIcon class="w-6 h-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Events</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_events }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100">
            <ClockIcon class="w-6 h-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Upcoming</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.upcoming_events }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100">
            <AcademicCapIcon class="w-6 h-6 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">This Month</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.events_this_month }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-orange-100">
            <ChartBarIcon class="w-6 h-6 text-orange-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Classes</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.events_by_type?.class || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar Controls -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <!-- View Toggles -->
        <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
          <button
            @click="setView('month')"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-md transition-colors',
              currentView === 'month' 
                ? 'bg-white text-gray-900 shadow-sm' 
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            Month
          </button>
          <button
            @click="setView('week')"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-md transition-colors',
              currentView === 'week' 
                ? 'bg-white text-gray-900 shadow-sm' 
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            Week
          </button>
          <button
            @click="setView('day')"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-md transition-colors',
              currentView === 'day' 
                ? 'bg-white text-gray-900 shadow-sm' 
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            Day
          </button>
          <button
            @click="setView('list')"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-md transition-colors',
              currentView === 'list' 
                ? 'bg-white text-gray-900 shadow-sm' 
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            List
          </button>
        </div>

        <!-- Date Navigation -->
        <div class="flex items-center space-x-4">
          <button
            @click="previousPeriod"
            class="p-2 rounded-full hover:bg-gray-100"
          >
            <ChevronLeftIcon class="w-5 h-5 text-gray-600" />
          </button>
          
          <h2 class="text-xl font-semibold text-gray-900 min-w-48 text-center">
            {{ currentPeriodText }}
          </h2>
          
          <button
            @click="nextPeriod"
            class="p-2 rounded-full hover:bg-gray-100"
          >
            <ChevronRightIcon class="w-5 h-5 text-gray-600" />
          </button>
          
          <button
            @click="goToToday"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
          >
            Today
          </button>
        </div>

        <!-- Filters -->
        <div class="flex space-x-2">
          <select
            v-model="filters.type"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
          >
            <option value="all">All Events</option>
            <option value="academic">Academic</option>
            <option value="holiday">Holidays</option>
            <option value="exam">Exams</option>
            <option value="event">Events</option>
            <option value="meeting">Meetings</option>
            <option value="class">Classes</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Calendar Views -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <!-- Month View -->
      <div v-if="currentView === 'month'" class="p-6">
        <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
          <!-- Weekday Headers -->
          <div
            v-for="day in weekdays"
            :key="day"
            class="bg-gray-50 p-4 text-center text-sm font-medium text-gray-700"
          >
            {{ day }}
          </div>
          
          <!-- Calendar Days -->
          <div
            v-for="day in calendarDays"
            :key="day.date"
            :class="[
              'bg-white p-4 min-h-32 border border-gray-100 hover:bg-gray-50 transition-colors',
              !day.isCurrentMonth ? 'bg-gray-50 text-gray-400' : '',
              day.isToday ? 'bg-blue-50 border-blue-200' : ''
            ]"
          >
            <div class="flex justify-between items-start mb-2">
              <span
                :class="[
                  'text-sm font-medium',
                  day.isToday ? 'text-blue-600' : 'text-gray-900'
                ]"
              >
                {{ day.day }}
              </span>
              <button
                v-if="day.isCurrentMonth"
                @click="openQuickAdd(day.date)"
                class="text-gray-400 hover:text-blue-600 text-xs"
              >
                <PlusIcon class="w-3 h-3" />
              </button>
            </div>
            
            <!-- Events for the day -->
            <div class="space-y-1">
              <div
                v-for="event in day.events"
                :key="event.id"
                @click="viewEvent(event)"
                class="text-xs p-1 rounded cursor-pointer truncate"
                :style="{ backgroundColor: event.color + '20', borderLeft: `3px solid ${event.color}` }"
              >
                <div class="font-medium" :style="{ color: event.color }">
                  {{ event.title }}
                </div>
                <div class="text-gray-600 text-xs">
                  {{ formatTime(event.start_date) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Week View -->
      <div v-else-if="currentView === 'week'" class="p-6">
        <div class="grid grid-cols-8 gap-px bg-gray-200 rounded-lg overflow-hidden">
          <!-- Time column -->
          <div class="bg-gray-50"></div>
          
          <!-- Day headers -->
          <div
            v-for="day in weekDays"
            :key="day.date"
            class="bg-gray-50 p-4 text-center"
          >
            <div class="text-sm font-medium text-gray-700">
              {{ formatDate(day.date, 'EEE') }}
            </div>
            <div
              :class="[
                'text-lg font-semibold',
                day.isToday ? 'text-blue-600' : 'text-gray-900'
              ]"
            >
              {{ formatDate(day.date, 'd') }}
            </div>
          </div>
          
          <!-- Time slots -->
          <div
            v-for="time in timeSlots"
            :key="time"
            class="contents"
          >
            <div class="bg-white p-2 text-xs text-gray-500 text-right pr-4 border-t border-gray-100">
              {{ time }}
            </div>
            <div
              v-for="day in weekDays"
              :key="day.date + time"
              class="bg-white border-t border-gray-100 min-h-16 relative"
              @click="openQuickAdd(day.date + ' ' + time)"
            >
              <!-- Events for this time slot -->
              <div
                v-for="event in getEventsForTimeSlot(day.date, time)"
                :key="event.id"
                @click.stop="viewEvent(event)"
                class="absolute left-1 right-1 p-1 text-xs rounded cursor-pointer border-l-2"
                :style="{
                  backgroundColor: event.color + '20',
                  borderLeftColor: event.color,
                  top: '2px',
                  bottom: '2px'
                }"
              >
                <div class="font-medium truncate" :style="{ color: event.color }">
                  {{ event.title }}
                </div>
                <div class="text-gray-600 truncate">
                  {{ event.location }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else-if="currentView === 'list'" class="p-6">
        <div class="space-y-4">
          <div
            v-for="event in filteredEvents"
            :key="event.id"
            @click="viewEvent(event)"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <div
              class="w-3 h-12 rounded-full mr-4"
              :style="{ backgroundColor: event.color }"
            ></div>
            <div class="flex-1">
              <h3 class="text-lg font-medium text-gray-900">{{ event.title }}</h3>
              <p class="text-gray-600 mt-1">{{ event.description }}</p>
              <div class="flex items-center mt-2 text-sm text-gray-500">
                <CalendarIcon class="w-4 h-4 mr-1" />
                {{ formatDateTime(event.start_date) }}
                <span v-if="event.end_date"> - {{ formatDateTime(event.end_date) }}</span>
              </div>
              <div v-if="event.location" class="flex items-center mt-1 text-sm text-gray-500">
                <LocationMarkerIcon class="w-4 h-4 mr-1" />
                {{ event.location }}
              </div>
              <div v-if="event.class" class="flex items-center mt-1 text-sm text-gray-500">
                <AcademicCapIcon class="w-4 h-4 mr-1" />
                {{ event.class.name }}
              </div>
            </div>
            <div class="text-right">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                :style="{
                  backgroundColor: event.color + '20',
                  color: event.color
                }"
              >
                {{ event.type }}
              </span>
            </div>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-if="filteredEvents.length === 0" class="text-center py-12">
          <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-4 text-lg font-medium text-gray-900">No events found</h3>
          <p class="mt-2 text-sm text-gray-500">
            No events match your current filters.
          </p>
          <button
            @click="showEventModal = true"
            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Add Your First Event
          </button>
        </div>
      </div>
    </div>

    <!-- Event Modal -->
    <EventModal
      :show="showEventModal"
      :event="selectedEvent"
      :selected-date="quickAddDate"
      @close="closeEventModal"
      @saved="handleEventSaved"
    />

    <!-- Event Details Modal -->
    <EventDetailsModal
      :show="showEventDetails"
      :event="selectedEvent"
      @close="closeEventDetails"
      @edit="editEvent"
      @delete="deleteEvent"
    />
  </div>
</template>

<script>
import {
  PlusIcon,
  CalendarIcon,
  ClockIcon,
  AcademicCapIcon,
  ChartBarIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ArrowsExpandIcon,
  LocationMarkerIcon
} from '@heroicons/vue/outline'
import EventModal from './EventModal.vue'
import EventDetailsModal from './EventDetailsModal.vue'

export default {
  name: 'CalendarIndex',
  components: {
    PlusIcon,
    CalendarIcon,
    ClockIcon,
    AcademicCapIcon,
    ChartBarIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ArrowsExpandIcon,
    LocationMarkerIcon,
    EventModal,
    EventDetailsModal
  },
  data() {
    return {
      currentView: 'month',
      currentDate: new Date(),
      events: [],
      stats: {},
      filters: {
        type: 'all'
      },
      showEventModal: false,
      showEventDetails: false,
      selectedEvent: null,
      quickAddDate: null,
      weekdays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
      timeSlots: [
        '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM',
        '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM'
      ]
    }
  },
  computed: {
    calendarDays() {
      const year = this.currentDate.getFullYear()
      const month = this.currentDate.getMonth()
      
      const firstDay = new Date(year, month, 1)
      const lastDay = new Date(year, month + 1, 0)
      const startDate = new Date(firstDay)
      startDate.setDate(startDate.getDate() - startDate.getDay())
      
      const endDate = new Date(lastDay)
      endDate.setDate(endDate.getDate() + (6 - endDate.getDay()))
      
      const days = []
      const current = new Date(startDate)
      
      while (current <= endDate) {
        const date = new Date(current)
        const dayEvents = this.events.filter(event => {
          const eventDate = new Date(event.start_date)
          return eventDate.toDateString() === date.toDateString()
        })
        
        days.push({
          date: date.toISOString().split('T')[0],
          day: date.getDate(),
          isCurrentMonth: date.getMonth() === month,
          isToday: date.toDateString() === new Date().toDateString(),
          events: dayEvents
        })
        
        current.setDate(current.getDate() + 1)
      }
      
      return days
    },
    weekDays() {
      const startOfWeek = new Date(this.currentDate)
      startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay())
      
      const days = []
      for (let i = 0; i < 7; i++) {
        const date = new Date(startOfWeek)
        date.setDate(date.getDate() + i)
        
        days.push({
          date: date.toISOString().split('T')[0],
          isToday: date.toDateString() === new Date().toDateString()
        })
      }
      
      return days
    },
    currentPeriodText() {
      if (this.currentView === 'month') {
        return this.currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
      } else if (this.currentView === 'week') {
        const start = new Date(this.weekDays[0].date)
        const end = new Date(this.weekDays[6].date)
        return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`
      } else {
        return this.currentDate.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
      }
    },
    filteredEvents() {
      let filtered = this.events
      
      if (this.filters.type !== 'all') {
        filtered = filtered.filter(event => event.type === this.filters.type)
      }
      
      // Sort by start date
      return filtered.sort((a, b) => new Date(a.start_date) - new Date(b.start_date))
    }
  },
  async mounted() {
    await this.fetchEvents()
    await this.fetchStats()
  },
  watch: {
    currentDate: 'fetchEvents',
    filters: {
      handler: 'fetchEvents',
      deep: true
    }
  },
  methods: {
    async fetchEvents() {
      try {
        const start = new Date(this.currentDate)
        const end = new Date(this.currentDate)
        
        if (this.currentView === 'month') {
          start.setDate(1)
          start.setMonth(start.getMonth() - 1)
          end.setMonth(end.getMonth() + 2)
          end.setDate(0)
        } else if (this.currentView === 'week') {
          start.setDate(start.getDate() - start.getDay())
          end.setDate(start.getDate() + 6)
        } else {
          end.setDate(start.getDate() + 1)
        }
        
        const params = {
          start: start.toISOString().split('T')[0],
          end: end.toISOString().split('T')[0],
          ...this.filters
        }
        
        const response = await this.$http.get('/api/calendar', { params })
        this.events = response.data
      } catch (error) {
        console.error('Error fetching events:', error)
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to load calendar events'
        })
      }
    },
    async fetchStats() {
      try {
        const response = await this.$http.get('/api/calendar/statistics')
        this.stats = response.data
      } catch (error) {
        console.error('Error fetching stats:', error)
      }
    },
    setView(view) {
      this.currentView = view
      this.fetchEvents()
    },
    toggleView() {
      this.currentView = this.currentView === 'month' ? 'week' : 'month'
      this.fetchEvents()
    },
    previousPeriod() {
      if (this.currentView === 'month') {
        this.currentDate.setMonth(this.currentDate.getMonth() - 1)
      } else if (this.currentView === 'week') {
        this.currentDate.setDate(this.currentDate.getDate() - 7)
      } else {
        this.currentDate.setDate(this.currentDate.getDate() - 1)
      }
      this.fetchEvents()
    },
    nextPeriod() {
      if (this.currentView === 'month') {
        this.currentDate.setMonth(this.currentDate.getMonth() + 1)
      } else if (this.currentView === 'week') {
        this.currentDate.setDate(this.currentDate.getDate() + 7)
      } else {
        this.currentDate.setDate(this.currentDate.getDate() + 1)
      }
      this.fetchEvents()
    },
    goToToday() {
      this.currentDate = new Date()
      this.fetchEvents()
    },
    openQuickAdd(date) {
      this.quickAddDate = date
      this.selectedEvent = null
      this.showEventModal = true
    },
    viewEvent(event) {
      this.selectedEvent = event
      this.showEventDetails = true
    },
    editEvent(event) {
      this.selectedEvent = event
      this.showEventDetails = false
      this.showEventModal = true
    },
    async deleteEvent(event) {
      if (!confirm(`Are you sure you want to delete "${event.title}"?`)) {
        return
      }
      
      try {
        await this.$http.delete(`/api/calendar/${event.id}`)
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Event deleted successfully'
        })
        this.closeEventDetails()
        await this.fetchEvents()
        await this.fetchStats()
      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to delete event'
        })
      }
    },
    closeEventModal() {
      this.showEventModal = false
      this.selectedEvent = null
      this.quickAddDate = null
    },
    closeEventDetails() {
      this.showEventDetails = false
      this.selectedEvent = null
    },
    async handleEventSaved() {
      this.closeEventModal()
      await this.fetchEvents()
      await this.fetchStats()
    },
    getEventsForTimeSlot(date, time) {
      return this.events.filter(event => {
        const eventDate = new Date(event.start_date)
        const eventTime = eventDate.toLocaleTimeString('en-US', { 
          hour: 'numeric', 
          minute: '2-digit',
          hour12: true 
        })
        return event.date === date && eventTime === time
      })
    },
    formatDate(dateString, format = 'MMM d, yyyy') {
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        weekday: format.includes('EEE') ? 'short' : undefined
      })
    },
    formatTime(dateString) {
      return new Date(dateString).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      })
    },
    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      })
    }
  }
}
</script>