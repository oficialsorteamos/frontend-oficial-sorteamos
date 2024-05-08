import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchInstances(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/administration/instance/fetch-instances', { params: queryParams })
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
    updateInstances(ctx, ) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/administration/instance/update-instances', )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    disconnectInstance(ctx, instanceData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/instance/disconnect-instance', instanceData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeInstance(ctx, instanceData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/instance/remove-instance', instanceData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
