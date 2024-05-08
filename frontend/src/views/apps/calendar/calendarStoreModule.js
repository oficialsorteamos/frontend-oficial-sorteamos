import axios from '@axios'

export default {
  namespaced: true,
  state: {
    /*calendarOptions: [
      {
        color: 'danger',
        label: '6',
      },
      {
        color: 'primary',
        label: '5',
      },
      /*{
        color: 'warning',
        label: 'Negociação',
      },
      {
        color: 'success',
        label: 'Holiday',
      },
      {
        color: 'info',
        label: 'ETC',
      },
    ],*/
    calendarOptions: [],
    selectedCalendars: [],
  },
  getters: {},
  mutations: {
    SET_TAGS(state, tags) {
      state.calendarOptions = tags
      //Extrai os ids das tags
      state.selectedCalendars = tags.map(tag => tag.id)
    },
    SET_SELECTED_EVENTS(state, val) {
      console.log('evento que quero')
      console.log(val)
      state.selectedCalendars = val
    },
  },
  actions: {
    fetchEvents(ctx, { calendars }) {
      return new Promise((resolve, reject) => {
        axios
          //.get('/apps/calendar/events', {
          .get('/api/calendar/get-events', {
            params: {
              //tags: calendars.join(','),
              tags: calendars,
            },
          })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addEvent(ctx, { event }) {
      return new Promise((resolve, reject) => {
        axios
          //.post('/apps/calendar/events', { event })
          .post('/api/calendar/add-event', { event })
          .then(response => 
            resolve(response)
            //console.log(response)
          )
          .catch(error => reject(error))
      })
    },
    updateEvent(ctx, { event }) {
      return new Promise((resolve, reject) => {
        axios
          //.post(`/apps/calendar/events/${event.id}`, { event })
          .post(`/api/calendar/update-event/${event.id}`, { event })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    removeEvent(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          //.delete(`/apps/calendar/events/${id}`)
          .delete(`/api/calendar/remove-event/${id}`)
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
    fetchTags({ commit }) {
      axios
      .get('/api/management/tag/fetch-tags-type/2')
      .then(response => {
        console.log('trouxe as tags')
        commit('SET_TAGS', response.data.tags)
      })
    }
  },
}
