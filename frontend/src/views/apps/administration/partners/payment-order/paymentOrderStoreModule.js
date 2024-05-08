import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchPaymentOrders(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/administration/partner/payment-order/fetch-payment-orders', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updatePaymentOrder(ctx, { paymentOrderData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/partner/payment-order/update-payment-order', paymentOrderData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    uploadPaymentReceipt(ctx, paymentOrderData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/partner/payment-order/upload-payment-receipt', paymentOrderData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
