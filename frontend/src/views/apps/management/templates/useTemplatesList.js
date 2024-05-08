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
    { key: 'show', label: 'Exibir'},
    { key: 'tem_name', label: 'Nome'},
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
    { key: 'category_name', label: 'Categoria' },
    { key: 'channel', label: 'Canal' },
    { key: 'language_name', label: 'Idioma' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Ações' }
  ]
  const templateItems = ref([])
  const perPageTemplate = ref(10)
  const totalTemplates = ref(0)
  const currentPageTemplate = ref(1)
  const perPageOptionsTemplate = [10, 25, 50, 100]
  const refTemplateListTable = ref(null)

  const categoryFilter = ref('')
  const statusTemplateFilter = ref('')
  const loadingRefresh = ref(false)
  const updateTemplates = ref(false)

  //Exibe as informações de paginação
  const dataMetaTemplate = computed(() => {
    const localItemsCount = refTemplateListTable.value ? refTemplateListTable.value.localItems.length : 0
    return {
      from: perPageTemplate.value * (currentPageTemplate.value - 1) + (localItemsCount ? 1 : 0),
      to: perPageTemplate.value * (currentPageTemplate.value - 1) + localItemsCount,
      of: totalTemplates.value,
    }
  })

  //Caso algum filtro seja alterado
  watch([categoryFilter, perPageTemplate, currentPageTemplate, statusTemplateFilter], () => {
    //Aplica o filtro
    fetchTemplates(categoryFilter.value, statusTemplateFilter.value, perPageTemplate.value, currentPageTemplate.value)
  })

  //Traz os templates cadastrados de acordo com o filtro aplicado
  const fetchTemplates = ()  => {
    //Traz as mensagens rápidas cadastradas
    store.dispatch('app-template/fetchTemplates', { 
      category: categoryFilter.value,
      status: statusTemplateFilter.value,
      perPage: perPageTemplate.value,
      page: currentPageTemplate.value,
      updateTemplate: updateTemplates.value,
      })
      .then(response => {
        templateItems.value = response.data.templates
        totalTemplates.value = response.data.totalTemplates

        updateTemplates.value = false
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
    templateItems,
    perPageTemplate,
    currentPageTemplate,
    perPageOptionsTemplate,
    refTemplateListTable,
    categoryFilter,
    statusTemplateFilter,
    loadingRefresh,
    updateTemplates,
    dataMetaTemplate,
    tableColumns,
    totalTemplates,
    fetchTemplates,

    resolveStatusVariant,

  }
}
