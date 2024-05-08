import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchExtensions(ctx, queryParams) {
      console.log(queryParams)
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/extension/fetch-extensions', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addExtension(ctx, extensionData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/extension/add-extension', extensionData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateExtension(ctx, extensionData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/extension/update-extension',  extensionData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeExtension(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/management/extension/remove-extension/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
