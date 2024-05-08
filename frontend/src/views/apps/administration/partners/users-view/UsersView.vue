<template>
  <div>

    <!-- Alert: No item found -->
    <b-alert
      variant="danger"
      :show="userData === undefined"
    >
      <h4 class="alert-heading">
        Error fetching user data
      </h4>
      <div class="alert-body">
        No user found with this user id. Check
        <b-link
          class="alert-link"
          :to="{ name: 'apps-users-list'}"
        >
          User List
        </b-link>
        for other users.
      </div>
    </b-alert>
    <b-button
      variant="dark"
      class="btn-icon rounded-circle mb-1"
      v-b-tooltip.hover.v-secondary
      v-b-modal.modal-add-channel
      :title="$t('user.back')"
      :to="{ name: 'apps-management-users' }"
    >
      <feather-icon 
        icon="ArrowLeftIcon" 
        size="16"
      />
    </b-button>
    <template v-if="userData">
      <!-- First Row -->
      <b-row>
        <b-col
          cols="12"
          xl="9"
          lg="8"
          md="7"
        >
          <user-view-user-info-card 
            :user-data="userData"
            :services-data="servicesData"
            @get-user="getUser"
          />
        </b-col>
        <b-col
          cols="12"
          md="5"
          xl="3"
          lg="4"
        >
          <user-view-user-address-card 
            :user-data="userData"
            @get-user="getUser"
            style="height: 220px"
          />
        </b-col>
      </b-row>

      <b-row>
        <b-col
          cols="12"
          lg="9"
          class="scroll-col"
        >
          <user-view-user-timeline-card 
            :services-data="servicesData"
            :hidden-button-service="hiddenButtonService"
            @load-services="fetchServices"
            class="h-100"
          />
        </b-col>
        <b-col
          cols="12"
          lg="3"
        >
          <user-view-user-access-detail-card 
            :user-data="userData"
            @get-user="getUser"
            class="h-100"  
          />
        </b-col>
      </b-row>
    </template>

  </div>
</template>

<script>
import store from '@/store'
import router from '@/router'
import { ref, onUnmounted } from '@vue/composition-api'
import {
  BRow, BCol, BAlert, BLink, BButton, VBTooltip,
} from 'bootstrap-vue'
import userStoreModule from '../partnerStoreModule'
import UserViewUserInfoCard from './UserViewUserInfoCard.vue'
import UserViewUserAddressCard from './UserViewUserAddressCard.vue'
import UserViewUserTimelineCard from './UserViewUserTimelineCard.vue'
import UserViewUserAccessDetailCard from './UserViewUserAccessDetailCard.vue'

export default {
  components: {
    BRow,
    BCol,
    BAlert,
    BLink,
    BButton,

    // Local Components
    UserViewUserInfoCard,
    UserViewUserAddressCard,
    UserViewUserTimelineCard,
    UserViewUserAccessDetailCard,

  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  setup() {
    const userData = ref(null)
    const servicesData = ref([])
    //Mostra ou não o botão exibir mais atendimentos
    const hiddenButtonService = ref(false)

    const USER_APP_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    //Traz os dados do contato
    const getUser = () => {
    store.dispatch('app-user/fetchUser', { id: router.currentRoute.params.id })
      .then(response => { userData.value = response.data })
      .catch(error => {
        if (error.response.status === 404) {
          userData.value = undefined
        }
      })
    }

    getUser()

    //Traz os atendimentos associados a um chat de um contato de pouco em pouco, de acordo com o clique do usuário
    const fetchServices = offset => {
      store.dispatch('app-user/fetchServicesContact', { id: router.currentRoute.params.id, offset: offset } )
        .then(response => {
          //Se existem atendimentos para ser exibidos
          if(response.data.length > 0) {
            if(offset == 0) {
            servicesData.value = response.data
            }
            else {
              //Insere cada novo atendimento carregado no array de serviços
              response.data.map(function(service, key) {
                servicesData.value.push(service)
              });
              console.log(response.data)
            }
          }
          else {
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonService.value = true
          }
        })
    }

    fetchServices(0)

    return {
      userData,
      hiddenButtonService,
      servicesData,

      getUser,
      fetchServices,
      
      
    }
  },
}
</script>

<style>
  .scroll-col {
    max-height: 350px; 
    overflow-y: scroll;
  }
</style>
