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
    fetchRoles(ctx, queryParams) {
      console.log(queryParams)
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/permission-control/role/fetch-roles', { params: queryParams })
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
    getResources(ctx, roleId) {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/permission-control/resource/get-resources/'+roleId)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updatePermissionRole(ctx,  permissionRoleData) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/permission-control/permission/update-permission-role',  permissionRoleData )
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
