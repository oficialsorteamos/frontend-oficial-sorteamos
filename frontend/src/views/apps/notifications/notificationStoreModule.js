import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchNotifications(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/notification/fetch-notifications', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
