<template>
  <nav class="bg-white shadow-lg fixed w-full top-0 z-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo and Brand -->
        <div class="flex items-center">
          <router-link to="/" class="flex-shrink-0 flex items-center">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
              <AcademicCapIcon class="w-5 h-5 text-white" />
            </div>
            <span class="ml-2 text-xl font-bold text-gray-800 hidden sm:block">
              SchoolManager
            </span>
          </router-link>
        </div>

        <!-- Desktop Navigation Links -->
        <div class="hidden md:flex items-center space-x-1">
          <router-link 
            v-for="item in navigation" 
            :key="item.name"
            :to="item.to"
            class="px-3 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center"
            :class="[
              $route.name === item.name 
                ? 'text-blue-600 bg-blue-50 border-b-2 border-blue-600' 
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
            ]"
          >
            <component :is="item.icon" class="w-4 h-4 mr-2" />
            {{ item.name }}
          </router-link>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <div class="relative">
            <button 
              @click="toggleNotifications"
              class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors"
            >
              <BellIcon class="w-5 h-5" />
              <span v-if="unreadNotifications > 0" class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
            </button>

            <!-- Notifications Dropdown -->
            <div 
              v-if="notificationsOpen"
              class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200 max-h-96 overflow-y-auto"
            >
              <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-sm font-medium text-gray-900">Notifications</h3>
                <span class="text-xs text-gray-500">{{ unreadNotifications }} unread</span>
              </div>
              
              <div v-if="notifications.length > 0">
                <div 
                  v-for="notification in notifications" 
                  :key="notification.id"
                  class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
                >
                  <div class="flex items-start">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                           :class="getNotificationColor(notification.type)">
                        <component :is="getNotificationIcon(notification.type)" class="w-4 h-4 text-white" />
                      </div>
                    </div>
                    <div class="ml-3 flex-1">
                      <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                      <p class="text-sm text-gray-500 mt-1">{{ notification.message }}</p>
                      <p class="text-xs text-gray-400 mt-1">{{ formatTime(notification.time) }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="px-4 py-8 text-center">
                <BellIcon class="mx-auto h-8 w-8 text-gray-400" />
                <p class="mt-2 text-sm text-gray-500">No notifications</p>
              </div>
              
              <div class="border-t border-gray-100 px-4 py-2">
                <button class="w-full text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                  View All Notifications
                </button>
              </div>
            </div>
          </div>

          <!-- Quick Calendar Access -->
          <router-link 
            to="/calendar"
            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors"
            :class="{ 'text-blue-600 bg-blue-50': $route.name === 'Calendar' }"
          >
            <CalendarIcon class="w-5 h-5" />
          </router-link>

          <!-- User Dropdown -->
          <div class="relative">
            <button 
              @click="userMenuOpen = !userMenuOpen"
              class="flex items-center space-x-3 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 p-1 hover:bg-gray-100 rounded-full"
            >
              <img 
                :src="user.photo_url" 
                :alt="user.full_name"
                class="w-8 h-8 rounded-full border-2 border-gray-200"
              >
              <div class="hidden md:block text-left">
                <p class="text-sm font-medium text-gray-700">{{ user.first_name }}</p>
                <p class="text-xs text-gray-500 capitalize">{{ user.role_name }}</p>
              </div>
              <ChevronDownIcon class="w-4 h-4 text-gray-500" />
            </button>

            <!-- Dropdown Menu -->
            <div 
              v-if="userMenuOpen"
              class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
            >
              <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900">{{ user.full_name }}</p>
                <p class="text-sm text-gray-500">{{ user.email }}</p>
                <p class="text-xs text-gray-400 capitalize mt-1">{{ user.role_name }}</p>
              </div>
              
              <div class="py-1">
                <router-link 
                  to="/profile"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="userMenuOpen = false"
                >
                  <UserIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Your Profile
                </router-link>
                
                <router-link 
                  to="/settings"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="userMenuOpen = false"
                >
                  <CogIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Settings
                </router-link>

                <div class="border-t border-gray-100 my-1"></div>

                <button 
                  @click="logout"
                  class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <LogoutIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Sign out
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden flex items-center">
          <button 
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <MenuIcon class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t border-gray-200 shadow-lg">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <router-link 
          v-for="item in navigation" 
          :key="item.name"
          :to="item.to"
          class="flex items-center px-3 py-2 rounded-md text-base font-medium transition-colors"
          :class="[
            $route.name === item.name 
              ? 'text-blue-600 bg-blue-50 border-r-2 border-blue-600' 
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
          ]"
          @click="mobileMenuOpen = false"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          {{ item.name }}
        </router-link>
      </div>
      
      <!-- Mobile user info -->
      <div class="border-t border-gray-200 px-4 py-3">
        <div class="flex items-center">
          <img 
            :src="user.photo_url" 
            :alt="user.full_name"
            class="w-10 h-10 rounded-full border-2 border-gray-200"
          >
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-900">{{ user.full_name }}</p>
            <p class="text-sm text-gray-500 capitalize">{{ user.role_name }}</p>
          </div>
        </div>
        <div class="mt-3 space-y-1">
          <router-link 
            to="/profile"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50"
            @click="mobileMenuOpen = false"
          >
            Your Profile
          </router-link>
          <button 
            @click="logout"
            class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50"
          >
            Sign out
          </button>
        </div>
      </div>
    </div>
  </nav>

  <!-- Overlay for dropdowns -->
  <div 
    v-if="userMenuOpen || notificationsOpen || mobileMenuOpen"
    class="fixed inset-0 z-40"
    @click="closeAllDropdowns"
  ></div>
