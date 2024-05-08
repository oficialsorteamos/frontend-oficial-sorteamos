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
    fetchInvoices(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/financial/fetch-invoices', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getPixQrcode(ctx, { chargeIdApi }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/financial/get-pix-qrcode/${chargeIdApi}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateStatusTemplateFacebook(ctx) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/update-status-templates-facebook')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeTemplate(ctx, params) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/remove-template/', { params: params })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    checkTemplateNameExist(ctx, { templateName }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/chat/check-template-name-exist/${templateName}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
