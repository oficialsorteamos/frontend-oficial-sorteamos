import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchUsers(ctx, queryParams) {
      console.log(queryParams)
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/user/fetch-users-filter', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchUser(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/management/user/fetch-user/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addUser(ctx, userData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/add-user', userData)
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
    fetchServicesContact(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/services-user', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateUserInformation(ctx, { userData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/update-user', { userData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateUserAccessDetail(ctx, { userData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/update-access-detail', { userData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateUserAddress(ctx,  userData ) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/update-user-address',  userData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getAddressUser(ctx, { cep }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/utils/get-address-api/${cep}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    uploadPhoto(ctx, formData, config) {
      return new Promise((resolve, reject) => {
        axios
        .post(`/api/management/user/upload-photo/`, formData, config)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateUserNotification(ctx, { userData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/update-notification', { userData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    sendAlertNotification(ctx, { alertData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/send-alert-notification', { alertData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchUserNotification(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/management/user/fetch-user-notification/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    markNotificationAsRead(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/management/user/mark-notification-as-read/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    logout(ctx, { userData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/logout', { userData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
