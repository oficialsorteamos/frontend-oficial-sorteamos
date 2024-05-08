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
    { key: 'com_name', label: 'Nome/Razão Social', sortable: false },
    { key: 'partner', label: 'Parceiro', sortable: false },
    { key: 'status_id', label: 'Status', sortable: false },
    { key: 'actions', label: "Ações" },
  ]
  const perPage = ref(9)
  const totalCompanies = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const companies = ref([])
  const partnerFilter = ref('')
  const companyStatusFilter = ref('')
  const overdueInvoiceFilter = ref('')
  const daysDueContractFilter = ref('')
  const typePartnerFilter = ref('')
  const isWhiteLabel = ref(1)

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = companies.value ? companies.value.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalCompanies.value,
    }
  })

  const refetchData = () => {
    fetchCompanies()
  }

  watch([currentPage, perPage, searchQuery, partnerFilter, companyStatusFilter, overdueInvoiceFilter, typePartnerFilter, daysDueContractFilter], () => {
    refetchData()
  })

  const fetchCompanies = () => {
    store
      .dispatch('app-company/fetchCompanies', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        partner: partnerFilter.value,
        companyStatus: companyStatusFilter.value,
        overdueInvoice: overdueInvoiceFilter.value,
        daysDueContract: daysDueContractFilter.value,
        typePartner: typePartnerFilter.value,
      })
      .then(response => {
        const { total } = response.data
        companies.value = response.data.companies
        totalCompanies.value = total
        isWhiteLabel.value = response.data.isWhiteLabel
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

  const resolveCompanySituationVariant = situationId => {
    if (situationId === 1) return 'success'
    if (situationId === 2) return 'danger'
    return 'primary'
  }

  const resolveDepartmentStatusVariant = status => {
    if (status === 'pending') return 'warning'
    if (status === 'A') return 'success'
    if (status === 'I') return 'secondary'
    return 'primary'
  }

  return {
    fetchCompanies,
    companies,
    isWhiteLabel,
    tableColumns,
    perPage,
    currentPage,
    totalCompanies,
    dataMeta,
    perPageOptions,
    searchQuery,
    typePartnerFilter,
    partnerFilter,
    companyStatusFilter,
    overdueInvoiceFilter,
    daysDueContractFilter,
    sortBy,
    isSortDirDesc,
    refUserListTable,

    resolveUserRoleVariant,
    resolveCompanySituationVariant,
    resolveDepartmentStatusVariant,
    refetchData,

  }
}
