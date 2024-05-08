import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    /*fetchUsers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/apps/user/users', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },*/
    fetchContacts(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/contact/fetch-contacts', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchContact(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/contact/fetch-contact/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchServicesContact(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/services-contact', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    blockContact(ctx, { contactId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/contact/block-contact/${contactId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    unlockContact(ctx, { contactId }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/contact/unlock-contact/${contactId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addContact(ctx, contactData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/contact/add-quick-contact', contactData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateContact(ctx, { contactData }) { 
      return new Promise((resolve, reject) => {
        axios
          //.post(`/apps/todo/tasks/${task.id}`, { task })
          .post(`/api/contact/update-contact/${contactData.id}`, { contactData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    uploadPhoto(ctx, formData, config) {
      return new Promise((resolve, reject) => {
        axios
        .post(`/api/contact/upload-photo/`, formData, config)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getAddressContact(ctx, { cep }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/utils/get-address-api/${cep}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchAddresses(ctx, payload) {
      return new Promise((resolve, reject) => {
        axios
          //.get('/apps/todo/tasks', { params: payload })
          .get('/api/system/address/fetch-address', { params: payload })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addAddress(ctx, addressData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/system/address/add-address',  addressData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateAddress(ctx, { address }) { 
      return new Promise((resolve, reject) => {
        axios
          //.post(`/apps/todo/tasks/${task.id}`, { task })
          .post(`/api/system/address/update-address/${address.id}`, address)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeAddress(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          //.delete(`/apps/todo/tasks/${id}`)
          .delete(`/api/system/address/remove-address/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchSocialNetworks(ctx, payload) {
      return new Promise((resolve, reject) => {
        axios
          //.get('/apps/todo/tasks', { params: payload })
          .get('/api/contact/fetch-social-networks', { params: payload })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addSocialNetwork(ctx, socialData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/contact/add-social-network',  socialData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateSocialNetwork(ctx, { socialNetwork }) { 
      return new Promise((resolve, reject) => {
        axios
          //.post(`/apps/todo/tasks/${task.id}`, { task })
          .post(`/api/contact/update-social-network/${socialNetwork.id}`, socialNetwork)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeSocialNetwork(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          //.delete(`/apps/todo/tasks/${id}`)
          .delete(`/api/contact/remove-social-network/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
