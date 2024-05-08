<template>
  <component :is="userData === undefined ? 'div' : 'b-card'"
    style="background-color: #f6f6f6;"
  >

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
      :title="$t('contacts.contactsEdit.back')"
      :to="{ name: 'apps-contacts-list' }"
    >
      <feather-icon 
        icon="ArrowLeftIcon" 
        size="16"
      />
    </b-button>
    <b-tabs
      v-if="userData"
      pills
    >
      <!-- Tab: Information -->
      <b-tab active>
        <template #title>
          <feather-icon
            icon="InfoIcon"
            size="16"
            class="mr-0 mr-sm-50"
          />
          <span class="d-none d-sm-inline">{{ $t('contacts.contactsEdit.information') }}</span>
        </template>
        <!--
        <user-edit-tab-information class="mt-2 pt-75" />
        -->
        <!-- First Row -->
        <b-row>
          <b-col
            cols="12"
            xl="4"
            lg="8"
            md="7"
          >
            <contact-view-user-info-card 
              :user-data="userData"
              @get-contact="getContact"
              class="h-100"
            />
          </b-col>
          <b-col
            cols="12"
            xl="8"
            lg="8"
            md="7"
            class="scroll-col"
          >
            <contact-view-user-timeline-card 
              :services-data="servicesData"
              :hidden-button-service="hiddenButtonService"
              @load-services="fetchServices"
              class="h-100"
            />
          </b-col>
        </b-row>
      </b-tab>

      <!-- Tab: Addresses -->
      <b-tab>
        <template #title>
          <feather-icon
            icon="GlobeIcon"
            size="16"
            class="mr-0 mr-sm-50"
          />
          <span class="d-none d-sm-inline">{{ $t('contacts.contactsEdit.addresses') }} &nbsp;</span>
          <b-badge
            pill
            variant="primary"
          >
            {{ userData.amountAddresses }}
          </b-badge>
        </template>
        <contact-edit-tab-address 
          class="mt-2 pt-75" 
          :user="userData"
        />
      </b-tab>

      <!-- Tab: Social -->
      <b-tab>
        <template #title>
          <feather-icon
            icon="Share2Icon"
            size="16"
            class="mr-0 mr-sm-50"
          />
          <span class="d-none d-sm-inline">{{ $t('contacts.contactsEdit.social') }} &nbsp;</span>
          <b-badge
            pill
            variant="primary"
          >
            {{ userData.amountSocialNetworks }}
          </b-badge>
        </template>
        <contact-edit-tab-social-network 
          class="mt-2 pt-75" 
          :user="userData"
        />
      </b-tab>
    </b-tabs>
  </component>
</template>

<script>
import {
  BTab, BTabs, BCard, BAlert, BLink, BRow, BCol, BBadge, BButton, VBTooltip,
} from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import router from '@/router'
import store from '@/store'
import userStoreModule from '../contactStoreModule'
import UserEditTabAccount from './UserEditTabAccount.vue'
import ContactEditTabInformation from './ContactEditTabInformation.vue'
import ContactEditTabSocialNetwork from './ContactEditTabSocialNetwork.vue'
import ContactEditTabAddress from './ContactEditTabAddress.vue'
import ContactViewUserInfoCard from './ContactViewUserInfoCard.vue'
import ContactViewUserTimelineCard from './ContactViewUserTimelineCard.vue'

export default {
  components: {
    BTab,
    BTabs,
    BCard,
    BAlert,
    BLink,
    BRow,
    BCol,
    BBadge,
    BButton,


    UserEditTabAccount,
    ContactEditTabInformation,
    ContactEditTabSocialNetwork,
    ContactEditTabAddress,

    ContactViewUserInfoCard,
    ContactViewUserTimelineCard,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  setup() {
    const userData = ref(null)
    const servicesData = ref([])
    //Mostra ou não o botão exibir mais atendimentos
    const hiddenButtonService = ref(false)

    const USER_APP_STORE_MODULE_NAME = 'app-contact'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    //Traz os dados do contato
    const getContact = () => {
      store.dispatch('app-contact/fetchContact', { id: router.currentRoute.params.id })
        .then(response => { userData.value = response.data })
        .catch(error => {
          if (error.response.status === 404) {
            userData.value = undefined
          }
        })
    }
    getContact()
    
    //Traz os atendimentos associados a um chat de um contato de pouco em pouco, de acordo com o clique do usuário 
    const fetchServices = offset => {
      store.dispatch('app-contact/fetchServicesContact', { id: router.currentRoute.params.id, offset: offset } )
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
      servicesData,

      fetchServices,
      hiddenButtonService,

      getContact,
    }
  },
}
</script>

<style>
  .scroll-col {
    max-height: 500px; 
    overflow-y: scroll;
  }
</style>
