import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchInvoices(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/partner/invoice/fetch-invoices', queryParams)
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
  },
}
