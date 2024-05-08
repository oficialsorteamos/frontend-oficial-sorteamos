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
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'roles', label: 'Perfil', sortable: true },
    { key: 'departments', label: 'Departamento', sortable: true },
    //{ key: 'status', label: 'Status', sortable: true },
    { key: 'situation_user_id', label: 'Situação', sortable: true },
    { key: 'actions', label: "Ações" },
  ]
  const perPage = ref(10)
  const totalUsers = ref(0)
  const totalCurrentUserQuota = ref(0)
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

  const fetchUsers = (ctx, callback) => {
    store
      .dispatch('app-user/fetchUsers', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        /*sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
        role: roleFilter.value,
        plan: planFilter.value,
        status: statusFilter.value,*/
      })
      .then(response => {
        const { departments, total } = response.data
        console.log(response.data)
        callback(departments)
        totalUsers.value = total
        totalCurrentUserQuota.value = response.data.totalCurrentUserQuota
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

  const resolveDepartmentSituationVariant = situation => {
    if (situation === 1) return 'success'
    if (situation === 2) return 'danger'
    return 'primary'
  }

  const resolveDepartmentStatusVariant = status => {
    if (status === 'pending') return 'warning'
    if (status === 'A') return 'success'
    if (status === 'I') return 'secondary'
    return 'primary'
  }

  return {
    fetchUsers,
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
    totalCurrentUserQuota,

    resolveUserRoleVariant,
    resolveDepartmentSituationVariant,
    resolveDepartmentStatusVariant,
    refetchData,

  }
}
