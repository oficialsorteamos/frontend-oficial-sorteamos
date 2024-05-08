<template>
  <div>
    <!--
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('chatbot.filters') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <div class="d-flex align-items-center justify-content-end">
          <b-form-input
            v-model="searchQuery"
            class="d-inline-block mr-1"
            :placeholder="$t('campaign.search')"
          />
          <b-button
            variant="primary"
            v-b-modal.modal-add-chatbot
          >
            <span class="text-nowrap">
              {{ $t('chatbot.addChatbot') }}
            </span>
          </b-button>
        </div>
        <b-row
          class="mt-1"
        >
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('chatbot.typeChatbots')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="typeChatbotFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="typeChatbots"
                :getOptionLabel="typeChatbots => typeChatbots.typ_description"
                :reduce="typeChatbots => typeChatbots.id"
                transition=""
              />
            </b-form-group>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>
    -->
    <!-- Table Container Card -->
    <b-card
      no-body
      class="mb-0"
      :style="skin == 'light'? 'background-color: #F5F5F5' : ''"
    >

      <div class="m-2">

        <!-- Table Top -->
        <b-row>

          <!-- Per Page -->
          <b-col
            cols="12"
            md="6"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <label>{{ $t('campaign.show') }}</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('campaign.entries') }}</label>
          </b-col>

          <!-- Search -->
          <b-col
            cols="12"
            md="6"
          >
          </b-col>
        </b-row>

      </div>
      <!-- Contatos cadastrados -->
      <b-row
        class="p-1"
        v-if="voips.length > 0"
      >
        <b-col
          lg="3"
          md="5"
          v-for="voip in voips"
          :key="voip.id"
          ref="refUserListTable"
        >
          <voip-card-handler 
            :voip="voip"
            :fetch-chatbots="fetchVoips"
          />
        </b-col>
      </b-row>
      <b-row
        class="p-1"
        v-else
      >
      <span
        class="w-100 text-center bg-white pt-2 pb-2"
      >
        {{ $t('voip.noIntegrationFound') }}
      </span>
      </b-row>
      <div class="mx-2 mb-2">
        <b-row>

          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-start"
          >
            <span class="text-muted">{{ $t('campaign.showing') }} {{ dataMeta.from }} {{ $t('campaign.to') }} {{ dataMeta.to }} {{ $t('campaign.of') }} {{ dataMeta.of }} {{ $t('campaign.entries') }}</span>
          </b-col>
          <!-- Pagination -->
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-end"
          >

            <b-pagination
              v-model="currentPage"
              :total-rows="totalVoips"
              :per-page="perPage"
              first-number
              last-number
              class="mb-0 mt-1 mt-sm-0"
              prev-class="prev-item"
              next-class="next-item"
            >
              <template #prev-text>
                <feather-icon
                  icon="ChevronLeftIcon"
                  size="18"
                />
              </template>
              <template #next-text>
                <feather-icon
                  icon="ChevronRightIcon"
                  size="18"
                />
              </template>
            </b-pagination>

          </b-col>

        </b-row>
      </div>
    </b-card>
  </div>
</template>

<script>
import {
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader, BFormGroup,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import axios from '@axios'
import useAppConfig from '@core/app-config/useAppConfig'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText } from '@core/utils/filter'
import useVoip from './useVoip'
import voipStoreModule from './voipStoreModule'
import VoipCardHandler from './voip-card-handler/VoipCardHandler.vue'

export default {
  components: {

    BCard,
    BRow,
    BCol,
    BFormInput,
    BButton,
    BTable,
    BMedia,
    BAvatar,
    BLink,
    BBadge,
    BDropdown,
    BDropdownItem,
    BPagination,
    BCardBody,
    BCardHeader,
    BFormGroup,

    VoipCardHandler,

    vSelect,
  },
  data() {
    return {
      typeChatbots: [],
    }
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
  },
  created() { 
    //Traz os departamentos cadastrados
    axios
      .get('/api/chatbot/fetch-type-chatbots/')
      .then(response => {
        //console.log(response.data.departments)
        this.typeChatbots = response.data.typeChatbots
      });
  },
  setup() {
    const VOIP_APP_STORE_MODULE_NAME = 'app-voip'

    // Register module
    if (!store.hasModule(VOIP_APP_STORE_MODULE_NAME)) store.registerModule(VOIP_APP_STORE_MODULE_NAME, voipStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(VOIP_APP_STORE_MODULE_NAME)) store.unregisterModule(VOIP_APP_STORE_MODULE_NAME)
    })

    const isAddNewUserSidebarActive = ref(false)
    const { skin } = useAppConfig()

    const roleOptions = [
      { label: 'Admin', value: 'admin' },
      { label: 'Author', value: 'author' },
      { label: 'Editor', value: 'editor' },
      { label: 'Maintainer', value: 'maintainer' },
      { label: 'Subscriber', value: 'subscriber' },
    ]

    const planOptions = [
      { label: 'Basic', value: 'basic' },
      { label: 'Company', value: 'company' },
      { label: 'Enterprise', value: 'enterprise' },
      { label: 'Team', value: 'team' },
    ]

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ]

    const blankVoip = {
      voi_name: '',
      voi_description: '',
      setting: {
        voi_user: '',
        voi_secret: '',
      }
    }
    const voipData = ref(JSON.parse(JSON.stringify(blankVoip)))
    //Limpa os dados do popup
    const clearChatbotData = () => {
      voipData.value = JSON.parse(JSON.stringify(blankVoip))
    }

    const {
      fetchVoips,
      addChatbot,
      tableColumns,
      perPage,
      currentPage,
      totalVoips,
      dataMeta,
      perPageOptions,
      refUserListTable,
      refetchData,
      voips,

      // UI
      resolveUserRoleVariant,
      resolveUserRoleIcon,
      resolveUserStatusVariant,

    } = useVoip()

    fetchVoips()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchVoips,
      addChatbot,
      clearChatbotData,
      tableColumns,
      perPage,
      currentPage,
      totalVoips,
      dataMeta,
      perPageOptions,
      refUserListTable,
      refetchData,
      voips,

      voipData,

      // Filter
      avatarText,

      // UI
      resolveUserRoleVariant,
      resolveUserRoleIcon,
      resolveUserStatusVariant,

      roleOptions,
      planOptions,
      statusOptions,

      skin,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
