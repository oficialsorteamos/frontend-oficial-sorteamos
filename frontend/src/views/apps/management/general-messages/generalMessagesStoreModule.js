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
    fetchGeneralMessages(ctx, ) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/general-message/fetch-general-messages')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addDepartment(ctx, departmentData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/department/add-department', departmentData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateGeneralMessage(ctx, { generalMessageData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/general-message/update-general-message', { generalMessageData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeDepartment(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/management/department/remove-department/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
