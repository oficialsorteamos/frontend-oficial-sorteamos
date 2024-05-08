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
  const userFilter = ref('')
  const periodFilter = ref('')
  const contactFilter = ref('')
  const extensionFilter = ref('')
  const urlBaseStorage = ref('')


  // Table Handlers
  const tableColumns = [
    { key: 'con_name', label: 'Contato', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'cal_phone_contact', label: 'Telefone Contato', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'name', label: 'Usuário', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'extension_name', label: 'Ramal', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'ser_protocol_number', label: 'Protocolo Atendimento', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'cal_call_date', label: 'Data', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'cal_record_name', label: 'Gravação', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
  ]
  const perPage = ref(10)
  const totalUsers = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = refUserListTable.value ? refUserListTable.value.localItems.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalUsers.value,
    }
  })

  const refetchData = () => {
    refUserListTable.value.refresh()
  }

  watch([currentPage, perPage, searchQuery, userFilter, periodFilter, contactFilter, extensionFilter], () => {
    refetchData()
  })

  const fetchCalls = (ctx, callback) => {
    store
      /*.dispatch('app-user/fetchUsers', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
      })*/
      .dispatch('app-call/fetchCalls', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        user: userFilter.value,
        period: periodFilter.value,
        extension: extensionFilter.value,
        contact: contactFilter.value,
      })
      .then(response => {
        const { calls, total } = response.data
        callback(calls)
        totalUsers.value = total

        urlBaseStorage.value = response.data.urlBaseStorage
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

  const resolveDepartmentStatusVariant = status => {
    if (status === 'pending') return 'warning'
    if (status === 'A') return 'success'
    if (status === 'I') return 'secondary'
    return 'primary'
  }

  return {
    fetchCalls,
    tableColumns,
    perPage,
    currentPage,
    totalUsers,
    dataMeta,
    perPageOptions,
    searchQuery,
    sortBy,
    isSortDirDesc,
    refUserListTable,
    urlBaseStorage,
    userFilter,
    contactFilter,
    periodFilter,
    extensionFilter,

    resolveDepartmentStatusVariant,
    refetchData,

  }
}
