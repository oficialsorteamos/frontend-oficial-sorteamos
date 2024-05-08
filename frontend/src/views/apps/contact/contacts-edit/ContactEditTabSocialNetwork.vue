<template>
  <!-- Need to add height inherit because Vue 2 don't support multiple root ele -->
  <div style="height: inherit">
    <b-button
      variant="primary"
      class="btn-icon rounded-circle mb-1"
      @click="isTaskHandlerSidebarActive= true;"
      v-b-tooltip.hover.v-secondary
      :title="$t('contacts.contactEditTabSocialNetwork.addNewSocialNetwork')"
    >
      <feather-icon 
        icon="PlusIcon" 
        size="20"
      />
    </b-button>
    <b-card
      no-body
      v-for="socialNetwork in socialNetworks"
      :key="socialNetwork.id"
      style="margin-bottom: 5px; cursor: pointer;"
      @click="handleSocialNetworkClick(socialNetwork)"
    >
      <b-card-body>
        <b-row>
          <b-col
            cols="12"
            xl="3"
            lg="12"
            md="12"
          >
            <div 
              class="title-wrapper"
            >
              <span class="h6">{{ $t('contacts.contactEditTabSocialNetwork.type') }}: </span>
              <span>{{ socialNetwork.type_social_network.typ_name }} </span>
            </div>
          </b-col>
          <b-col
            cols="12"
            xl="6"
            lg="12"
            md="12"
          >
            <div 
              class="title-wrapper"
            >
              <span class="h6">{{ $t('contacts.contactEditTabSocialNetwork.url') }}: </span>
              <a 
                :href="socialNetwork.url"
                target="_blank"
                > 
              <span>{{ socialNetwork.url }}</span>
              </a>
            </div>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>
    <div
      class="no-results text-center"
      :class="{'hidden': socialNetworks.length > 0}"
    >
      <h5>{{ $t('contacts.contactEditTabSocialNetwork.noSocialNetworksFound') }}</h5>
    </div>
    <!-- Task Handler -->
    <contact-social-network-handler-sidebar
      v-model="isTaskHandlerSidebarActive"
      :social="social"
      :user-id="user.id"
      :clear-social-network-data="clearSocialNetworkData"
      @remove-social-network="removeSocialNetwork"
      @add-social-network="addSocialNetwork"
      @update-social-network="updateSocialNetwork"
    />
  </div>
</template>

<script>
import store from '@/store'
import {
  ref, onUnmounted,
} from '@vue/composition-api'
import {
  BFormInput, BInputGroup, BInputGroupPrepend, BDropdown, BDropdownItem,
  BFormCheckbox, BBadge, BAvatar, BCard, BTable, BRow, BCol, BModal, BForm, BButton, VBTooltip, BCardBody
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { useResponsiveAppLeftSidebarVisibility } from '@core/comp-functions/ui/app'
import userStoreModule from '../contactStoreModule'
import ContactSocialNetworkHandlerSidebar from './contact-social-network-handler-sidebar/ContactSocialNetworkHandlerSidebar.vue'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'


export default {
  components: {
    BFormInput,
    BInputGroup,
    BInputGroupPrepend,
    BDropdown,
    BDropdownItem,
    BFormCheckbox,
    BBadge,
    BAvatar,
    VuePerfectScrollbar,
    BCard, 
    BTable, 
    BRow, 
    BCol,
    BModal,
    BForm,
    BBadge,
    BButton,
    BCardBody,

    // App SFC
    ContactSocialNetworkHandlerSidebar,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    const CONTACT_APP_STORE_MODULE_NAME = 'app-contact'

    // Register module
    if (!store.hasModule(CONTACT_APP_STORE_MODULE_NAME)) store.registerModule(CONTACT_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(CONTACT_APP_STORE_MODULE_NAME)) store.unregisterModule(CONTACT_APP_STORE_MODULE_NAME)
    })

    //Toast Notification
    const toast = useToast()

    const socialNetworks = ref([])

    const blankSocial = {
      typeSocialNetwork: '',
      url: '',
    }
    const social = ref(JSON.parse(JSON.stringify(blankSocial)))
    const clearSocialNetworkData = () => {
      social.value = JSON.parse(JSON.stringify(blankSocial))
    }

    //Adiciona uma nova rede social
    const addSocialNetwork = socialData => {
      store.dispatch('app-contact/addSocialNetwork', socialData)
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Social Network added successfully!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          fetchSocialNetworks()
        })
    }
    //Remove uma rede social
    const removeSocialNetwork = () => {
      store.dispatch('app-contact/removeSocialNetwork', { id: social.value.id })
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Social Network removed successfully!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          fetchSocialNetworks()
        })
    }
    //Atualiza uma rede social
    const updateSocialNetwork = socialData => {
      store.dispatch('app-contact/updateSocialNetwork', { socialNetwork: socialData })
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Social Network updated successfully!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          fetchSocialNetworks()
        })
    }

    const isTaskHandlerSidebarActive = ref(false)

    //Traz os endereços cadastrados associados ao contato
    const fetchSocialNetworks = () => {
      store.dispatch('app-contact/fetchSocialNetworks', {
        userId: props.user.id,
        typeUserId: 2, //Contato
      })
        .then(response => {
          console.log(response.data)
          socialNetworks.value = response.data
        })
    }

    fetchSocialNetworks()

    //Abre o sidebar já preenchida para atualização dos dados
    const handleSocialNetworkClick = socialData => {
      social.value = socialData
      social.value.typeSocialNetwork = socialData.type_social_network
      isTaskHandlerSidebarActive.value = true

    }

    const { mqShallShowLeftSidebar } = useResponsiveAppLeftSidebarVisibility()

    return {
      social,
      socialNetworks,
      removeSocialNetwork,
      fetchSocialNetworks,
      addSocialNetwork,
      updateSocialNetwork,
      clearSocialNetworkData,

      // UI
      isTaskHandlerSidebarActive,

      // Click Handler
      handleSocialNetworkClick,

      // Left Sidebar Responsive
      mqShallShowLeftSidebar,
    }
  },
}
</script>

<style lang="scss" scoped>
.draggable-task-handle {
position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    visibility: hidden;
    cursor: move;

    .todo-task-list .todo-item:hover & {
      visibility: visible;
    }
}

.dropdown-item-action  {
  margin: 0% !important; 
  padding: 0% !important
}
</style>

<style lang="scss">
@import "~@core/scss/base/pages/app-chatbot.scss";
</style>
