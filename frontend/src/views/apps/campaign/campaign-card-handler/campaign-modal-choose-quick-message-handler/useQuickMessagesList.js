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
  const tableColumnsCallWhatsapp = [
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
    {key: 'parameters', label: "Respostas Aceitas"},
    { key: 'actions', label: 'Ações' }
  ]

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
  const perPageQuickMessage = ref(10)
  const totalQuickMessages = ref(0)
  const currentPageQuickMessage = ref(1)
  const perPageOptionsQuickMessage = [10, 25, 50, 100]
  const refQuickMessageListTable = ref(null)

  const campaign = ref(null)

  //Exibe as informações de paginação
  const dataMetaQuickMessage = computed(() => {
    const localItemsCount = refQuickMessageListTable.value ? refQuickMessageListTable.value.localItems.length : 0
    return {
      from: perPageQuickMessage.value * (currentPageQuickMessage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPageQuickMessage.value * (currentPageQuickMessage.value - 1) + localItemsCount,
      of: totalQuickMessages.value,
    }
  })

  //Caso algum filtro seja alterado
  watch([perPageQuickMessage, currentPageQuickMessage], () => {
    //Aplica o filtro
    fetchQuickMessages(perPageQuickMessage.value, currentPageQuickMessage.value, campaign.value)
  })

  //Traz os templates cadastrados de acordo com o filtro aplicado
  const fetchQuickMessages = campaignData => {
    if(campaign.value == null) {
      campaign.value = campaignData
    }

    //Traz as mensagens rápidas cadastradas
    store.dispatch('app-campaign/fetchQuickMessages', {
      perPageQuickMessage: perPageQuickMessage.value,
      currentPageQuickMessage: currentPageQuickMessage.value,
      typeQuickMessage: campaign.value.campaign_type_id == 1? 3 : campaign.value.campaign_type_id == 2? 4 : 5, //Se for uma campanha de Whatsapp, seta 3, se for SMS, seta 4, se for ligação via WhatsApp, seta 5
      campaignId: campaign.value.id,
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
    dataMetaQuickMessage,
    tableColumns,
    tableColumnsCallWhatsapp,
    totalQuickMessages,
    fetchQuickMessages,

    resolveStatusVariant,

  }
}
