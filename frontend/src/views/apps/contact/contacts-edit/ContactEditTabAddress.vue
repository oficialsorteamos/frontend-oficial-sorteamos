<template>
  <!-- Need to add height inherit because Vue 2 don't support multiple root ele -->
  <div style="height: inherit">
    <b-button
      variant="primary"
      class="btn-icon rounded-circle mb-1"
      @click="isTaskHandlerSidebarActive= true;"
      v-b-tooltip.hover.v-secondary
      :title="$t('contacts.contactEditTabAddress.addNewAddress')"
    >
      <feather-icon 
        icon="PlusIcon" 
        size="20"
      />
    </b-button>
    <b-card
      no-body
      v-for="address in addresses"
      :key="address.id"
      style="margin-bottom: 5px; cursor: pointer;"
      @click="handleAddressClick(address)"
    >
      <b-card-body>
        <b-row>
          <b-col
            cols="12"
            xl="2"
            lg="12"
            md="12"
          >
            <div 
               class="title-wrapper" 
            >
              <span class="h6">{{ $t('contacts.contactEditTabAddress.zipCode') }}: </span>
              <span style="font-weight: bold">{{ address.cep | VMask('#####-####') }}</span>
            </div>
          </b-col>
          <b-col
            cols="12"
            xl="3"
            lg="12"
            md="12"
          >
            <div 
              class="title-wrapper"
            >
              <span class="h6">{{ $t('contacts.contactEditTabAddress.street') }}: </span>
              <span>{{ address.street }} </span>
              <span v-if="address.number">, nº  {{ address.number }} </span>
              <span v-else>, S/N </span>
            </div>
          </b-col>
          <b-col
            cols="12"
            xl="3"
            lg="12"
            md="12"
          >
            <div 
              class="title-wrapper"
            >
              <span class="h6">{{ $t('contacts.contactEditTabAddress.complement') }}: </span>
              <span>{{ address.address_complement }}</span>
            </div>
          </b-col>
          <b-col
            cols="12"
            xl="2"
            lg="12"
            md="12"
          >
            <div 
              class="title-wrapper"
            >
              <span class="h6">{{ $t('contacts.contactEditTabAddress.district') }}: </span>
              <span>{{ address.district }}</span>
            </div>
          </b-col>
          <b-col
            cols="12"
            xl="2"
            lg="12"
            md="12"
          >
            <div 
              class="title-wrapper"
            >
              <span class="h6">{{ $t('contacts.contactEditTabAddress.city') }}: </span>
              <span>{{ address.city }}</span>
            </div>
          </b-col>
        </b-row>
        <b-row 
          class="mt-1"
        >
          <b-col
            cols="12"
            xl="2"
            lg="12"
            md="12"
          >
            <div 
              class="title-wrapper"
            >
              <span class="h6">{{ $t('contacts.contactEditTabAddress.state') }}: </span>
              <span>{{ address.state.sta_name }}</span>
            </div>
          </b-col>
          <b-col
            cols="12"
            xl="2"
            lg="12"
            md="12"
          >
            <div 
              class="title-wrapper"
            >
              <span class="h6">{{ $t('contacts.contactEditTabAddress.country') }}: </span>
              <span>{{ address.country.cou_name }}</span>
            </div>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>
    <div
      class="no-results text-center"
      :class="{'hidden': addresses.length > 0}"
    >
      <h5>{{ $t('contacts.contactEditTabAddress.noAddressesFound') }}</h5>
    </div>
    <!-- Address Handler -->
    <contact-address-handler-sidebar
      v-model="isTaskHandlerSidebarActive"
      :address="address"
      :user-id="user.id"
      :clear-address-data="clearAddressData"
      @remove-task="removeAddress"
      @add-address="addAddress"
      @update-address="updateAddress"
    />
  </div>
</template>

