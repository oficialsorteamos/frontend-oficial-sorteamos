import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchFees(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/financial/fetch-fees', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateFee(ctx, { feeData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/financial/update-fee', feeData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
