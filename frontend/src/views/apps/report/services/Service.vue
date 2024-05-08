<template>

  <div>
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('reports.service.filter') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <b-row
          class="mt-1"
        >
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('reports.service.departments')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="departmentFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="departments"
                :getOptionLabel="departments => departments.dep_name"
                :reduce="departments => departments.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('reports.service.users')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="userFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="users"
                :getOptionLabel="users => users.name"
                :reduce="users => users.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('reports.service.channels')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="channelFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="channels"
                :getOptionLabel="channels => channels.cha_name"
                :reduce="channels => channels.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="2"
            md="2"
          >
            <b-form-group
              :label="$t('reports.service.origin')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="originFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="optionCampaign"
                :getOptionLabel="optionCampaign => optionCampaign.label"
                :reduce="optionCampaign => optionCampaign.code"
                transition=""
              />
            </b-form-group>
          </b-col>
        </b-row>
        <b-row
          class="mt-1"
        >
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('reports.service.contacts')"
              label-for="add-guests"
            >
              <v-select
                v-model="contactFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="contactsSearch"
                label="con_name"
                input-id="add-guests"
                @search="fuseSearch"
                :filterable="false"
              >

                <template #option="{ con_avatar, con_name }">
                  <b-avatar
                    size="sm"
                    :src="'../../'+con_avatar"
                  />
                  <span class="ml-50 align-middle"> {{ con_name }}</span>
                </template>

                <template #selected-option="{ con_avatar, con_name }">
                  <b-avatar
                    size="sm"
                    class="border border-white"
                    :src="'../../'+con_avatar"
                  />
                  <span class="ml-50 align-middle"> {{ con_name }}</span>
                </template>
              </v-select>
            </b-form-group>
          </b-col>
          <b-col
            lg="2"
            md="2"
          >
            <b-form-group
              :label="$t('reports.service.tags')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="tagFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                multiple
                :options="tags"
                :getOptionLabel="tags => tags.tag_name"
                :reduce="tags => tags.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="2"
            md="2"
          >
            <b-form-group
              :label="$t('reports.service.status')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="statusFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="status"
                :getOptionLabel="status => status.typ_description"
                :reduce="status => status.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            md="2"
            class="mb-1"
          >
            <!-- Language -->
            <b-form-group
              :label="$t('reports.service.servicesInitiated')"
              label-for="vue-select"
            >
              <flat-pickr
                v-model="periodFilter"
                class="form-control"
                :config="{
                            mode: 'range',
                            wrap: true, // set wrap to true only when using 'input-group'
                            altFormat: 'd/m/Y',
                            altInput: true,
                            dateFormat: 'Y-m-d',
                            locale: Portuguese, // locale for this instance only          
                        }"
              />
            </b-form-group>
          </b-col>
          <b-col
            md="2"
            class="mb-1"
          >
            <!-- Language -->
            <b-form-group
              :label="$t('reports.service.userInteracted')"
              label-for="vue-select"
            >
              <flat-pickr
                v-model="userSystemInteractionFilter"
                class="form-control"
                :config="{
                            wrap: true, // set wrap to true only when using 'input-group'
                            altFormat: 'd/m/Y',
                            altInput: true,
                            dateFormat: 'Y-m-d',
                            locale: Portuguese, // locale for this instance only          
                        }"
              />
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col
            lg="2"
            md="2"
          >
            <b-button
            variant="primary"
            @click="clearFilter"
            style="margin-bottom: -15px"
          >
            <span class="text-nowrap">{{ $t('reports.service.clear') }}</span>
          </b-button>
          </b-col>
        </b-row>
      </b-card-body>
    </b-card>

    <!-- Table Container Card -->
    <b-card
      no-body
      class="mb-0"
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

      <!-- App Action Bar -->
      <div 
        class="d-flex justify-content-between pl-1 pr-1 pb-1"
        
      >
        <div class="action-left">
        </div>
        <!-- Só mostra o botão de remover contatos se houver algum contato -->
        <div
          v-show="servicesData.length"
          class="align-items-center"
        >
          <feather-icon
            icon="DownloadIcon"
            v-b-tooltip.hover.v-secondary
            :title="$t('reports.service.downloadServices')"
            size="20"
            class="cursor-pointer ml-1"
            @click="downloadServicesReport"
            v-if="dataMeta.of <= 10000"
            
          />
          <feather-icon
            icon="DownloadIcon"
            v-b-tooltip.hover.v-secondary
            :title="$t('reports.service.downloadLimitMessage')"
            size="20"
            class="ml-1"
            v-else
            stroke="#FF9F43"
          />
        </div>
      </div>
      
      <b-table
        ref="refUserListTable"
        class="position-relative"
        :items="fetchServices"
        responsive
        :fields="tableColumns"
        primary-key="ser_protocol_number"
        :sort-by.sync="sortBy"
        show-empty
        :empty-text="$t('reports.service.noServicesFound')"
        :sort-desc.sync="isSortDirDesc"
      >
        
        <template #cell(con_name)="data">
          <b-media vertical-align="center">
            <template #aside>
              <b-avatar
                size="32"
                :src="data.item.con_avatar"
                :text="avatarText(data.item.con_name)"
                variant="light-primary"
              />
            </template>
            <span
              class="font-weight-bold d-block text-nowrap"
              v-if="data.item.con_name"
            >
              {{ data.item.con_name.length > 20? data.item.con_name.substring(0,20)+'...' : data.item.con_name }}
            </span>
            <span
              class="font-weight-bold d-block text-nowrap"
              v-else
            >
              -
            </span>
            <small class="text-muted">{{ data.item.con_phone | VMask(' +## (##) #####-####') }}</small>
          </b-media>
        </template>

        <template #cell(tags)="data">
          <div class="text-nowrap">
            <span
              v-for="tag in data.item.tags"
              :key="tag.id"
              html
            >
              <b-badge
                pill
                :style="'margin-bottom: 4px; background-color:'+tag.tag_color"
              >
                {{ tag.tag_name }}
              </b-badge>
              <br>
            </span>
          </div>
        </template>

        <template #cell(name)="data">
          <span
            class="font-weight-bold d-block text-nowrap"
            v-if="data.item.name"
          >
            {{ data.item.name.length > 20? data.item.name.substring(0,20)+'...' : data.item.name }}
          </span>
        </template>

        <template #cell(created_at)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ formatDateTimeOnlyNumber(data.item.created_at) }}</span>
          </div>
        </template>

        <template #cell(ser_dt_end_service)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ formatDateTimeOnlyNumber(data.item.ser_dt_end_service) }}</span>
          </div>
        </template> 

        <template #cell(cha_name)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.cha_name }}</span>
          </div>
        </template>

        <template #cell(type_status_service_id)="data">
          <b-badge
            pill
            :variant="resolveServiceStatusVariant(data.item.type_status_service_id)"
            class="text-capitalize"
          >

            {{ data.item.typ_description }}
          </b-badge>
        </template>
        
        <!-- Atendimento associado a campanha ou não -->
        <template #cell(cam_name)="data">
          <div class="text-nowrap">
              <b-badge
                pill
                :variant="data.item.cam_name == null? 'primary' : 'success'"
              >
                {{ data.item.cam_name == null? $t('reports.service.no') : $t('reports.service.yes') }}
              </b-badge>
          </div>
        </template>
        
        <!-- Ações -->
        <template #cell(actions)="row">
          <a :href="row.item.inv_url_invoice" target="_blank" rel="noopener noreferrer">
            <feather-icon
              icon="MessageSquareIcon"
              size="16"
              class="cursor-pointer"
              v-if="row"
              v-b-tooltip.hover.v-secondary
              :title="$t('reports.service.seeConversation')"
              @click="openModal('modal-chat-'+row.item.id)"
            />
          </a>
          <a :href="row.item.inv_url_invoice" target="_blank" rel="noopener noreferrer">
            <feather-icon
              icon="ListIcon"
              size="16"
              class="cursor-pointer ml-1"
              v-if="row"
              v-b-tooltip.hover.v-secondary
              :title="$t('reports.service.timeline')"
              @click="fetchServicesContact(row.item.contactId, row.item.id, 0); openModal('modal-timeline-'+row.item.id)"
            />
          </a>
          <!-- Modal com o chat do atendimento -->
          <b-modal
            :id="'modal-chat-'+row.item.id"
            title="Chat"
            hide-footer
            size="lg"
          >
            <report-modal-card-chat-service 
              :contact-id="row.item.contactId"
              :service-id="row.item.id"
              :base-url-storage="baseUrlStorage"
            />
          </b-modal>
          <!-- Modal com o chat do atendimento -->
          <b-modal
            :id="'modal-timeline-'+row.item.id"
            :title="$t('reports.service.timeline')"
            hide-footer
            size="lg"
          >
            <report-modal-timeline-service
              :services-data="servicesTimelineData"
              :hidden-button-service="hiddenButtonServiceTimeline"
              @load-services="fetchServices"
              class="h-100"
            />
          </b-modal>
        </template>
      </b-table>
      



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
              :total-rows="totalUsers"
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
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink, BFormGroup,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader, VBTooltip, BTooltip, BFormCheckbox,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import axios from '@axios'
import { ref, onUnmounted, computed } from '@vue/composition-api'
import { avatarText, formatDateTimeOnlyNumber } from '@core/utils/filter'
import useServicesList from './useServicesList'
import useContactsList from './useContactsList'
import serviceStoreModule from './serviceStoreModule'
import ReportModalCardChatService from '../report-modal-card-chat-service/ReportModalCardChatService.vue'
import ReportModalTimelineService from '../report-modal-timeline-service/ReportModalTimelineService.vue'
import { VueMaskFilter } from 'v-mask'
import router from '@/router'
import Swal from 'sweetalert2'
import flatPickr from 'vue-flatpickr-component'
import {Portuguese} from 'flatpickr/dist/l10n/pt.js';
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

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
    VBTooltip,
    BTooltip,
    BFormCheckbox,
    BFormGroup,

    vSelect,
    flatPickr,
    ReportModalCardChatService,
    ReportModalTimelineService,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      blocId: null,
      departments: [],
      users: [],
      channels: [],
      optionCampaign: [{label: 'Campanha', code: 'C'}, {label: 'Sem Campanha', code: 'S'}, {label: 'URA Reversa', code: 'U'}],
      status: [],
      tags: [],
      baseUrlStorage: '',
    }
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  created() { 
    //Traz os departamentos cadastrados
    axios
      .get('/api/management/department/fetch-departments/')
      .then(response => {
        //console.log(response.data)
        this.departments = response.data.departments
      })
    
    //Traz os usuários cadastrados
    axios
      .get('/api/management/user/get-operators/')
      .then(response => {
        //console.log(response.data)
        this.users = response.data.users
      })
    
    //Traz os canais cadastrados
    axios
      .get('/api/management/channel/fetch-channels/')
      .then(response => {
        //console.log(response.data)
        this.channels = response.data.channels
      })

    //Traz os status de atendimento cadastrados
    axios
      .get('/api/service/fetch-status-services/')
      .then(response => {
        //console.log(response.data)
        this.status = response.data.statusServices
      })

      //Traz as tags de de contatos
    axios
      .get('/api/management/tag/fetch-tags-type/1')
      .then(response => {
        //console.log(response.data)
        this.tags = response.data.tags
      })
    
    axios
      .get('/api/chat/get-base-url-storage/')
      .then(response => {
        //console.log(response.data)
        this.baseUrlStorage = response.data.baseUrlStorage
      })
  },
  setup() {
      const SERVICE_APP_STORE_MODULE_NAME = 'app-service'

    // Register module
    if (!store.hasModule(SERVICE_APP_STORE_MODULE_NAME)) store.registerModule(SERVICE_APP_STORE_MODULE_NAME, serviceStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(SERVICE_APP_STORE_MODULE_NAME)) store.unregisterModule(SERVICE_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()


    const {
      fetchServices,
      fetchServicesContact,
      downloadServicesReport,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      servicesData,
      departmentFilter,
      userFilter,
      channelFilter,
      originFilter,
      statusFilter,
      tagFilter,
      periodFilter,
      userSystemInteractionFilter,
      contactFilter,
      hiddenButtonServiceTimeline,
      servicesTimelineData,
      clearFilter,

      // UI
      resolveServiceStatusVariant,

    } = useServicesList()

    fetchServices()

    const {
      fetchContacts,
      searchQuerySelect,
      contactsSearch,
    } = useContactsList()
    fetchContacts()
    const fuseSearch = (search, loading) => {
      searchQuerySelect.value = search

      return null
    }
    
    return {
      fetchServices,
      fetchServicesContact,
      downloadServicesReport,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      servicesData,
      departmentFilter,
      userFilter,
      channelFilter,
      originFilter,
      statusFilter,
      periodFilter,
      userSystemInteractionFilter,
      contactFilter,
      tagFilter,
      hiddenButtonServiceTimeline,
      servicesTimelineData,
      clearFilter,
      Portuguese,

      contactsSearch,
      fuseSearch,

      // Filter
      avatarText,

      // UI
      resolveServiceStatusVariant,

      downloadServicesReport,
      formatDateTimeOnlyNumber,

    }
  },
}
</script>

<style lang="scss" scoped>
.per-page-selector {
  width: 90px;
}

/** or use this class: table thead th.bTableThStyle { ... } */
  .table .bTableThStyle {
    max-width: 12rem !important;
    text-overflow: ellipsis !important;
  }
  .table .selectColumn {
    max-width: 2rem !important;
  }

</style>

<style lang="scss">

@import '@core/scss/vue/libs/vue-select.scss';
</style>

