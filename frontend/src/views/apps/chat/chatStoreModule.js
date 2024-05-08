import axios from '@axios'

export default {
  namespaced: true,
  state: {
    userId: 1
  },
  getters: {},
  mutations: {},
  actions: {
    fetchChatsAndContacts() {
      return new Promise((resolve, reject) => {
        axios
          //.get('/apps/chat/chats-and-contacts')
          .get('/api/chat/chats-and-contacts')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getProfileUser() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/user/profile-user')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getChat(ctx, params) {
      return new Promise((resolve, reject) => {
        axios
          //.get(`/apps/chat/chats/${userId}`)
          .get('/api/chat/active-chat', {params: params})
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    sendMessage(ctx, formData, config) {
      return new Promise((resolve, reject) => {
        axios
        .post(`/api/chat/send-message/`, formData, config)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    resendMessage(ctx, messageData) {
      return new Promise((resolve, reject) => {
        axios
        .post(`/api/chat/resend-message/`, messageData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    startService(ctx, { chatId, channelId }) {
      return new Promise((resolve, reject) => {
        axios
          //.post('/apps/todo/tasks', { task: taskData })
          .get(`/api/chat/start-service/${chatId}/${channelId}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    closeService(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/close-service/', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    getUserLogged() {
      return new Promise((resolve, reject) => {
        axios
          //.post('/apps/todo/tasks', { task: taskData })
          .get('/api/user/')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    transferService(ctx, transferData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chat/transfer-service', transferData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchQuickMessages(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/quick-message', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addQuickMessage(ctx, messageData, config) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chat/add-quick-message', messageData, config )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateQuickMessage(ctx, { quickMessage }) { 
      return new Promise((resolve, reject) => {
        axios
          //.post(`/apps/todo/tasks/${task.id}`, { task })
          .post(`/api/chat/update-quick-message/${quickMessage.id}`, { quickMessage })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeQuickMessage(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/chat/remove-quick-message/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    readAllUnseenMessage(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/chat/read-all-unseen-message/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    situationServiceOperator(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/api/chat/situation-service-operator/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateTag(ctx,  tagData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/contact/update-tag/',  tagData)
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
    updateUserSituation(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/user/update-user-situation', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchMessagesChat(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/fetch-messages-chat', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchContacts(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/contact/fetch-contacts-chat', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addContactBlacklistCampaign(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/contact/add-contact-blacklist-campaign', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addTemplateMessage(ctx, messageData, config) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chat/add-template-message', messageData, config )
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
    fetchActiveChats(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/fetch-active-chats', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchPendingChats(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/fetch-pending-chats', { params: queryParams })
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
    updateContactGeneral(ctx, { contactData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/contact/update-contact-general', { contactData })
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
    addContactAddress(ctx, addressData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/system/address/add-address',  addressData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateContactAddress(ctx, { address }) { 
      return new Promise((resolve, reject) => {
        axios
          .post(`/api/system/address/update-address/${address.id}`, address)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchChatObservations(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/fetch-chat-observations', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addChatObservation(ctx, observationData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chat/add-chat-observation', observationData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeChatObservation(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/chat/remove-chat-observation/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    callPhone(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/call-phone/', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
