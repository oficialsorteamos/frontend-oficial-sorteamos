import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'
import { title } from '@core/utils/filter'
import Vue from 'vue'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const refUserListTable = ref(null)


  // Table Handlers
  const tableColumns = [
    { key: 'show', label: 'Detalhes'},
    { key: 'type_cost', label: 'Tipo de Custo'},
    { key: 'created_at', label: 'Data' },
    { key: 'cos_value', label: 'Valor' },
  ]
  const costItems = ref([])
  const perPageTemplate = ref(10)
  const totalTemplates = ref(0)
  const currentPageTemplate = ref(1)
  const perPageOptionsTemplate = [10, 25, 50, 100]
  const refTemplateListTable = ref(null)

  const periodFilter = ref('')
  const finalDateFilter = ref('')
  const loadingRefresh = ref(false)

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
  watch([periodFilter, perPageTemplate, currentPageTemplate, finalDateFilter], () => {
    //Aplica o filtro
    fetchCosts(periodFilter.value, finalDateFilter.value, perPageTemplate.value, currentPageTemplate.value)
  })

  //Traz os templates cadastrados de acordo com o filtro aplicado
  const fetchCosts = ()  => {
    //Traz as mensagens rápidas cadastradas
    store.dispatch('app-cost/fetchCosts', { 
      period: periodFilter.value,
      //status: finalDateFilter.value,
      perPage: perPageTemplate.value,
      page: currentPageTemplate.value,
      })
      .then(response => {
        console.log('response')
        console.log(response)
        costItems.value = response.data.costs
        totalTemplates.value = response.data.total
      })
  }

  const clearFilter = () => {
    periodFilter.value = ''
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  const resolveStatusVariant = status => {
    if (status === 'Aguardando Pagamento') return 'primary'
    if (status === 'Estornado' || status === 'Pagamento em Análise') return 'warning'
    if (status === 'Pago') return 'success'
    if (status === 'Vencido') return 'danger'
    return 'primary'
  }

  const checkFlagCard = cardNumber => {
    if (cardNumber.slice(0, 1) == 4) return 'visa'
    else if ((cardNumber.slice(0, 2) >= 51 && cardNumber.slice(0, 2) <= 55) || (cardNumber.slice(0, 4) >= 2221 && cardNumber.slice(0, 2) <= 2720)) return 'mastercard'
    else if (cardNumber.slice(0, 2) == 34 || cardNumber.slice(0, 2) == 37) return 'amex'
    return ''
  }

  return {
    costItems,
    perPageTemplate,
    currentPageTemplate,
    perPageOptionsTemplate,
    refTemplateListTable,
    periodFilter,
    finalDateFilter,
    loadingRefresh,
    dataMetaTemplate,
    tableColumns,
    totalTemplates,
    fetchCosts,
    clearFilter,

    resolveStatusVariant,
    checkFlagCard,

  }
}
