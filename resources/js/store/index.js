import { createStore } from 'vuex';
import axios from 'axios';

export default createStore({
  state: {
    user: null,
    token: localStorage.getItem('token') || null
  },
  getters: {
    isAuthenticated: state => !!state.token,
    user: state => state.user,
    token: state => state.token
  },
  mutations: {
    SET_TOKEN(state, token) {
      state.token = token;
      localStorage.setItem('token', token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
    SET_USER(state, user) {
      state.user = user;
    },
    CLEAR_AUTH(state) {
      state.token = null;
      state.user = null;
      localStorage.removeItem('token');
      delete axios.defaults.headers.common['Authorization'];
    }
  },
  actions: {
    async login({ commit }, credentials) {
      const response = await axios.post('/api/login', credentials);
      const { token, user } = response.data;

      commit('SET_TOKEN', token);
      commit('SET_USER', user);
    },
    async logout({ commit }) {
      try {
        await axios.post('/api/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        commit('CLEAR_AUTH');
      }
    },
    async fetchUser({ commit }) {
      const response = await axios.get('/api/user');
      commit('SET_USER', response.data);
    }
  }
});
