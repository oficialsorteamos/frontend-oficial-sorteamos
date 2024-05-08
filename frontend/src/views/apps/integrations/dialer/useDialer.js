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

  const dialers = ref([])

  // Table Handlers
  const tableColumns = [
    { key: 'user', sortable: true },
    { key: 'email', sortable: true },
    { key: 'role', sortable: true },
    {
      key: 'currentPlan',
      label: 'Plan',
      formatter: title,
      sortable: true,
    },
    { key: 'status', sortable: true },
    { key: 'actions' },
  ]
  const perPage = ref(12)
  const totalVoips = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [12, 24, 48, 96]

  const dataMeta = computed(() => {
    const localItemsCount = refUserListTable.value ? refUserListTable.value.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalVoips.value,
    }
  })

  const refetchData = () => {
    fetchDialers()
  }

  watch([currentPage, perPage], () => {
    refetchData()
  })

  const fetchDialers = (ctx, callback) => {
    store
      .dispatch('app-dialer/fetchDialers', {
        perPage: perPage.value,
        page: currentPage.value,
      })
      .then(response => {
        dialers.value = response.data.dialers
        totalVoips.value = response.data.total
      })
      .catch(() => {
        toast({
          component: ToastificationContent,
          props: {
            title: 'Error fetching contacts list',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })
      })
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  const resolveUserRoleVariant = role => {
    if (role === 'subscriber') return 'primary'
    if (role === 'author') return 'warning'
    if (role === 'maintainer') return 'success'
    if (role === 'editor') return 'info'
    if (role === 'admin') return 'danger'
    return 'primary'
  }

  const resolveUserRoleIcon = role => {
    if (role === 'subscriber') return 'UserIcon'
    if (role === 'author') return 'SettingsIcon'
    if (role === 'maintainer') return 'DatabaseIcon'
    if (role === 'editor') return 'Edit2Icon'
    if (role === 'admin') return 'ServerIcon'
    return 'UserIcon'
  }

  const resolveUserStatusVariant = status => {
    if (status === 'pending') return 'warning'
    if (status === 'active') return 'success'
    if (status === 'inactive') return 'secondary'
    return 'primary'
  }

  return {
    fetchDialers,
    tableColumns,
    perPage,
    currentPage,
    totalVoips,
    dataMeta,
    perPageOptions,
    refUserListTable,
    dialers,

    resolveUserRoleVariant,
    resolveUserRoleIcon,
    resolveUserStatusVariant,
    refetchData,
  }
}
