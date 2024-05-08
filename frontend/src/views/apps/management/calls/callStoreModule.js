import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    /*fetchUsers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/apps/user/users', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },*/
    fetchCalls(ctx, queryParams) {
      console.log(queryParams)
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/call/fetch-calls', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchContacts(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/blacklist/fetch-contacts', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addContactBlacklist(ctx, blacklistData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/blacklist/add-contact-blacklist', blacklistData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeContactBlacklist(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/management/blacklist/remove-contact-blacklist/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchContacts(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/blacklist/fetch-contacts', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
