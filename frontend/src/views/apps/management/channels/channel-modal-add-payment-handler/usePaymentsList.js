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
    { key: 'payment_method', label: 'Método de Pagamento', sortable: true },
    { key: 'cha_value', label: 'Valor', sortable: true },
    { key: 'created_at', label: 'Data', sortable: true },
  ]
  const perPage = ref(10)
  const totalUsers = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)

  const channelPayments = ref([])

  const channel = ref(0)

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = channelPayments.value ? channelPayments.value.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalUsers.value,
    }
  })

  watch([perPage, currentPage], () => {
    fetchChannelPayments(channel.value)
  })

  const fetchChannelPayments = (channelData) => {
    channel.value = channelData
    store
      .dispatch('app-channel/fetchChannelPayments', {
        perPage: perPage.value,
        page: currentPage.value,
        channelId: channel.value.id
      })
      .then(response => {
        channelPayments.value = response.data.channelPayments
        totalUsers.value = response.data.total
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
    fetchChannelPayments,
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
    channelPayments,

    resolveDepartmentStatusVariant,

  }
}
