import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'
import { title } from '@core/utils/filter'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const refUserListTable = ref(null)


  // Table Handlers
  const tableColumns = [
    { key: 'com_name', label: 'Empresa', sortable: false },
  ]
  const perPage = ref(10)
  const totalNotifications = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const companies = ref([])
  const notification = ref(null)

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = refUserListTable.value ? refUserListTable.value.localItems.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalNotifications.value,
    }
  })

  const refetchData = () => {
    fetchCompaniesByNotification()
  }

  watch([currentPage, perPage, searchQuery], () => {
    if(searchQuery.value != '') {
      if(searchQuery.value.length > 2) {
        refetchData()
      }
    }
    else {
      refetchData() 
    }
  })

  const fetchCompaniesByNotification = (notificationId) => {
    if(!notification.value) {
      notification.value = notificationId
    }
    store
      .dispatch('app-notification/fetchCompaniesByNotification', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        notificationId: notification.value,
      })
      .then(response => {
        const { total } = response.data
        companies.value = response.data.companies
        
        totalNotifications.value = total
      })
      .catch((e) => {
        //console.log(e)
        /*toast({
          component: ToastificationContent,
          props: {
            title: 'Error fetching contacts list',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })*/
      })
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  return {
    fetchCompaniesByNotification,
    tableColumns,
    perPage,
    currentPage,
    totalNotifications,
    dataMeta,
    perPageOptions,
    searchQuery,
    sortBy,
    isSortDirDesc,
    refUserListTable,
    companies,

    refetchData,

  }
}
