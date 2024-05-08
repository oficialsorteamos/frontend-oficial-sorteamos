import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
//import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const companiesSearch = ref([])

  const searchQuerySelect = ref('')

  const refetchData = () => {
    //refUserListTable.value.refresh()
    fetchCompanies()
  }

  //Vigia o campo de pesquisa (se foi digitado algo nele)
  watch([searchQuerySelect], () => {
    refetchData()
  })

  const fetchCompanies = (ctx, callback) => {
    //console.log('dados pesquisa')
    store
      .dispatch('app-notification/fetchCompanies', {
        q: searchQuerySelect.value,
        perPage: 10,
        page: 1,
      })
      .then(response => {
        companiesSearch.value = response.data.companies
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
    fetchCompanies,
    searchQuerySelect,
    companiesSearch,
  }
}
