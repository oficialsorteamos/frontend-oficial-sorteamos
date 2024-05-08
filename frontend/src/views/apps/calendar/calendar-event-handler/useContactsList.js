import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
//import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const contactsSearch = ref([])

  const searchQuery = ref('')

  const refetchData = () => {
    //refUserListTable.value.refresh()
    fetchContacts()
  }

  //Vigia o campo de pesquisa (se foi digitado algo nele)
  watch([searchQuery], () => {
    refetchData()
  })

  const fetchContacts = (ctx, callback) => {
    console.log('dados pesquisa')
    store
      .dispatch('calendar/fetchContacts', {
        q: searchQuery.value,
      })
      .then(response => {
        console.log('dados pesquisa')
        console.log(response.data.contacts)
        //const { users, total } = response.data
        contactsSearch.value = response.data.contacts
      })
      .catch(() => {
        /*
        toast({
          component: ToastificationContent,
          props: {
            title: 'Error fetching contacts list',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })*/
      })
  }

  return {
    fetchContacts,
    searchQuery,
    contactsSearch,
  }
}
