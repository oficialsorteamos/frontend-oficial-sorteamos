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
    fetchDepartments(ctx, queryParams) {
      console.log(queryParams)
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/department/fetch-departments-filter', { params: queryParams })
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
    updateDepartment(ctx, { departmentData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/department/update-department', { departmentData })
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
