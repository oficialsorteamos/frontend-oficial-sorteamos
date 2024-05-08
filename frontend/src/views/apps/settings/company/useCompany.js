import { ref, computed, watch, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import companyStoreModule from './companyStoreModule'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Swal from 'sweetalert2'
import Vue from 'vue'

export default function useChannel(props, emit) {
  
  const toast = useToast()

  const company = ref(null)
  const plan = ref(null)
  const whiteLabel = ref(null)
  const parametersCharge = ref(null)
  const parametersGeneral = ref([])
  const servicesData = ref([])
  //Mostra ou não o botão exibir mais atendimentos
  const hiddenButtonService = ref(false)

  const COMPANY_APP_STORE_MODULE_NAME = 'app-company'

  // Register module
  if (!store.hasModule(COMPANY_APP_STORE_MODULE_NAME)) store.registerModule(COMPANY_APP_STORE_MODULE_NAME, companyStoreModule)

  // UnRegister on leave
  onUnmounted(() => {
    if (store.hasModule(COMPANY_APP_STORE_MODULE_NAME)) store.unregisterModule(COMPANY_APP_STORE_MODULE_NAME)
  })

  //Traz os dados da empresa
  const fetchCompany = () => {
    store.dispatch('app-company/fetchCompany')
      .then(response => { 
        company.value = response.data.company
        console.log(company.value)
      })
      .catch(error => {
        
      })
  }

  //Traz os dados do plano
  const fetchPlan = () => {
    store.dispatch('app-company/fetchPlan')
      .then(response => { 
        plan.value = response.data.plan
        //console.log(plan.value)
      })
      .catch(error => {
        
      })
  }
  fetchPlan()

  //Traz os dados do plano
  const fetchWhiteLabel = () => {
    store.dispatch('app-company/fetchWhiteLabel')
      .then(response => { 
        whiteLabel.value = response.data.whiteLabel
        //console.log(plan.value)
      })
      .catch(error => {
        
      })
  }
  fetchWhiteLabel()

  //Traz os parâmetros de cobrança
  const fetchParametersCharge = () => {
    store.dispatch('app-company/fetchParametersCharge')
      .then(response => { 
        parametersCharge.value = response.data.parametersCharge
        //console.log('parametersCharge.value')
      })
      .catch(error => {
        
      })
  }
  fetchParametersCharge()

  //Traz os parâmetros de cobrança
  const fetchParametersGeneral = () => {
    store.dispatch('app-company/fetchParametersGeneral')
      .then(response => {  
        var d  =  response.data.parametersGeneral[0].par_value
        response.data.parametersGeneral[0].par_value  = d.substring(8, 10) + '/' + d.substring(5, 7) + '/' + d.substring(0, 4)
        parametersGeneral.value = response.data.parametersGeneral
      })
      .catch(error => {
        
      })
  }
  fetchParametersGeneral()

  return {
    fetchCompany,
    company,
    plan,
    whiteLabel,
    parametersCharge,
    parametersGeneral,
    servicesData,
    hiddenButtonService,
  }
}
