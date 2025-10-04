<template>
  <div id="app" class="min-h-screen bg-gray-100">
    <NavBar v-if="isAuthenticated" />
    <main :class="{'pt-16': isAuthenticated}">
      <router-view />
    </main>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import NavBar from './Layout/NavBar.vue';

export default {
  name: 'App',
  components: {
    NavBar
  },
  computed: {
    ...mapGetters(['isAuthenticated'])
  }
}
</script>
