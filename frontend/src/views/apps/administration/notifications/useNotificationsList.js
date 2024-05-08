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
    { key: 'not_title', label: 'Título', sortable: false },
    { key: 'not_message', label: 'Mensagem', sortable: false },
    { key: 'type_users', label: "Enviado para" },
    { key: 'companies', label: "Total Empresas Notif." },
  ]
  const perPage = ref(10)
  const totalNotifications = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]
  const searchQuery = ref('')
  const companyFilter = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const notifications = ref([])

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
    fetchNotifications()
    console.log('chamou aqui')
  }

  watch([currentPage, perPage, searchQuery, companyFilter], () => {
    refetchData()
  })

  const fetchNotifications = () => {
    store
      .dispatch('app-notification/fetchNotifications', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        company: companyFilter.value,
      })
      .then(response => {
        const { total } = response.data
        notifications.value = response.data.notifications

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

  const resolveUserRoleVariant = role => {
    //Se for um GESTOR
    if (role === 1) return 'primary'
    //Se for um OPERADOR
    if (role === 2) return 'info'
    return 'danger'
  }

  const resolveTypeUserVariant = situationId => {
    if (situationId === 1) return 'dark'
    if (situationId === 2) return 'primary'
    return 'primary'
  }

  const resolveDepartmentStatusVariant = status => {
    if (status === 'pending') return 'warning'
    if (status === 'A') return 'success'
    if (status === 'I') return 'secondary'
    return 'primary'
  }

  return {
    fetchNotifications,
    notifications,
    tableColumns,
    perPage,
    currentPage,
    totalNotifications,
    dataMeta,
    perPageOptions,
    searchQuery,
    companyFilter,
    sortBy,
    isSortDirDesc,
    refUserListTable,

    resolveUserRoleVariant,
    resolveTypeUserVariant,
    resolveDepartmentStatusVariant,
    refetchData,

  }
}
