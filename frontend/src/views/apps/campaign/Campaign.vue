<template>

  <div>

    <user-list-add-new
      :is-add-new-user-sidebar-active.sync="isAddNewUserSidebarActive"
      :role-options="roleOptions"
      :plan-options="planOptions"
      @refetch-data="refetchData"
    />
    <b-card 
      no-body
      class="p-1"
    > 
      <span
        class="align-self-end mr-2"
      >
        <b-badge
          class="profile-badge"
          variant="light-success"
          style="font-size: 18px"
        >
          Crédito: {{balance}}
        </b-badge>
        <h5>
          
        </h5>
      </span>
      <b-card-header class="pb-50">
        <h5>
          {{ $t('campaign.filters') }}
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
            v-b-modal.modal-add-campaign
          >
            <span class="text-nowrap">{{ $t('campaign.addCampaign') }}</span>
          </b-button>
          <b-button
            class="ml-1"
            variant="dark"
            v-b-modal.modal-add-credit
          >
            <span class="text-nowrap">{{ $t('campaign.addCredit') }}</span>
          </b-button>
        </div>
      </b-card-body>
    </b-card>

    <!-- Table Container Card -->
    <b-card
      no-body
      class="mb-0"
      :style="skin != 'dark'? 'background-color: #F5F5F5' : ''"
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
            <!--
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                placeholder="Search..."
              />
              <b-button
                variant="primary"
                @click="isAddNewUserSidebarActive = true"
              >
                <span class="text-nowrap">Add User</span>
              </b-button>
            </div>
            -->
          </b-col>
        </b-row>

      </div>
      <!-- Contatos cadastrados -->
      <b-row
        class="p-1"
        v-if="campaigns.length > 0"
      >
        <b-col
          lg="4"
          md="5"
          v-for="campaign in campaigns"
          :key="campaign.id"
          ref="refUserListTable"
        >
          <card-advance-profile 
            :campaign="campaign"
            :fetch-campaigns="fetchCampaigns"
            :base-url-storage="baseUrlStorage"
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
        {{ $t('campaign.noCampaignFound') }}
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
              :total-rows="totalCampaigns"
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
      <!-- Form para cadastro de um novo canal -->
      <b-modal
        id="modal-add-campaign"
        :title="$t('campaign.newCampaign')"
        hide-footer
        size="lg"
      >
        <campaign-modal-new-campaign-handler
          :campaign="campaignData"
          :clear-campaign-data="clearCampaignData"
          @add-campaign="addCampaign"
          @hide-modal="hideModal"
        />
      </b-modal>
      <!-- Modal para adição de crédito para utilização da campanha -->
      <b-modal
        id="modal-add-credit"
        title="Add Credit"
        hide-footer
        size="lg"
      >
        <campaign-modal-add-credit-handler
          :payment="paymentData"
          :cards-data="cardsData"
          :clear-contact-data="clearPaymentData"
          :add-card="addCard"
          @add-credit="addCredit"
          @hide-modal="hideModal"
        />
      </b-modal>
    </b-card>
  </div>
</template>

<script>
import {
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText } from '@core/utils/filter'
import useAppConfig from '@core/app-config/useAppConfig'
import useCampaign from './useCampaign'
import campaignStoreModule from './campaignStoreModule'
import UserListAddNew from './UserListAddNew.vue'
import CardAdvanceProfile from './campaign-card-handler/CardAdvanceProfile.vue'
import CampaignModalNewCampaignHandler from './campaign-modal-new-campaign-handler/CampaignModalNewCampaignHandler.vue'
import CampaignModalAddCreditHandler from './campaign-modal-add-credit-handler/CampaignModalAddCreditHandler.vue'

export default {
  components: {
    UserListAddNew,

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

    CardAdvanceProfile,

    vSelect,

    CampaignModalNewCampaignHandler,
    CampaignModalAddCreditHandler,
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
  },
  setup() {
    const CAMPAIGN_APP_STORE_MODULE_NAME = 'app-campaign'

    // Register module
    if (!store.hasModule(CAMPAIGN_APP_STORE_MODULE_NAME)) store.registerModule(CAMPAIGN_APP_STORE_MODULE_NAME, campaignStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(CAMPAIGN_APP_STORE_MODULE_NAME)) store.unregisterModule(CAMPAIGN_APP_STORE_MODULE_NAME)
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

    const blankCampaign = {
      cam_name: '',
      cam_description: '',
    }
    const campaignData = ref(JSON.parse(JSON.stringify(blankCampaign)))
    //Limpa os dados do popup
    const clearCampaignData = () => {
      campaignData.value = JSON.parse(JSON.stringify(blankCampaign))
    }


    const blankPayment = {
      credit_value: '',
      payment_method: [],
      credit_card: null,
    }
    const paymentData = ref(JSON.parse(JSON.stringify(blankPayment)))
    //Limpa os dados do popup
    const clearPaymentData = () => {
      paymentData.value = JSON.parse(JSON.stringify(blankPayment))
    }

    const {
      fetchCampaigns,
      addCampaign,
      tableColumns,
      perPage,
      currentPage,
      totalCampaigns,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      campaigns,
      cardsData,
      balance,

      addCard,
      addCredit,

      baseUrlStorage,

      // UI
      resolveUserRoleVariant,
      resolveUserRoleIcon,
      resolveUserStatusVariant,

      // Extra Filters
      roleFilter,
      planFilter,
      statusFilter,
    } = useCampaign()

    fetchCampaigns()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchCampaigns,
      addCampaign,
      clearCampaignData,
      tableColumns,
      perPage,
      currentPage,
      totalCampaigns,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      campaigns,
      cardsData,
      balance,

      addCard,
      addCredit,

      baseUrlStorage,

      campaignData,

      // Filter
      avatarText,

      // UI
      resolveUserRoleVariant,
      resolveUserRoleIcon,
      resolveUserStatusVariant,

      roleOptions,
      planOptions,
      statusOptions,

      // Extra Filters
      roleFilter,
      planFilter,
      statusFilter,

      paymentData,
      clearPaymentData,

      skin,
    }
  },
}
</script>

<style lang="scss" scoped>
.per-page-selector {
  width: 90px;
}
</style>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
#btn-emoji-default {
  height: auto !important;
  width: 25px !important;
  margin: 0 !important;
}
#btn-emoji-default > div > img.emoji {
  width: 17px !important;
  height: 17px !important;
}
</style>
