import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'
import { title } from '@core/utils/filter'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {
  // Use toast
  const toast = useToast()

  const refInvoiceListTable = ref(null)

  // Table Handlers
  const tableColumns = [
    { key: 'show', label: 'Detalhes'},
    { key: 'inv_month_year', label: 'Mês/Ano'},
    { key: 'inv_closing', label: 'Fechamento' },
    { key: 'inv_due', label: 'Vencimento' },
    { key: 'total_invoice_value', label: 'Valor Total' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Ações' }
  ]
  const invoiceItems = ref([])
  const perPage = ref(10)
  const totalInvoices = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]

  const categoryFilter = ref('')
  const statusFilter = ref('')

  //Exibe as informações de paginação
  const dataMetaTemplate = computed(() => {
    const localItemsCount = refInvoiceListTable.value ? refInvoiceListTable.value.localItems.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalInvoices.value,
    }
  })

  //Caso algum filtro seja alterado
  watch([categoryFilter, perPage, currentPage, statusFilter], () => {
    //Aplica o filtro
    fetchInvoices(categoryFilter.value, statusFilter.value, perPage.value, currentPage.value)
  })

  //Traz os templates cadastrados de acordo com o filtro aplicado
  const fetchInvoices = ()  => {
    //Traz as mensagens rápidas cadastradas
    store.dispatch('app-invoice/fetchInvoices', { 
      //category: categoryFilter.value,
      status: statusFilter.value,
      perPage: perPage.value,
      page: currentPage.value,
      })
      .then(response => {
        invoiceItems.value = response.data.invoices
        totalInvoices.value = response.data.total
      })
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

  return {
    invoiceItems,
    perPage,
    currentPage,
    perPageOptions,
    refInvoiceListTable,
    categoryFilter,
    statusFilter,
    dataMetaTemplate,
    tableColumns,
    totalInvoices,
    fetchInvoices,

    resolveStatusVariant,

  }
}
