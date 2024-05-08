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
    { key: 'type_fee', label: 'Tipo de Taxa'},
    { key: 'fee_value', label: 'Valor' },
    { key: 'actions', label: 'Ações' },
  ]
  const fees = ref([])
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

  //Traz os templates cadastrados de acordo com o filtro aplicado
  const fetchFees = ()  => {
    //Traz as mensagens rápidas cadastradas
    store.dispatch('app-fee/fetchFees', { 
      perPage: perPage.value,
      page: currentPage.value,
      })
      .then(response => {
        fees.value = response.data.fees
        totalCredits.value = response.data.total
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

  const checkFlagCard = cardNumber => {
    if (cardNumber.slice(0, 1) == 4) return 'visa'
    else if ((cardNumber.slice(0, 2) >= 51 && cardNumber.slice(0, 2) <= 55) || (cardNumber.slice(0, 4) >= 2221 && cardNumber.slice(0, 2) <= 2720)) return 'mastercard'
    else if (cardNumber.slice(0, 2) == 34 || cardNumber.slice(0, 2) == 37) return 'amex'
    return ''
  }

  return {
    fees,
    perPage,
    currentPage,
    perPageOptions,
    refCreditListTable,
    dataMeta,
    tableColumns,
    totalCredits,
    fetchFees,
    cardsData,

    resolveStatusVariant,
    checkFlagCard,

  }
}
