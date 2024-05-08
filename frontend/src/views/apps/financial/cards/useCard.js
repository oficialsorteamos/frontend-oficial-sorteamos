import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'
import axios from '@axios'
import { title } from '@core/utils/filter'
import Vue from 'vue'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const refUserListTable = ref(null)

  const cards = ref([])
  const cardsData = ref(0)

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
  const totalCards = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [12, 24, 48, 96]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const roleFilter = ref(null)
  const planFilter = ref(null)
  const statusFilter = ref(null)

  const dataMeta = computed(() => {
    const localItemsCount = 2
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalCards.value,
    }
  })

  const refetchData = () => {
    //refUserListTable.value.refresh()
    fetchCards()
  }

  watch([currentPage, perPage, searchQuery, roleFilter, planFilter, statusFilter], () => {
    refetchData()
  })

  const fetchCards = (ctx, callback) => {
    store
      /*.dispatch('app-user/fetchUsers', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
        role: roleFilter.value,
        plan: planFilter.value,
        status: statusFilter.value,
      })*/
      .dispatch('app-card/fetchCards', {
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
        //const { users, total } = response.data
        cards.value = response.data.cards
        //callback(users)
        totalCards.value = response.data.total
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
  const addCard = cardData => {
    store.dispatch('app-card/addCard', { cardData: cardData })
      .then(() => {
        refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Cartão adicionado com sucesso!',
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
    fetchCards,
    tableColumns,
    perPage,
    currentPage,
    totalCards,
    dataMeta,
    perPageOptions,
    searchQuery,
    sortBy,
    isSortDirDesc,
    refUserListTable,
    cards,
    cardsData,

    addCard,

    resolveUserRoleVariant,
    resolveUserRoleIcon,
    resolveUserStatusVariant,
    refetchData,

    // Extra Filters
    roleFilter,
    planFilter,
    statusFilter,
  }
}
