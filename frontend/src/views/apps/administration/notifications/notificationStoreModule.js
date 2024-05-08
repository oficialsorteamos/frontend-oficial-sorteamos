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
          .get('/api/administration/notification/fetch-notifications', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchCompanies(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/administration/company/fetch-companies', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    sendNotification(ctx, notificationData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/notification/send-notification', notificationData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeUser(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/management/user/remove-user/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchCompaniesByNotification(ctx, queryParams) {
      console.log(queryParams)
      return new Promise((resolve, reject) => {
        axios
          .get('/api/administration/notification/fetch-companies-by-notification', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
