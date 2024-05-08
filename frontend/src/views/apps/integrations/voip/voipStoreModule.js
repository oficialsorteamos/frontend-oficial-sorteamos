import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchVoips(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/integration/voip/fetch-voips', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateAuthenticationVoip(ctx,  authenticationData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/integration/voip/update-authentication-voip/', authenticationData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateStatusVoip(ctx,  voipData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/integration/voip/update-status-voip/', voipData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