<script>
import store from '@/store'
import {
  ref, watch, computed, onUnmounted,
} from '@vue/composition-api'
import {
  BFormInput, BInputGroup, BInputGroupPrepend, BDropdown, BDropdownItem,
  BFormCheckbox, BBadge, BAvatar, BCard, BTable, BRow, BCol, BModal, BForm, BButton, VBTooltip, BCardBody
} from 'bootstrap-vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import draggable from 'vuedraggable'
import { formatDate, avatarText } from '@core/utils/filter'
import { useRouter } from '@core/utils/utils'
import { useResponsiveAppLeftSidebarVisibility } from '@core/comp-functions/ui/app'
import TodoLeftSidebar from './TodoLeftSidebar.vue'
import userStoreModule from '../contactStoreModule'
import ContactAddressHandlerSidebar from './contact-address-handler-sidebar/ContactAddressHandlerSidebar.vue'
import { VueMaskFilter } from 'v-mask'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

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
    draggable,
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
    TodoLeftSidebar,
    ContactAddressHandlerSidebar,

    VueMaskFilter,
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

    const { route, router } = useRouter()
    const routeSortBy = computed(() => route.value.query.sort)
    const routeQuery = computed(() => route.value.query.q)
    const routeParams = computed(() => route.value.params)
    watch(routeParams, () => {
      // eslint-disable-next-line no-use-before-define
      //fetchTasks()
    })

    //Toast Notification
    const toast = useToast()

    const addresses = ref([])

    const sortOptions = [
      'latest',
      'title-asc',
      'title-desc',
      'assignee',
      'due-date',
    ]
    const sortBy = ref(routeSortBy.value)
    watch(routeSortBy, val => {
      if (sortOptions.includes(val)) sortBy.value = val
      else sortBy.value = val
    })
    const resetSortAndNavigate = () => {
      const currentRouteQuery = JSON.parse(JSON.stringify(route.value.query))

      delete currentRouteQuery.sort

      router.replace({ name: route.name, query: currentRouteQuery }).catch(() => {})
    }

    const blankAddress = {
      cep: '',
      street: '',
      number: '',
      district: '',
      address_complement: '',
      city: '',
      state: '',
      country: '',
      userId: '',
      typeUserId: '',
    }
    const address = ref(JSON.parse(JSON.stringify(blankAddress)))
    const clearAddressData = () => {
      address.value = JSON.parse(JSON.stringify(blankAddress))
    }

    const addAddress = addressData => {
      store.dispatch('app-contact/addAddress', addressData)
        .then(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Address added successfully!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
          fetchAddresses()
        })
    }
    const removeAddress = () => {
      store.dispatch('app-contact/removeAddress', { id: address.value.id })
        .then(() => {
          fetchAddresses()
        })
    }
    const updateAddress = addressData => {
      store.dispatch('app-contact/updateAddress', { address: addressData })
        .then(() => {
          fetchAddresses()
        })
    }

    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    const isTaskHandlerSidebarActive = ref(false)
    

    const taskTags = [
      { title: 'Transferência', color: 'primary', route: { name: 'apps-chatbot-tag', params: { tag: 'team' } } },
      { title: 'Chamada de Bloco', color: 'success', route: { name: 'apps-chatbot-tag', params: { tag: 'low' } } },
      { title: 'Rastreamento', color: 'warning', route: { name: 'apps-chatbot-tag', params: { tag: 'medium' } } },
      /*{ title: 'High', color: 'danger', route: { name: 'apps-chatbot-tag', params: { tag: 'high' } } },
      { title: 'Update', color: 'info', route: { name: 'apps-chatbot-tag', params: { tag: 'update' } } },*/
    ]

    const resolveTagVariant = tag => {
      if (tag === 'team') return 'primary'
      if (tag === 'low') return 'success'
      if (tag === 'medium') return 'warning'
      if (tag === 'high') return 'danger'
      if (tag === 'update') return 'info'
      return 'primary'
    }

    const resolveAvatarVariant = tags => {
      if (tags.includes('high')) return 'primary'
      if (tags.includes('medium')) return 'warning'
      if (tags.includes('low')) return 'success'
      if (tags.includes('update')) return 'danger'
      if (tags.includes('team')) return 'info'
      return 'primary'
    }

    //Traz os endereços cadastrados associados ao contato
    const fetchAddresses = () => {
      store.dispatch('app-contact/fetchAddresses', {
        userId: props.user.id,
        typeUserId: 2, //Contato
      })
        .then(response => {
          console.log(response.data)
          addresses.value = response.data
        })
    }

    fetchAddresses()

    //Abre o sidebar já preenchida para atualização do bloco
    const handleAddressClick = addressData => {
      
      address.value = addressData
      address.value.cep =  VueMaskFilter(addressData.cep, '#####-####')
      isTaskHandlerSidebarActive.value = true

    }

    const { mqShallShowLeftSidebar } = useResponsiveAppLeftSidebarVisibility()

    return {
      address,
      addresses,
      removeAddress,
      fetchAddresses,
      addAddress,
      updateAddress,
      clearAddressData,
      taskTags,
      perfectScrollbarSettings,
      resetSortAndNavigate,

      // UI
      resolveTagVariant,
      resolveAvatarVariant,
      isTaskHandlerSidebarActive,

      // Click Handler
      handleAddressClick,

      // Filters
      formatDate,
      avatarText,

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
