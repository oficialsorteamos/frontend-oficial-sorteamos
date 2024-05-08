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
    { key: 'ins_name', label: 'Nome', sortable: false },
    { key: 'ins_token', label: 'Token', sortable: false },
    { key: 'ins_webhook', label: "Webhook" },
    { key: 'ins_dt_created', label: "Criada em" },
    { key: 'connection_status', label: "Status Conexão" },
    { key: 'status', label: "Status" },
    { key: 'actions', label: "Ações" },
  ]
  const perPage = ref(10)
  const totalInstances = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]
  const searchQuery = ref('')
  const companyFilter = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const instances = ref([])
  const instanceStatusFilter = ref('')
  const instanceConnectionStatusFilter = ref('')
  const apiFilter = ref('')

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = refUserListTable.value ? refUserListTable.value.localItems.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalInstances.value,
    }
  })

  const refetchData = () => {
    fetchInstances()
  }

  watch([currentPage, perPage, searchQuery, instanceStatusFilter, instanceConnectionStatusFilter, apiFilter], () => {
    refetchData()
  })

  const fetchInstances = () => {
    store
      .dispatch('app-instance/fetchInstances', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        instanceStatus: instanceStatusFilter.value,
        instanceConnectionStatus: instanceConnectionStatusFilter.value,
        api: apiFilter.value,
      })
      .then(response => {
        const { total } = response.data
        instances.value = response.data.instances

        totalInstances.value = total
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

  const resolveInstanceStatusVariant = statusId => {
    //Se for status ATIVO
    if (statusId === 1) return 'primary'
    //Se for status BLOQUEADA
    if (statusId === 2) return 'warning'
    //Se for status REMOVIDA
    if (statusId === 2) return 'danger'
    return 'danger'
  }

  const resolveConnectionStatusVariant = connectionStatusId => {
    //Se for status CONECTADO
    if (connectionStatusId === 1) return 'success'
    //Se for status DESCONECTADO
    if (connectionStatusId === 2) return 'danger'
    return 'primary'
  }


  return {
    fetchInstances,
    instances,
    tableColumns,
    perPage,
    currentPage,
    totalInstances,
    dataMeta,
    perPageOptions,
    searchQuery,
    companyFilter,
    sortBy,
    isSortDirDesc,
    refUserListTable,
    instanceStatusFilter,
    instanceConnectionStatusFilter,
    apiFilter,

    resolveInstanceStatusVariant,
    resolveConnectionStatusVariant,
    refetchData,

  }
}
