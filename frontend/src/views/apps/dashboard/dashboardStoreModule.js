import axios from '@axios'

export default {
  namespaced: true,
  state: {
    userId: 1
  },
  getters: {},
  mutations: {},
  actions: {
    fetchStatistics() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/dashboard/fetch-statistics')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchContactsByGender() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/dashboard/fetch-contacts-gender')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchContactsByAgeGroup() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/dashboard/fetch-contacts-age-group')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchBestOperators() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/dashboard/fetch-best-operators')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchServicesByLastMonths() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/dashboard/fetch-services-last-months')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchContactsPerState() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/dashboard/fetch-contacts-per-state')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchServicesByStatus() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/dashboard/fetch-services-by-status')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getProfileUser() {
      return new Promise((resolve, reject) => {
        axios
          //.get('/apps/chat/users/profile-user')
          .get('/api/chat/profile-user')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
