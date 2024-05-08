import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchCards(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/financial/fetch-cards', { params: queryParams })
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
    updateCard(ctx,  messageData) { 
      return new Promise((resolve, reject) => {
        axios
          //.post(`/apps/todo/tasks/${task.id}`, { task })
          .post('/api/financial/update-card/', messageData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeCard(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/financial/remove-card/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
