import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchCompany() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/settings/company/fetch-company')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchPlan() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/settings/plan/fetch-plan')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchWhiteLabel() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/settings/white-label/fetch-white-label')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchParametersCharge() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/financial/fetch-parameters-charge')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchParametersGeneral() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/financial/fetch-parameters-general')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCompanyGeneral(ctx, { companyData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/settings/company/update-company-general', { companyData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updatePlan(ctx, { planData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/settings/plan/update-plan',  planData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateWhiteLabel(ctx, whiteLabelData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/settings/white-label/update-white-label',  whiteLabelData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCustomization(ctx, customizationData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/settings/company/update-customization',  customizationData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateParametersGeneral(ctx, { generalData }) { 
      return new Promise((resolve, reject) => {
        axios
        .post('/api/financial/update-parameters-general',  generalData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateParametersCharge(ctx, { parametersData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/financial/update-parameters-charge',  parametersData )
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
  },
}