</template>

<script>
import { 
  AcademicCapIcon,
  HomeIcon,
  UserGroupIcon,
  BookOpenIcon,
  CalendarIcon,
  ChartBarIcon,
  CogIcon,
  BellIcon,
  UserIcon,
  LogoutIcon,
  ChevronDownIcon,
  MenuIcon,
  ExclamationIcon,
  CheckCircleIcon,
  InformationCircleIcon
} from '@heroicons/vue/outline'

export default {
  name: 'NavBar',
  components: {
    AcademicCapIcon,
    HomeIcon,
    UserGroupIcon,
    BookOpenIcon,
    CalendarIcon,
    ChartBarIcon,
    CogIcon,
    BellIcon,
    UserIcon,
    LogoutIcon,
    ChevronDownIcon,
    MenuIcon,
    ExclamationIcon,
    CheckCircleIcon,
    InformationCircleIcon
  },
  data() {
    return {
      userMenuOpen: false,
      notificationsOpen: false,
      mobileMenuOpen: false,
      notifications: [
        {
          id: 1,
          type: 'info',
          title: 'New Assignment Posted',
          message: 'Mathematics assignment due tomorrow',
          time: new Date(Date.now() - 30 * 60 * 1000), // 30 minutes ago
          read: false
        },
        {
          id: 2,
          type: 'warning',
          title: 'Library Book Due',
          message: 'Your book "Introduction to Physics" is due in 2 days',
          time: new Date(Date.now() - 2 * 60 * 60 * 1000), // 2 hours ago
          read: false
        },
        {
          id: 3,
          type: 'success',
          title: 'Attendance Updated',
          message: 'Your attendance for this week has been updated',
          time: new Date(Date.now() - 5 * 60 * 60 * 1000), // 5 hours ago
          read: true
        }
      ]
    }
  },
  computed: {
    user() {
      return this.$store.getters.user || {}
    },
    navigation() {
      const baseRoutes = [
        { name: 'Dashboard', to: '/', icon: HomeIcon }
      ]

      if (this.user.is_admin || this.user.is_teacher || this.user.is_staff) {
        baseRoutes.push(
          { name: 'Users', to: '/users', icon: UserGroupIcon },
          { name: 'Classes', to: '/classes', icon: AcademicCapIcon },
          { name: 'Library', to: '/library', icon: BookOpenIcon }
        )
      }

      if (this.user.is_student) {
        baseRoutes.push(
          { name: 'My Classes', to: '/my-classes', icon: AcademicCapIcon },
          { name: 'Library', to: '/library', icon: BookOpenIcon }
        )
      }

      baseRoutes.push(
        { name: 'Calendar', to: '/calendar', icon: CalendarIcon }
      )

      if (this.user.is_admin || this.user.is_teacher || this.user.is_staff) {
        baseRoutes.push(
          { name: 'Reports', to: '/reports', icon: ChartBarIcon }
        )
      }

      if (this.user.is_admin) {
        baseRoutes.push(
          { name: 'Departments', to: '/departments', icon: CogIcon }
        )
      }

      return baseRoutes
    },
    unreadNotifications() {
      return this.notifications.filter(n => !n.read).length
    }
  },
  methods: {
    async logout() {
      await this.$store.dispatch('logout')
      this.closeAllDropdowns()
      this.$router.push('/login')
    },
    toggleNotifications() {
      this.notificationsOpen = !this.notificationsOpen
      this.userMenuOpen = false
    },
    closeAllDropdowns() {
      this.userMenuOpen = false
      this.notificationsOpen = false
      this.mobileMenuOpen = false
    },
    getNotificationColor(type) {
      const colors = {
        info: 'bg-blue-500',
        warning: 'bg-yellow-500',
        success: 'bg-green-500',
        error: 'bg-red-500'
      }
      return colors[type] || 'bg-gray-500'
    },
    getNotificationIcon(type) {
      const icons = {
        info: InformationCircleIcon,
        warning: ExclamationIcon,
        success: CheckCircleIcon,
        error: ExclamationIcon
      }
      return icons[type] || InformationCircleIcon
    },
    formatTime(date) {
      const now = new Date()
      const diff = now - date
      
      if (diff < 60 * 1000) return 'Just now'
      if (diff < 60 * 60 * 1000) return `${Math.floor(diff / (60 * 1000))}m ago`
      if (diff < 24 * 60 * 60 * 1000) return `${Math.floor(diff / (60 * 60 * 1000))}h ago`
      return `${Math.floor(diff / (24 * 60 * 60 * 1000))}d ago`
    }
  },
  mounted() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
      if (!this.$el.contains(e.target)) {
        this.closeAllDropdowns()
      }
    })
  }
}
</script>