import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    addTemplateMessage(ctx, messageData, config) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chat/add-template-message', messageData, config )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchCredits(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/financial/fetch-credits', { params: queryParams })
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
    addCredit(ctx, creditData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/financial/add-credit', creditData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
