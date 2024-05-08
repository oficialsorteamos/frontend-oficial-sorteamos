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

      <chat-contact-general
        :contact="contact"
        :clear-user-data="clearUserData"
        @update-contact-general="updateContactGeneral"
        @upload-photo="uploadPhoto"
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

      <chat-contact-address 
        :contact="addressData.street? addressData : contact.addresses[0]?  contact.addresses[0] : clearAddressData"
        :contactId="contact.id? contact.id : null"
        :addressId="contact.addresses[0]? contact.addresses[0].id : null"
        :loading="loading"
        :clear-user-data="clearUserData"
        @add-contact-address="addContactAddress"
        @update-contact-address="updateContactAddress"
        @get-address-user="getAddressUser"
      />
    </b-tab>
    <!--/ change password tab -->

    <!-- info -->
    <!--
    <b-tab>

      <template #title>
        <feather-icon
          icon="InfoIcon"
          size="18"
          class="mr-50"
        />
        <span class="font-weight-bold">Information</span>
      </template>

      <account-setting-information
        v-if="options.info"
        :information-data="options.info"
      />
    </b-tab>
    -->
  </b-tabs>
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue'
import { ref, onMounted, onBeforeMount } from '@vue/composition-api'
import store from '@/store'
import ChatContactGeneral from './chat-contact-general/ChatContactGeneral.vue'
import ChatContactAddress from './chat-contact-address/ChatContactAddress.vue'
import AccountSettingInformation from './AccountSettingInformation.vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BTabs,
    BTab,
    ChatContactGeneral,
    ChatContactAddress,
    AccountSettingInformation,
  },
  props: {
    contact: {
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
    const updateContactGeneral = contactData => {
      store.dispatch('app-chat/updateContactGeneral', { contactData: contactData })
        .then(response => {
          emit('set-contact', response.data.contact) 

          toast({
            component: ToastificationContent,
            props: {
              title: 'Dados do contato atualizados com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const photo = ref('')
    
    //Faz o upload da foto de avatar do usuário
    const uploadPhoto = (event) => {
      photo.value = event.target.files[0]
      
      const formData = new FormData()
      formData.append('name', 'ivahy.jpg')
      formData.append('file', photo.value)
      formData.append('userId', props.contact.id)
      
      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }
      store.dispatch('app-user/uploadPhoto', formData, config)
        .then(() => {
          //emit('get-contact')

          toast({
            component: ToastificationContent,
            props: {
              title: 'Avatar atualizado com sucesso!',
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

        store.dispatch('app-chat/getAddressUser', { cep: cep })
        .then(response => {
          //Se não houve erro ao buscar o endereço
          if(!response.data.error) { 
            addressData.value.street = response.data.address.logradouro 
            addressData.value.district = response.data.address.bairro 
            //userData.value.address_complement = response.data.complemento 
            addressData.value.city = response.data.address.localidade 
            addressData.value.state = response.data.state 
            addressData.value.country = response.data.country

          }
          else {
            addressData.value.street = null 
            addressData.value.number = null 
            addressData.value.district = null 
            //addressLocal.value.address_complement = response.data.complemento 
            addressData.value.city = null 
            addressData.value.state = null 
            addressData.value.country = null
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
      cep: '',
      street: '',
      number: '',
      address_complement: '',
      district: '',
      city: '',
      state: '',
      country: '',
    }
    const clearAddressData = ref(JSON.parse(JSON.stringify(clearAddressDataBlank)))


    return {
      clearUserData,
      updateContactGeneral,
      uploadPhoto,
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
