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

  const campaigns = ref([])
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
  const totalCampaigns = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [12, 24, 48, 96]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const roleFilter = ref(null)
  const planFilter = ref(null)
  const statusFilter = ref(null)
  const baseUrlStorage = ref('')
  const balance = ref(0)

  const dataMeta = computed(() => {
    const localItemsCount = refUserListTable.value ? refUserListTable.value.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalCampaigns.value,
    }
  })

  const refetchData = () => {
    //refUserListTable.value.refresh()
    fetchCampaigns()
  }

  watch([currentPage, perPage, searchQuery, roleFilter, planFilter, statusFilter], () => {
    refetchData()
  })

  const fetchCampaigns = (ctx, callback) => {
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
      .dispatch('app-campaign/fetchCampaigns', {
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
        campaigns.value = response.data.campaigns
        baseUrlStorage.value = response.data.baseUrlStorage
        //callback(users)
        totalCampaigns.value = response.data.total
        balance.value = 'R$ ' + response.data.balance.toFixed(2).toString().replace(".", ",")
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
  const addCampaign = campaignData => {
    store.dispatch('app-campaign/addCampaign', { campaignData: campaignData })
      .then(() => {  
        fetchCampaigns()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Canal adicionado com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
  }

  //Adiciona uma campanha
  const addCard = cardData => {
    store.dispatch('app-campaign/addCard', { cardData: cardData })
      .then(() => {
        cardsData.value = cardsData.value + 1
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

  //Adiciona crédito para campanhas
  const addCredit = creditData => {
    Vue.prototype.$isLoading(true)
    store.dispatch('app-campaign/addCredit', { creditData: creditData })
      .then(response => {  
        //Se houver alguma mensagem de erro
        if(response.data.errorMessage) {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Erro ao Adicionar Crédito',
              text: response.data.errorMessage,
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          },
          {
            timeout: 8000,
          })  
        }
        else {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Crédito adicionado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        }
      })
      .finally(() => {
        //Esconde a loading screen
        Vue.prototype.$isLoading(false) 
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
    fetchCampaigns,
    addCampaign,
    tableColumns,
    perPage,
    currentPage,
    totalCampaigns,
    dataMeta,
    perPageOptions,
    searchQuery,
    sortBy,
    isSortDirDesc,
    refUserListTable,
    campaigns,
    cardsData,
    balance,
    
    baseUrlStorage,

    addCard,
    addCredit,

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
