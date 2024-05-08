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
    { key: 'payment_method', label: 'Forma de Pagamento'},
    { key: 'created_at', label: 'Data' },
    { key: 'cre_value', label: 'Valor' },
    { key: 'status', label: 'Status' },
  ]
  const creditItems = ref([])
  const cardsData = ref(0)
  const balance = ref(0)
  const perPage = ref(10)
  const totalCredits = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [10, 25, 50, 100]


  const periodFilter = ref('')
  const loadingRefresh = ref(false)

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = refCreditListTable.value ? refCreditListTable.value.localItems.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalCredits.value,
    }
  })

  //Caso algum filtro seja alterado
  watch([periodFilter, perPage, currentPage], () => {
    //Aplica o filtro
    fetchCredits(periodFilter.value, perPage.value, currentPage.value)
  })

  //Traz os templates cadastrados de acordo com o filtro aplicado
  const fetchCredits = ()  => {
    //Traz as mensagens rápidas cadastradas
    store.dispatch('app-credit/fetchCredits', { 
      period: periodFilter.value,
      perPage: perPage.value,
      page: currentPage.value,
      })
      .then(response => {
        creditItems.value = response.data.credits
        totalCredits.value = response.data.total
        balance.value = 'R$ ' + response.data.balance.toFixed(2).toString().replace(".", ",")
      })
  }

  //Adiciona um cartão
  const addCard = cardData => {
    store.dispatch('app-credit/addCard', { cardData: cardData })
      .then(() => {
        cardsData.value = cardsData.value + 1
        toast({
          component: ToastificationContent,
          props: {
            title: 'Cartão adicionado com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
  }

  //Adiciona crédito para campanhas
  const addCredit = creditData => {
    Vue.prototype.$isLoading(true)
    store.dispatch('app-credit/addCredit', { creditData: creditData })
      .then(response => {  
        //Se houver alguma mensagem de erro
        if(response.data.errorMessage) {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Erro ao Adicionar Crédito',
              text: response.data.errorMessage,
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          },
          {
            timeout: 8000,
          })  
        }
        else {
          fetchCredits()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Crédito adicionado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        }
      })
      .finally(() => {
        //Esconde a loading screen
        Vue.prototype.$isLoading(false) 
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
    creditItems,
    perPage,
    currentPage,
    perPageOptions,
    refCreditListTable,
    periodFilter,
    loadingRefresh,
    dataMeta,
    tableColumns,
    totalCredits,
    fetchCredits,
    addCard,
    addCredit,
    clearFilter,
    cardsData,
    balance,

    resolveStatusVariant,
    checkFlagCard,

  }
}
