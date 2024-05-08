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
  const fowardingSettingsData = ref([])
  const baseUrlStorage = ref('')

  // Table Handlers
  const tableColumns = [
    { key: 'channel', label: 'Canal', sortable: false, thClass: 'text-center', tdClass: 'text-center', tbClass: 'selectColumn' },
    { key: 'dia_message', label: 'Mensagem Inicial', sortable: false, thClass: 'text-center', tdClass: 'text-center', tbClass: 'selectColumn' },
    { key: 'chatbot', label: 'Chatbot', sortable: false, thClass: 'text-center', tdClass: 'text-center', tbClass: 'selectColumn' },
    { key: 'department', label: 'Departamento', sortable: false, thClass: 'text-center', tdClass: 'text-center', tbClass: 'selectColumn' },
    { key: 'fair_distribution', label: 'Distr. Igualitária', sortable: false, thClass: 'text-center', tdClass: 'text-center', tbClass: 'selectColumn' },
    { key: 'actions', label:'Ações', thClass: 'text-center', tdClass: 'text-center' },
  ]
  const perPage = ref(12)
  const totalUsers = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [12, 24, 48, 96]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const dialerId = ref(null)

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

  watch([currentPage, perPage,], () => {
    refetchData()
  })

  const fetchFowardingSettings = (dialerIdData) => {
    if(!dialerId.value) {
      dialerId.value = dialerIdData
    }
    store
      .dispatch('app-dialer/fetchFowardingSettings', {
        dialerId: dialerId.value,
        perPage: perPage.value,
        page: currentPage.value,
      })
      .then(response => {
        fowardingSettingsData.value = response.data.fowardingSettings
        baseUrlStorage.value = response.data.baseUrlStorage
        //totalUsers.value = total
      })
  }

  const addFowardingSetting = (fowardingSettingData) => {
    store
      .dispatch('app-dialer/addFowardingSetting', fowardingSettingData)
      .then(response => {
        fetchFowardingSettings(dialerId.value)
        toast({
          component: ToastificationContent,
          props: {
            title: 'Configurações de encaminhamento adicionadas com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
  }

  const updateFowardingSetting = (fowardingSettingData) => {
    store
      .dispatch('app-dialer/updateFowardingSetting', fowardingSettingData)
      .then(response => {
        fetchFowardingSettings(dialerId.value)
        toast({
          component: ToastificationContent,
          props: {
            title: 'COnfigurações de encaminhamento atualizadas com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
  }

  //Remove uma mensagem rápida
  const removeFowardingSetting = fowardingSettingId => {
    store.dispatch('app-dialer/removeFowardingSetting', { id: fowardingSettingId })
      .then(() => {
        fetchFowardingSettings(dialerId.value)
        toast({
          component: ToastificationContent,
          props: {
            title: 'Configuração de encaminhamento removida',
            text: 'COnfiguração de encaminhamento removida com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
  }
  

  return {
    fetchFowardingSettings,
    addFowardingSetting,
    updateFowardingSetting,
    removeFowardingSetting,
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
    fowardingSettingsData,
    baseUrlStorage,
    
    refetchData,
  }
}
