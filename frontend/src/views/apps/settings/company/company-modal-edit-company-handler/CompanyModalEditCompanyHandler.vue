<template>
  <b-tabs
    vertical
    content-class="col-12 col-md-9 mt-1 mt-md-0"
    pills
    nav-wrapper-class="col-md-3 col-12"
    nav-class="nav-left"
  >

    <!-- general tab -->
    <b-tab active>

      <!-- title -->
      <template #title>
        <feather-icon
          icon="UserIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('chat.chatModalEditContactHandler.general') }}</span>
      </template>

      <company-general
        :company="company"
        :clear-user-data="clearUserData"
        @update-company-general="updateCompanyGeneral"
      />
    </b-tab>
    <!--/ general tab -->

    <!-- change password tab -->
    <b-tab>

      <!-- title -->
      <template #title>
        <feather-icon
          icon="MapPinIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">{{ $t('chat.chatModalEditContactHandler.address') }}</span>
      </template>
      <company-address 
        :company="addressData.com_address? addressData : company?  company : clearAddressData"
        :contactId="company.id? company.id : null"
        :addressId="company? company.id : null"
        :loading="loading"
        :clear-user-data="clearUserData"
        @add-contact-address="addContactAddress"
        @update-contact-address="updateCompanyGeneral"
        @get-address-user="getAddressUser"
      />
    </b-tab>
  </b-tabs>
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue'
import { ref, onMounted, onBeforeMount } from '@vue/composition-api'
import store from '@/store'
import CompanyGeneral from './company-general/CompanyGeneral.vue'
import CompanyAddress from './company-address/CompanyAddress.vue'
import AccountSettingInformation from './AccountSettingInformation.vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BTabs,
    BTab,
    CompanyGeneral,
    CompanyAddress,
    AccountSettingInformation,
  },
  props: {
    company: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      options: {},
    }
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
  },
  setup(props, { emit }) {

    //Toast Notification
    const toast = useToast()

    const blankUser = {

    }
    const contactData = ref(JSON.parse(JSON.stringify(blankUser)))
    //Limpa os dados do popup
    const clearUserData = () => {
      contactData.value = JSON.parse(JSON.stringify(blankUser))
    }

    //Atualiza os dados do contato
    const updateCompanyGeneral = companyData => {
      store.dispatch('app-company/updateCompanyGeneral', { companyData: companyData })
        .then(response => {
          emit('set-company', response.data.company) 

          toast({
            component: ToastificationContent,
            props: {
              title: 'Dados da empresa atualizados com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }


    //Atualiza os dados de acesso e detalhes da conta o usuário
    const updateContactAddress = userData => {
      store.dispatch('app-chat/updateContactAddress', { address: userData })
        .then(response => {
          console.log('response.data.address')
          console.log(response.data.address)
          emit('set-address-contact', response.data.address)
          
          toast({
            component: ToastificationContent,
            props: {
              title: 'Endereço atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const addContactAddress = userData => {
      store.dispatch('app-chat/addContactAddress',  userData)
        .then(response => {
          console.log('response.data.address')
          console.log(response.data.address)
          emit('set-address-contact', response.data.address)
          
          toast({
            component: ToastificationContent,
            props: {
              title: 'Endereço adicionado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //###################################### ADDRESS ###########################################

    
    const loading = ref(false)
    const addressData = ref({})

    //Busca o endereço com base no CEP digitado
    const getAddressUser = cep => {
      //Se foi digitado todos os caracteres do CEP
      if(cep.length == 9) {
        //Mostra o spinner
        loading.value = true

        store.dispatch('app-company/getAddressUser', { cep: cep })
        .then(response => {
          //Se não houve erro ao buscar o endereço
          if(!response.data.error) { 
            addressData.value.com_address = response.data.address.logradouro 
            addressData.value.com_province = response.data.address.bairro 
            //userData.value.address_complement = response.data.complemento 
            addressData.value.com_city = response.data.address.localidade 
            addressData.value.com_state = response.data.state.sta_name 
            addressData.value.com_country = response.data.country.cou_name

          }
          else {
            addressData.value.com_address = null 
            addressData.value.com_address_number = null 
            addressData.value.com_province = null 
            //addressLocal.value.address_complement = response.data.complemento 
            addressData.value.com_city = null 
            addressData.value.com_state = null 
            addressData.value.com_country = null
            toast({
              component: ToastificationContent,
              props: {
                title: response.data.error,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
           
          //console.log(response.data)

          //Esconde o spinner
          loading.value = false
        })
        .catch(error => {
        })
      }
    }

    const clearAddressDataBlank = {
      com_zip_code: '',
      com_address: '',
      com_address_number: '',
      com_complement: '',
      com_province: '',
      com_city: '',
      com_state: '',
      com_country: '',
    }
    const clearAddressData = ref(JSON.parse(JSON.stringify(clearAddressDataBlank)))


    return {
      clearUserData,
      updateCompanyGeneral,
      addContactAddress,
      updateContactAddress,
      getAddressUser,
      addressData,
      loading,
      clearAddressData,
    }
  }
}
</script>
