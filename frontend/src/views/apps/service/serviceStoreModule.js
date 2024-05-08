import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchServicesInProgress(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/service/fetch-services-progress', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchSelfServicesProgress(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/service/fetch-self-services-progress', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchPendingServicesProgress(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/service/fetch-pending-services-progress', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchActiveServicesProgress(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/service/fetch-active-services-progress', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    transferService(ctx, transferData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/service/transfer-service-progress', transferData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getChat(ctx, params) {
      return new Promise((resolve, reject) => {
        axios
          //.get(`/apps/chat/chats/${userId}`)
          .get('/api/chat/active-chat', {params: params})
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchMessagesChat(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/fetch-messages-chat', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    sendMessage(ctx, formData, config) {
      return new Promise((resolve, reject) => {
        axios
        .post(`/api/chat/send-message/`, formData, config)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    resendMessage(ctx, messageData) {
      return new Promise((resolve, reject) => {
        axios
        .post(`/api/chat/resend-message/`, messageData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    closeService(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/close-service/', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateFairDistribution(ctx,  distributionData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chat/update-fair-distribution/', distributionData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addFairDistribution(ctx, distributionData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chat/add-fair-distribution/', distributionData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchFairDistribution(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chat/fecth-fair-distribution', queryParams)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeFairDistribution(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/chat/remove-fair-distribution/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchContacts(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/contact/fetch-contacts-chat', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getUserDepartmentTransfer(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/management/user/get-user-department-transfer/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addService(ctx, serviceData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/service/add-service', serviceData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addContact(ctx, contactData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/contact/add-quick-contact', contactData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
