import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchBlocs(ctx, payload) {
      return new Promise((resolve, reject) => {
        axios
          //.get('/apps/todo/tasks', { params: payload })
          .get('/api/chatbot/fetch-blocs', { params: payload })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addBloc(ctx, blocData) {
      return new Promise((resolve, reject) => {
        axios
          //.post('/apps/todo/tasks', { task: taskData })
          .post('/api/chatbot/add-bloc', { blocData: blocData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateBloc(ctx, { bloc }) { 
      return new Promise((resolve, reject) => {
        axios
          //.post(`/apps/todo/tasks/${task.id}`, { task })
          .post(`/api/chatbot/update-bloc/${bloc.id}`, { bloc })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeBloc(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          //.delete(`/apps/todo/tasks/${id}`)
          .delete(`/api/chatbot/remove-bloc/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addAction(ctx, actionData) {
      return new Promise((resolve, reject) => {
        axios
          //.post('/apps/todo/tasks', { task: taskData })
          .post('/api/chatbot/add-action', { action: actionData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateAction(ctx, { action }) { 
      return new Promise((resolve, reject) => {
        axios
          //.post(`/apps/todo/tasks/${task.id}`, { task })
          .post(`/api/chatbot/update-action/${action.id}`, { action })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeAction(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          //.delete(`/apps/todo/tasks/${id}`)
          .delete(`/api/chatbot/remove-action/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchChatbots(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chatbot/fetch-chatbots', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addChatbot(ctx, chatbotData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chatbot/add-chatbot', chatbotData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateChatbot(ctx,  chatbotData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chatbot/update-chatbot/', chatbotData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeChatbot(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/chatbot/remove-chatbot/${id}`)
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
    fetchQuickMessages(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/chat/quick-message', { params: queryParams })
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
    updateStatusChatbot(ctx,  chatbotData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chatbot/update-status-chatbot/', chatbotData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateChannelChatbot(ctx,  chatbotData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/chatbot/update-channel-chatbot/', chatbotData)
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
  },
}
