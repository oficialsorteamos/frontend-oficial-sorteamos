<template>
  <b-card
    no-body
    class="border-primary"
  >
    <b-card-header class="d-flex justify-content-between align-items-center pt-75 pb-25">
      <h4 class="mb-0">
        {{ $t('user.userViewUserAddressCard.address') }}
      </h4>
      <feather-icon
        icon="EditIcon"
        size="18"
        class="cursor-pointer"
        v-b-modal.modal-edit-user-address
        @click="setUserData"
      />
    </b-card-header>

    <b-card-body>
      <div 
        v-if="userData.addresses[0]"
      >
        <ul class="list-unstyled my-1">
          <li>
            <span 
              class="align-middle"
              v-if="userData.addresses[0].add_zip_code"
            >
              <strong>{{ $t('user.userViewUserAddressCard.zipCode') }}: </strong> {{ userData.addresses[0].add_zip_code | VMask('##.###-###') }}
            </span>
            <span 
              v-else
            >
              -    
            </span>
          </li>
          <li
            class="my-25"
          >
            <span 
              class="align-middle"
              v-if="userData.addresses[0].add_street"
            >
              <strong>{{ $t('user.userViewUserAddressCard.street') }}: </strong> {{ userData.addresses[0].add_street }}, {{userData.addresses[0].add_number? userData.addresses[0].add_number : 'S/N'}}
            </span>
            <span 
              v-else
            >
              -    
            </span>
          </li>
          <li>
            <span class="align-middle"
              v-if="userData.addresses[0].add_district"
            >
              <strong>{{ $t('user.userViewUserAddressCard.district') }}: </strong> {{ userData.addresses[0].add_district }}
            </span>
            <span 
              v-else
            >
              -    
            </span>
          </li>
          <li>
            <span class="align-middle"
              v-if="userData.addresses[0].add_address_complement"
            >
              <strong>{{ $t('user.userViewUserAddressCard.complement') }}: </strong> {{ userData.addresses[0].add_address_complement }}
            </span>
            <span 
              v-else
            >
              -    
            </span>
          </li>
          <li 
            class="my-25"
          >
            <span class="align-middle"
              v-if="userData.addresses[0].add_city"
            >
              <strong>{{ $t('user.userViewUserAddressCard.city') }}: </strong> {{ userData.addresses[0].add_city }}
            </span>
            <span 
              v-else
            >
              -    
            </span>
          </li>
          <li>
            <span class="align-middle"
              v-if="userData.addresses[0].state"
            >
              <strong>{{ $t('user.userViewUserAddressCard.state') }}: </strong> {{ userData.addresses[0].state.sta_name }}
            </span>
            <span 
              v-else
            >
              -    
            </span>
          </li>
          <li
            class="my-25"
          >
            <span class="align-middle"
              v-if="userData.addresses[0].country"
            >
              <strong>{{ $t('user.userViewUserAddressCard.country') }}: </strong> {{ userData.addresses[0].country.cou_name }}
            </span>
            <span 
              v-else
            >
              -    
            </span>
          </li>
        </ul>
      </div>
      <div
        class="no-results"
        v-else
      > 
        <h5 
          class="mt-5"
          style="text-align: center"
        >
          {{ $t('user.userViewUserAddressCard.noAddressFound') }}
        </h5>
      </div>
    </b-card-body>
    <!-- Form para editar dados do usuário -->
    <b-modal
      id="modal-edit-user-address"
      :title="$t('user.userViewUserAddressCard.editUserAddress')"
      hide-footer
      size="lg"
    >
      <user-modal-edit-address-handler
        :user="user"
        :clear-user-data="clearUserData"
        :loading="loading"
        @get-address-user="getAddressUser"
        @update-user-address="updateUserAddress"
        @hide-modal="hideModal"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BCardBody, BBadge, BButton,
} from 'bootstrap-vue'
import {
  ref
} from '@vue/composition-api'
import store from '@/store'
import userModalEditAddressHandler from './user-modal-edit-address-handler/UserModalEditAddressHandler.vue'
import Ripple from 'vue-ripple-directive'
import { VueMaskFilter } from 'v-mask'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

export default {
  directives: {
    Ripple,
  },
  components: {
    BCard,
    BCardHeader,
    BCardBody,
    BBadge,
    BButton,

    //Modal
    userModalEditAddressHandler,

  },
  props: {
    userData: {
      type: Object,
      required: true,
    },
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      console.log(modalName)
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  setup(props, {emit}) {

    const toast = useToast()

    const blankUser = {
      id: '',
      street: '',
      number: '',
      address_complement: '',
      district: '',
      city: '',
      state: '',
      country: '',
    }
    const user = ref(JSON.parse(JSON.stringify(blankUser)))
    //Limpa os dados do popup
    const clearUserData = () => {
      user.value = JSON.parse(JSON.stringify(blankUser))
    }

    //Atualiza os dados do contato
    const updateUserAddress = userData => {
      store.dispatch('app-user/updateUserAddress',  userData )
        .then(() => {
          emit('get-user')  

          toast({
            component: ToastificationContent,
            props: {
              title: 'Usuário atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const loading = ref(false)
    
    //Busca o endereço com base no CEP digitado
    const getAddressUser = cep => {
      //Se foi digitado todos os caracteres do CEP
      if(cep.length == 9) {
        //Mostra o spinner
        loading.value = true

        store.dispatch('app-user/getAddressUser', { cep: cep })
        .then(response => {
          //Se não houve erro ao buscar o endereço
          if(!response.data.error) { 
            user.value.street = response.data.address.logradouro 
            user.value.district = response.data.address.bairro 
            //userData.value.address_complement = response.data.complemento 
            user.value.city = response.data.address.localidade 
            user.value.state = response.data.state 
            user.value.country = response.data.country
            console.log(user)

          }
          else {
            props.userData.addresses[0].add_street = null 
            props.userData.addresses[0].add_number = null 
            props.userData.addresses[0].add_district = null 
            //addressLocal.value.address_complement = response.data.complemento 
            props.userData.addresses[0].add_city = null 
            props.userData.addresses[0].state = null 
            props.userData.addresses[0].country = null
            
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

    //Cria um atributo na prop com o número do telefone sem o DDI
    const setUserData = () => {
      user.value.id = props.userData.addresses[0].id 
      user.value.cep = props.userData.addresses[0].add_zip_code 
      user.value.street = props.userData.addresses[0].add_street 
      user.value.number = props.userData.addresses[0].add_number 
      user.value.address_complement = props.userData.addresses[0].add_address_complement 
      user.value.district = props.userData.addresses[0].add_district 
      user.value.city = props.userData.addresses[0].add_city
      user.value.state = props.userData.addresses[0].state
      user.value.country = props.userData.addresses[0].country
      console.log(user)
    }  

    return {
      clearUserData,
      updateUserAddress,
      getAddressUser,
      setUserData,

      loading,
      user,

    }
  }
}
</script>

<style>

</style>
