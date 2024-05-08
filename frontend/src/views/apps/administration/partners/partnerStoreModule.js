import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchPartners(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/administration/partner/fetch-partners', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchUser(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/management/user/fetch-user/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addPartner(ctx, partnerData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/partner/add-partner', partnerData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updatePartner(ctx, partnerData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/partner/update-partner', { partnerData: partnerData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCommission(ctx,  partnerData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/partner/update-commission', partnerData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updatePartnerFees(ctx,  partnerData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/partner/update-partner-fees', partnerData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeUser(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/management/user/remove-user/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchServicesContact(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/services-user', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateUserInformation(ctx, { userData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/update-user', { userData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateUserAccessDetail(ctx, { userData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/update-access-detail', { userData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateUserAddress(ctx,  userData ) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/update-user-address',  userData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getAddressUser(ctx, { cep }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/utils/get-address-api/${cep}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    uploadPhoto(ctx, formData, config) {
      return new Promise((resolve, reject) => {
        axios
        .post(`/api/management/user/upload-photo/`, formData, config)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
