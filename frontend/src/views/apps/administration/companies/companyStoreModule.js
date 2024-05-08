import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchCompanies(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/administration/company/fetch-companies', { params: queryParams })
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
    addCompany(ctx, companyData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/add-company', { companyData: companyData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCompanyDetails(ctx, companyData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/update-company-details', companyData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCompany(ctx, companyData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/update-company', { companyData: companyData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addContract(ctx, contractData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/add-contract', contractData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateContract(ctx, contractData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/update-contract', contractData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchContracts(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/fetch-contracts', queryParams)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeContract(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/administration/company/remove-contract/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    downloadContract(ctx,  contractData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/download-contract', contractData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCompanyStatus(ctx,  { companyId, statusId }) { 
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/administration/company/update-company-status/${companyId}/${statusId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCompanyPlan(ctx,  companyData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/update-company-plan', companyData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCompanyFees(ctx,  companyData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/update-company-fees', companyData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCompanyCharges(ctx,  companyData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/administration/company/update-company-charges', companyData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchCampaignTemplates(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/fetch-campaign-templates', { params: queryParams })
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
    updateUserNotification(ctx, { userData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/update-notification', { userData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    sendAlertNotification(ctx, { alertData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/user/send-alert-notification', { alertData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchUserNotification(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/management/user/fetch-user-notification/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    markNotificationAsRead(ctx, { userId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/management/user/mark-notification-as-read/${userId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    logout(ctx, { userData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/logout', { userData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
