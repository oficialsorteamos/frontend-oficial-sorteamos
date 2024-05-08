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
    { key: 'par_corporate_name', label: 'Nome/Razão Social', sortable: true },
    { key: 'par_responsible_phone', label: 'Telefone Responsável', sortable: true },
    { key: 'total_companies', label: 'Total Empresas Assoc.', sortable: true },
    { key: 'par_partnership_started', label: 'Início Parceria', sortable: true },
    { key: 'actions', label: "Ações" },
  ]
  const perPage = ref(10)
  const totalPartners = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]
  const searchQuery = ref('')
  const typePartnerFilter = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const partners = ref([])

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = partners.value ? partners.value.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalPartners.value,
    }
  })

  const refetchData = () => {
    fetchPartners()
  }

  watch([currentPage, perPage, searchQuery, typePartnerFilter], () => {
    fetchPartners()
  })

  const fetchPartners = () => {
    store
      .dispatch('app-partner/fetchPartners', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        typePartner: typePartnerFilter.value,
      })
      .then(response => {
        const { total } = response.data
        partners.value = response.data.partners
        totalPartners.value = total
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
    fetchPartners,
    tableColumns,
    perPage,
    currentPage,
    totalPartners,
    dataMeta,
    perPageOptions,
    searchQuery,
    typePartnerFilter,
    sortBy,
    isSortDirDesc,
    refUserListTable,
    partners,

    resolveUserRoleVariant,
    resolveDepartmentSituationVariant,
    resolveDepartmentStatusVariant,
    refetchData,

  }
}
