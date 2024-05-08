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

  const refCreditListTable = ref(null)

  // Table Handlers
  const tableColumns = [
    { key: 'company', label: 'Empresa'},
    { key: 'invoice', label: 'Competência'},
    { key: 'par_value_order', label: 'Valor da Ordem' },
    { key: 'status', label: 'Status' },
    { key: 'par_link_payment_receipt', label: 'Comprovante' },
    { key: 'actions', label: 'Ações' },
  ]
  const paymentOrders = ref([])
  const baseUrlStorage = ref('')
  const cardsData = ref(0)
  const perPage = ref(10)
  const totalCredits = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = refCreditListTable.value ? refCreditListTable.value.localItems.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalCredits.value,
    }
  })

  const fetchPaymentOrders = ()  => {
    //Traz as mensagens rápidas cadastradas
    store.dispatch('app-payment-order/fetchPaymentOrders', { 
      perPage: perPage.value,
      page: currentPage.value,
      })
      .then(response => {
        paymentOrders.value = response.data.paymentOrders
        totalCredits.value = response.data.total
        baseUrlStorage.value = response.data.baseUrlStorage
      })
  }


  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  const resolveStatusVariant = status => {
    if (status === 1) return 'primary'
    if (status === 2) return 'success'
    if (status === 3) return 'danger'
    return 'primary'
  }

  const checkFlagCard = cardNumber => {
    if (cardNumber.slice(0, 1) == 4) return 'visa'
    else if ((cardNumber.slice(0, 2) >= 51 && cardNumber.slice(0, 2) <= 55) || (cardNumber.slice(0, 4) >= 2221 && cardNumber.slice(0, 2) <= 2720)) return 'mastercard'
    else if (cardNumber.slice(0, 2) == 34 || cardNumber.slice(0, 2) == 37) return 'amex'
    return ''
  }

  return {
    paymentOrders,
    perPage,
    currentPage,
    perPageOptions,
    refCreditListTable,
    dataMeta,
    tableColumns,
    totalCredits,
    fetchPaymentOrders,
    cardsData,
    baseUrlStorage,

    resolveStatusVariant,
    checkFlagCard,

  }
}
