import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'
import { title } from '@core/utils/filter'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useCampaignTemplatesList() {
  // Use toast
  const toast = useToast()

  // Table Handlers
  const tableColumnsCampaignTemplates = [
    { key: 'show', label: 'Exibir'},
    { 
      key: 'body', 
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
  const campaignTemplateItems = ref([])
  const perPageCampaignTemplate = ref(10)
  const totalCampaignTemplates = ref(0)
  const currentPageCampaignTemplate = ref(1)
  const perPageOptionsCampaignTemplate = [10, 25, 50, 100]
  const refCampaignTemplateListTable = ref(null)
  const campaignId = ref(null)
  const templateMessageData = ref([]) 

  //const categoryFilter = ref('')
  //const statusTemplateFilter = ref('')
  const loadingCampaignTemplatesRefresh = ref(false)

  //Exibe as informações de paginação
  const dataMetaCampaignTemplate = computed(() => {
    const localItemsCount = refCampaignTemplateListTable.value ? refCampaignTemplateListTable.value.localItems.length : 0
    return {
      from: perPageCampaignTemplate.value * (currentPageCampaignTemplate.value - 1) + (localItemsCount ? 1 : 0),
      to: perPageCampaignTemplate.value * (currentPageCampaignTemplate.value - 1) + localItemsCount,
      of: totalCampaignTemplates.value,
    }
  })

  //Caso algum filtro seja alterado
  //watch([categoryFilter, perPageCampaignTemplate, currentPageCampaignTemplate, statusTemplateFilter], () => {
  watch([perPageCampaignTemplate, currentPageCampaignTemplate], () => {
    //Aplica o filtro
    //fetchCampaignTemplates(categoryFilter.value, statusTemplateFilter.value, perPageCampaignTemplate.value, currentPageCampaignTemplate.value)
    fetchCampaignTemplates()
  })

  //Traz os templates cadastrados de acordo com o filtro aplicado
  const fetchCampaignTemplates = campaignData  => {
    console.log('campaignData')
    console.log(campaignData)
    campaignId.value = campaignData.id
    //Traz as mensagens rápidas cadastradas
    store.dispatch('app-campaign/fetchCampaignTemplates', { 
      //category: categoryFilter.value,
      //status: statusTemplateFilter.value,
      perPage: perPageCampaignTemplate.value,
      page: currentPageCampaignTemplate.value,
      campaignId: campaignId.value,
      })
      .then(response => {
        campaignTemplateItems.value = response.data.templates
        totalCampaignTemplates.value = response.data.totalTemplates

        campaignData.pendencies = response.data.campaign.pendencies
        campaignData.totalContactMailing = response.data.campaign.totalContactMailing
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
    campaignTemplateItems,
    perPageCampaignTemplate,
    currentPageCampaignTemplate,
    perPageOptionsCampaignTemplate,
    refCampaignTemplateListTable,
    campaignId,
    templateMessageData,
    //categoryFilter,
    //statusTemplateFilter,
    loadingCampaignTemplatesRefresh,
    dataMetaCampaignTemplate,
    tableColumnsCampaignTemplates,
    totalCampaignTemplates,
    fetchCampaignTemplates,

    resolveStatusVariant,

  }
}
