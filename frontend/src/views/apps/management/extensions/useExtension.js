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
    { key: 'name', label: 'Nº Ramal', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'description', label: 'Descrição', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'users', label: 'Usuários', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'created_at', label: 'Criado em', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'actions', label: 'Ações', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
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

  watch([currentPage, perPage, searchQuery], () => {
    refetchData()
  })

  const fetchExtensions = (ctx, callback) => {
    store
      .dispatch('app-extension/fetchExtensions', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
      })
      .then(response => {
        const { extensions, total } = response.data
        console.log(extensions)
        callback(extensions)
        totalUsers.value = total
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
    fetchExtensions,
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

    resolveDepartmentStatusVariant,
    refetchData,

  }
}
