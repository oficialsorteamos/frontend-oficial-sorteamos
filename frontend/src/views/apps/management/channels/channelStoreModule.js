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
    fetchChannels() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/channel/fetch-channels')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addChannel(ctx, channelData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/channel/add-channel', channelData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    startSession(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/communication/communicator/start-session', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    closeSession(ctx, channelData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/communication/communicator/close-session', channelData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateChannel(ctx, { channelData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/channel/update-channel', { channelData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateParametersChannel(ctx, { parametersChannelData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/channel/update-parameters-channel', { parametersChannelData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateStatusChannel(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/channel/update-status-channel', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    checkPhoneExist(ctx, { phoneNumber }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/management/channel/check-phone-exist/${phoneNumber}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addCard(ctx, cardData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/financial/add-card', cardData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addPayment(ctx, paymentData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/channel/add-payment', paymentData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchChannelPayments(ctx, queryParams) {
      console.log(queryParams)
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/channel/fetch-channel-payments', queryParams)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateSubscriptionRenewal(ctx, { subscriptionData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/channel/update-subscription-renewal',  subscriptionData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    hideNotification(ctx, notificationId) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/channel/hide-notification', notificationId)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
