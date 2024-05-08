import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchCosts(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/financial/fetch-costs', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
