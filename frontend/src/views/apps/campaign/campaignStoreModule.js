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
    fetchCampaigns(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/campaign/fetch-campaigns', { params: queryParams })
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
    addCampaign(ctx, campaignData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/add-campaign', campaignData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeCampaign(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/campaign/remove-campaign/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addMessage(ctx, messageData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/add-message', messageData )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateMessage(ctx,  messageData) { 
      return new Promise((resolve, reject) => {
        axios
          //.post(`/apps/todo/tasks/${task.id}`, { task })
          .post('/api/campaign/update-message/', messageData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeMessage(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/campaign/remove-message/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addMailing(ctx, formData, config) {
      console.log(formData)
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/add-mailing', formData, config)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    downloadMailingModel(ctx,  campaignData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/download-mailing-model/', campaignData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeContactMailing(ctx,  mailingData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/remove-contact-mailing/', mailingData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateChannelCampaign(ctx,  channelData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/update-channel-campaign/', channelData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateStatusCampaign(ctx,  campaignData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/update-status-campaign/', campaignData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchChannelsChatbots(ctx,  campaignData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/fetch-channels-chatbots/', campaignData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchMailing(ctx, queryParams) {
      console.log(queryParams)
      return new Promise((resolve, reject) => {
        axios
          .get('/api/campaign/fetch-mailing', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateCampaign(ctx,  campaignData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/update-campaign/', campaignData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateOperatingFrequency(ctx,  frequencyData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/update-operating-frequency/', frequencyData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateOperatingHours(ctx,  hourData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/update-operating-hours/', hourData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateForwarding(ctx,  forwardingData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/update-forwarding/', forwardingData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    downloadMailing(ctx,  mailingData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/download-mailing/', mailingData)
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
    removeCampaignTemplate(ctx, params) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/remove-campaign-template/', { params: params })
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
    addCampaignTemplate(ctx, params) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/add-campaign-template/', { params: params })
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
    addCard(ctx, cardData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/financial/add-card', cardData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addCredit(ctx, creditData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/financial/add-credit', creditData)
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
    removeQuickMessage(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/chat/remove-quick-message/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchChannelsChatbotsCampaign(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/fetch-channels-chatbots-campaign/',  queryParams )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addChannelChatbotCampaign(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/campaign/add-channel-chatbot-campaign/',  queryParams )
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeChannelChatbotCampaign(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/campaign/remove-channel-chatbot-campaign/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
