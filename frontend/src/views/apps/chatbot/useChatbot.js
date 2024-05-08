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

  const chatbots = ref([])

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
  const totalChatbots = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [12, 24, 48, 96]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const typeChatbotFilter = ref(null)
  const planFilter = ref(null)
  const statusFilter = ref(null)

  const dataMeta = computed(() => {
    const localItemsCount = refUserListTable.value ? refUserListTable.value.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalChatbots.value,
    }
  })

  const refetchData = () => {
    fetchChatbots()
  }

  watch([currentPage, perPage, searchQuery, typeChatbotFilter, planFilter, statusFilter], () => {
    refetchData()
  })

  const fetchChatbots = (ctx, callback) => {
    store
      .dispatch('app-chatbot/fetchChatbots', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        typeChatbot: typeChatbotFilter.value,
        /*sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
        role: roleFilter.value,
        plan: planFilter.value,
        status: statusFilter.value,*/
      })
      .then(response => {
        //console.log('response chatbot')
        //console.log(response)
        //const { users, total } = response.data
        chatbots.value = response.data.chatbots
        //callback(users)
        totalChatbots.value = response.data.total
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

  //Adiciona uma campanha
  const addChatbot = chatbotData => {
    store.dispatch('app-chatbot/addChatbot', { chatbotData: chatbotData })
      .then(() => {  
        fetchChatbots()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Chatbot adicionado com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
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
    fetchChatbots,
    addChatbot,
    tableColumns,
    perPage,
    currentPage,
    totalChatbots,
    dataMeta,
    perPageOptions,
    searchQuery,
    sortBy,
    isSortDirDesc,
    refUserListTable,
    chatbots,

    resolveUserRoleVariant,
    resolveUserRoleIcon,
    resolveUserStatusVariant,
    refetchData,

    // Extra Filters
    typeChatbotFilter,
    planFilter,
    statusFilter,
  }
}
