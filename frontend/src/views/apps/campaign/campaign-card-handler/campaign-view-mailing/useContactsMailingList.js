import { ref, watch, computed, } from '@vue/composition-api'
import store from '@/store'
import { title } from '@core/utils/filter'
import router from '@/router'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {

  // Use toast
  const toast = useToast()

  const refUserListTable = ref(null)
  const mailingData = ref([])
  const statusFilter = ref('')


  // Table Handlers
  const tableColumns = [
    { key: 'id', label: '', sortable: false, thClass: 'text-center', tdClass: 'text-center', tbClass: 'selectColumn' },
    { key: 'con_name', label: 'Nome', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'con_phone', label: 'Telefone', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'mes_content', label: 'Mensagem', sortable: false, thClass: 'text-center', tbClass: 'bTableThStyle' },
    { key: 'cha_name', label: 'Canal', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'tags', label: 'Tags', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'mai_dt_sending', label: 'Data Envio', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'mai_contact_returned', label: 'Retornou', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'mai_description', label: 'Status', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    
  ]
  const perPage = ref(12)
  const totalUsers = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [12, 24, 48, 96]
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

  watch([currentPage, perPage, searchQuery, statusFilter], () => {
    if(searchQuery.value != '') {
      if(searchQuery.value.length > 3) {
        refetchData()
      }
    }
    else {
      refetchData()
    }
  })

  const fetchMailing = (ctx, callback) => {
    store
      /*.dispatch('app-user/fetchUsers', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
      })*/
      .dispatch('app-campaign/fetchMailing', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        campaignId: router.currentRoute.params.id,
        status: statusFilter.value,
        /*sortBy: sortBy.value,
        sortDesc: isSortDirDesc.value,
        role: roleFilter.value,
        plan: planFilter.value,
        status: statusFilter.value,*/
      })
      .then(response => {
        const { mailing, total } = response.data
        mailingData.value = response.data.mailing 
        callback(mailing)
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
    if (status === 'Falha ao Enviar') return 'danger'
    if (status === 'Enviado' || status === 'Entregue') return 'success'
    if (status === 'Aguardando Envio') return 'primary'
    if (status === 'Blacklist') return 'dark'
    return 'info'
  }

  return {
    fetchMailing,
    tableColumns,
    perPage,
    currentPage,
    totalUsers,
    dataMeta,
    perPageOptions,
    searchQuery,
    statusFilter,
    sortBy,
    isSortDirDesc,
    refUserListTable,
    mailingData,

    resolveDepartmentStatusVariant,
    refetchData,

  }
}
