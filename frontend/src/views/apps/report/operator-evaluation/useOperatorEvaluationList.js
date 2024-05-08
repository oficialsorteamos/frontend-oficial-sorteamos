import { ref, watch, computed, } from '@vue/composition-api'
import store from '@/store'
import Vue from 'vue'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {

  // Use toast
  const toast = useToast()

  const refUserListTable = ref(null)
  const servicesData = ref([])
  const departmentFilter = ref('')
  const userFilter = ref('')
  const channelFilter = ref('')
  const originFilter = ref('')
  const statusFilter = ref('')
  const periodFilter = ref('')
  const userSystemInteractionFilter = ref('')
  const contactFilter = ref('')
  const tagFilter = ref('')

  const servicesTimelineData = ref([])
  //Mostra ou não o botão exibir mais atendimentos
  const hiddenButtonServiceTimeline = ref(false)

  // Table Handlers
  const tableColumns = [
    { key: 'company', label: 'Operadores', class: 'text-center' },
    { key: 'views', label: 'Nº Atendimentos Totais', class: 'text-center' },
    { key: 'totalServicesEvaluations', label: 'Nº Atendimentos Avaliados', class: 'text-center' },
    { key: 'rating', label: 'Avaliação', class: 'text-center' },
  ]
  const perPage = ref(50)
  const totalUsers = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [50, 100, 150, 200]
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

  watch([currentPage, perPage, searchQuery, departmentFilter, userFilter, channelFilter, originFilter, statusFilter, periodFilter, contactFilter, tagFilter, userSystemInteractionFilter], () => {
    fetchOperatorEvaluation()  
  })

  const fetchOperatorEvaluation = (ctx, callback) => {
    store
      .dispatch('app-operator-evaluation/fetchOperatorEvaluation', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        user: userFilter.value,
        period: periodFilter.value,
      })
      .then(response => {
        servicesData.value = response.data.operators
        //console.log(response.data.services)        
        totalUsers.value = response.data.total
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

  const clearFilter = () => {
    departmentFilter.value = ''
    userFilter.value = ''
    channelFilter.value = ''
    originFilter.value = ''
    statusFilter.value = ''
    periodFilter.value = ''
    userSystemInteractionFilter.value = ''
    contactFilter.value = ''
    tagFilter.value = ''
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  const resolveServiceStatusVariant = status => {
    if (status === 3) return 'danger'
    if (status === 1) return 'success'
    if (status === 2) return 'warning'
    if (status === 4) return 'dark'
    return 'info'
  }

  return {
    fetchOperatorEvaluation,
    clearFilter,
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
    servicesData,
    departmentFilter,
    userFilter,
    channelFilter,
    originFilter,
    statusFilter,
    periodFilter,
    userSystemInteractionFilter,
    contactFilter,
    tagFilter,
    hiddenButtonServiceTimeline,
    servicesTimelineData,

    resolveServiceStatusVariant,
    refetchData,
  }
}
