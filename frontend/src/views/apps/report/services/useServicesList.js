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
    { key: 'ser_protocol_number', label: 'Nº Protocolo', sortable: false, thClass: 'text-center', tdClass: 'text-center', tbClass: 'selectColumn' },
    { key: 'con_name', label: 'Contato', sortable: false },
    { key: 'tags', label: 'Tags', sortable: false, thClass: 'text-center', tdClass: 'text-center', tbClass: 'selectColumn' },
    { key: 'name', label: 'Operador', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'dep_name', label: 'Departamento', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'cha_name', label: 'Canal', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'cam_name', label: 'Campanha?', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'created_at', label: 'Iniciado em', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'ser_dt_end_service', label: 'Fechado em', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'type_status_service_id', label: 'Status', sortable: false, thClass: 'text-center', tdClass: 'text-center' },
    { key: 'actions', label:'Detalhes', thClass: 'text-center', tdClass: 'text-center' },
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

  watch([currentPage, perPage, searchQuery, departmentFilter, userFilter, channelFilter, originFilter, statusFilter, periodFilter, contactFilter, tagFilter, userSystemInteractionFilter], () => {
    if(searchQuery.value != '') {
      if(searchQuery.value.length > 3) {
        refetchData()
      }
    }
    else {
      refetchData()
    }
  })

  const fetchServices = (ctx, callback) => {
    store
      .dispatch('app-service/fetchServices', {
        q: searchQuery.value,
        perPage: perPage.value,
        page: currentPage.value,
        department: departmentFilter.value,
        user: userFilter.value,
        channel: channelFilter.value,
        origin: originFilter.value,
        status: statusFilter.value,
        period: periodFilter.value,
        userSystemInteractionDate: userSystemInteractionFilter.value,
        contact: contactFilter.value,
        tags: tagFilter.value,
      })
      .then(response => {
        const { services, total } = response.data
        servicesData.value = response.data.services
        //console.log(response.data.services)
        callback(services)
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

  //Traz os atendimentos associados a um chat de um contato de pouco em pouco, de acordo com o clique do usuário 
  const fetchServicesContact = (contactId, serviceId, offset) => {
    store.dispatch('app-service/fetchServicesContact', { id: contactId, serviceId: serviceId, offset: offset } )
      .then(response => {
        //Se existem atendimentos para ser exibidos
        if(response.data.length > 0) {
          if(offset == 0) {
          servicesTimelineData.value = response.data
          }
          else {
            //Insere cada novo atendimento carregado no array de serviços
            response.data.map(function(service, key) {
              servicesTimelineData.value.push(service)
            });
            console.log(response.data)
          }
        }
        else {
          //Esconde o botão que carrega mais atendimentos
          hiddenButtonService.value = true
        }
      })
  }

  const downloadServicesReport = () => {
    //Chama a loading screen
    Vue.prototype.$isLoading(true)
    store.dispatch('app-service/downloadServicesReport', {
      q: searchQuery.value,
      perPage: perPage.value,
      page: currentPage.value,
      department: departmentFilter.value,
      user: userFilter.value,
      channel: channelFilter.value,
      origin: originFilter.value,
      status: statusFilter.value,
      period: periodFilter.value,
      userSystemInteractionDate: userSystemInteractionFilter.value,
      contact: contactFilter.value,
      tags: tagFilter.value,
    })
    .then(response => {
      console.log(response.data)
      const anchor = document.createElement("a")
      anchor.setAttribute("download", response.data.filename)
      anchor.setAttribute("href", response.data.linkData)
      document.body.appendChild(anchor)
      anchor.click();
      document.body.removeChild(anchor)
      //Atualiza a lista de contatos do mailing
      //refetchData()
      toast({
        component: ToastificationContent,
        props: {
          title: 'Download do relatório realizado com sucesso!',
          icon: 'CheckIcon',
          variant: 'success',
        },
      })
    })
    .finally(() => {
      //Esconde a loading screen
      Vue.prototype.$isLoading(false) 
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
    fetchServices,
    fetchServicesContact,
    downloadServicesReport,
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
