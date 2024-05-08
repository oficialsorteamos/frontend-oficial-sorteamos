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
    fetchTags(ctx, queryParams) {
      console.log(queryParams)
      return new Promise((resolve, reject) => {
        axios
          .get('/api/management/tag/fetch-tags-filter', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addTag(ctx, tagData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/tag/add-tag', tagData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    updateTag(ctx, { tagData }) { 
      return new Promise((resolve, reject) => {
        axios
          .post('/api/management/tag/update-tag', { tagData })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeTag(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/api/management/tag/remove-tag/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
