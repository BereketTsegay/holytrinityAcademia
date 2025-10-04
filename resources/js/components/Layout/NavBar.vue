<template>
  <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex justify-between h-16">
        <!-- Logo and Brand -->
        <div class="flex items-center">
          <router-link to="/" class="flex-shrink-0 flex items-center">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">SM</span>
            </div>
            <span class="ml-2 text-xl font-bold text-gray-800 hidden sm:block">
              School Manager
            </span>
          </router-link>
        </div>

        <!-- Navigation Links -->
        <div class="hidden md:flex items-center space-x-4">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.to"
            class="px-3 py-2 rounded-md text-sm font-medium transition-colors"
            :class="[
              $route.name === item.name
                ? 'text-blue-600 bg-blue-50'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
            ]"
          >
            {{ item.name }}
          </router-link>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <button class="relative p-2 text-gray-600 hover:text-gray-900">
            <BellIcon class="w-5 h-5" />
            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
          </button>

          <!-- User Dropdown -->
          <div class="relative">
            <button
              @click="userMenuOpen = !userMenuOpen"
              class="flex items-center space-x-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <img
                :src="user.photo_url"
                :alt="user.full_name"
                class="w-8 h-8 rounded-full border-2 border-gray-200"
              >
              <span class="hidden md:block text-gray-700 font-medium">
                {{ user.first_name }}
              </span>
              <ChevronDownIcon class="w-4 h-4 text-gray-500" />
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="userMenuOpen"
              class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
            >
              <div class="px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900">{{ user.full_name }}</p>
                <p class="text-sm text-gray-500 capitalize">{{ user.role_name }}</p>
              </div>

              <router-link
                to="/profile"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click="userMenuOpen = false"
              >
                <UserIcon class="w-4 h-4 inline mr-2" />
                Your Profile
              </router-link>

              <button
                @click="logout"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                <LogoutIcon class="w-4 h-4 inline mr-2" />
                Sign out
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile menu button -->
    <div class="md:hidden">
      <button
        @click="mobileMenuOpen = !mobileMenuOpen"
        class="flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <MenuIcon class="w-6 h-6" />
      </button>
    </div>

    <!-- Mobile menu -->
    <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t border-gray-200">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <router-link
          v-for="item in navigation"
          :key="item.name"
          :to="item.to"
          class="block px-3 py-2 rounded-md text-base font-medium transition-colors"
          :class="[
            $route.name === item.name
              ? 'text-blue-600 bg-blue-50'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
          ]"
          @click="mobileMenuOpen = false"
        >
          {{ item.name }}
        </router-link>
      </div>
    </div>
  </nav>
</template>

<script>
import {
  BellIcon,
  ChevronDownIcon,
  UserIcon,
  LogoutIcon,
  MenuIcon
} from '@vue-hero-icons/outline'

export default {
  name: 'NavBar',
  components: {
    BellIcon,
    ChevronDownIcon,
    UserIcon,
    LogoutIcon,
    MenuIcon
  },
  data() {
    return {
      userMenuOpen: false,
      mobileMenuOpen: false
    }
  },
  computed: {
    user() {
      return this.$store.getters.user
    },
    navigation() {
      const baseRoutes = [
        { name: 'Dashboard', to: '/' }
      ]

      if (this.user.is_admin || this.user.is_teacher) {
        baseRoutes.push(
          { name: 'Classes', to: '/classes' },
          { name: 'Attendance', to: '/attendance' },
          { name: 'Library', to: '/library' }
        )
      }

      if (this.user.is_student) {
        baseRoutes.push(
          { name: 'My Classes', to: '/my-classes' },
          { name: 'Library', to: '/library' }
        )
      }

      baseRoutes.push(
        { name: 'Calendar', to: '/calendar' }
      )

      if (this.user.is_admin || this.user.is_teacher) {
        baseRoutes.push(
          { name: 'Reports', to: '/reports' }
        )
      }

      return baseRoutes
    }
  },
  methods: {
    async logout() {
      await this.$store.dispatch('logout')
      this.$router.push('/login')
    }
  },
  mounted() {
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!this.$el.contains(e.target)) {
        this.userMenuOpen = false
      }
    })
  }
}
</script>
