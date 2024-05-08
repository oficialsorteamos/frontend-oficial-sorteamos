import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchDialers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/integration/dialer/fetch-dialers', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchFowardingSettings(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/integration/dialer/fetch-fowarding-settings', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addFowardingSetting(ctx,  fowardingSettingData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/integration/dialer/add-fowarding-setting/', fowardingSettingData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateFowardingSetting(ctx,  fowardingSettingData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/integration/dialer/update-fowarding-setting/', fowardingSettingData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeFowardingSetting(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/integration/dialer/remove-fowarding-setting/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchTemplates(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/fetch-templates', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
