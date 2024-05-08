import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'
import { title } from '@core/utils/filter'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()


  // Table Handlers
  const tableColumns = [
    { key: 'show', label: 'Exibir'},
    { key: 'title', label: 'Nome'},
    { 
      key: 'content', 
      label: 'Mensagem', 
      formatter: (value) =>  {
        //Limita a quantidade de caracteres da mensagem apresentados
        if(value.length > 50) {
          return value.substring(0,50)+".."
        } else {
          return value
        }
        
      }
    },
    { key: 'actions', label: 'Ações' }
  ]
  const quickMessageItems = ref([])
  const perPageQuickMessage = ref(5)
  const totalQuickMessages = ref(0)
  const currentPageQuickMessage = ref(1)
  const perPageOptionsQuickMessage = [5, 10, 50, 100]
  const refQuickMessageListTable = ref(null)

  const categoryFilter = ref('')
  const statusTemplateFilter = ref('')
  const channelFilter = ref('')
  const loadingRefresh = ref(false)

  //Exibe as informações de paginação
  const dataMetaTemplate = computed(() => {
    const localItemsCount = refQuickMessageListTable.value ? refQuickMessageListTable.value.localItems.length : 0
    return {
      from: perPageQuickMessage.value * (currentPageQuickMessage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPageQuickMessage.value * (currentPageQuickMessage.value - 1) + localItemsCount,
      of: totalQuickMessages.value,
    }
  })

  //Caso algum filtro seja alterado
  watch([categoryFilter, perPageQuickMessage, currentPageQuickMessage, statusTemplateFilter], () => {
    //Aplica o filtro
    fetchQuickMessages()
  })

  //Traz os templates cadastrados de acordo com o filtro aplicado
  const fetchQuickMessages = ()  => {
    //Traz as mensagens rápidas cadastradas
    store.dispatch('app-chatbot/fetchQuickMessages', { 
      perPageQuickMessage: perPageQuickMessage.value,
      currentPageQuickMessage: currentPageQuickMessage.value,
      typeQuickMessage: 2,
      })
      .then(response => {
        quickMessageItems.value = response.data.quickMessages
        totalQuickMessages.value = response.data.totalQuickMessages
      })
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  const resolveStatusVariant = role => {
    if (role === 'Enviado') return 'primary'
    if (role === 'Pendente' || role === 'Sinalizado') return 'warning'
    if (role === 'Aprovado') return 'success'
    if (role === 'Reprovado' || role === 'Desativado' || role === 'Erro ao Enviar') return 'danger'
    return 'primary'
  }

  return {
    quickMessageItems,
    perPageQuickMessage,
    currentPageQuickMessage,
    perPageOptionsQuickMessage,
    refQuickMessageListTable,
    categoryFilter,
    channelFilter,
    statusTemplateFilter,
    loadingRefresh,
    dataMetaTemplate,
    tableColumns,
    totalQuickMessages,
    fetchQuickMessages,

    resolveStatusVariant,

  }
}
