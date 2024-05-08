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

      <b-table
        class="mb-0"
        :items="servicesData"
        responsive
        :fields="tableColumns"
        show-empty
        :empty-text="$t('reports.service.noServicesFound')"
      >
        <template #cell(company)="data">
          <div class="d-flex align-items-center">
            <!--
            <b-avatar
                rounded
                size="32"
                variant="light-company"
                v-if="data.index == 0"
              >
              <b-img
                :src="require('@/assets/images/illustration/badge.svg')"
                style="width: 16px"
                alt="avatar img"
                
              />
            </b-avatar>
            <b-avatar
                size="32"
                variant="light-company"
                v-else
                style="background-color: white;"
              >
            </b-avatar>
            -->
            <b-media>
              <template #aside>
                <b-avatar
                  size="32"
                  :src="data.item.avatar"
                  :text="avatarText(data.item.name)"
                  variant="light-info"
                />
              </template>
              <b-link
                :to="{ name: 'apps-management-users-view', params: { id: data.item.id } }"
                class="font-weight-bold d-block text-nowrap"
                v-b-tooltip.hover.v-secondary
                :title="data.item.name.length > 13? data.item.name : null"
              >
                {{ data.item.name.length > 13? data.item.name.substring(0, 13)+'...' : data.item.name }}
              </b-link>
              <div class="font-small-2 text-muted" style="text-align: left !important">
                <b-form-rating
                  id="rating-sm-no-border"
                  v-model="data.item.rating"
                  no-border
                  variant="warning"
                  inline
                  size="sm"
                  v-b-tooltip.hover.v-secondary
                  :title="data.item.rating"
                  readonly
                  style="margin-left: -15px; text-align: left !important"
                />
              </div>
            </b-media>
          </div>
        </template>
        <!-- views -->
        <template #cell(views)="data">
          <div class="d-flex flex-column">
            <span class="font-weight-bolder mb-25">{{ data.item.countServices }}</span>
          </div>
        </template>
        <!-- Atendimentos Avaliados -->
        <template #cell(totalServicesEvaluations)="data">
          <div class="d-flex flex-column">
            <span class="font-weight-bolder mb-25" v-if="data.item.totalServicesEvaluations">{{ data.item.totalServicesEvaluations }}</span>
            <span class="font-weight-bolder mb-25" v-else>0</span>
          </div>
        </template>
        <!-- Rating -->
        <template #cell(rating)="data">
          <div class="d-flex flex-column">
            <span class="font-weight-bolder mb-25" v-if="data.item.totalServicesEvaluations > 0">
              <b-badge variant="dark">
                {{ data.item.rating }}
              </b-badge>
            </span>
            <span class="font-weight-bolder mb-25" v-else> - </span>
          </div>
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
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink, BFormGroup, BImg,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader, VBTooltip, BTooltip, BFormCheckbox, BFormRating,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import axios from '@axios'
import { ref, onUnmounted, computed } from '@vue/composition-api'
import { avatarText, formatDateTimeOnlyNumber } from '@core/utils/filter'
import useOperatorEvaluationList from './useOperatorEvaluationList'
import operatorEvaluationStoreModule from './operatorEvaluationStoreModule'
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
    BFormRating,
    BImg,

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
      const OPERATOR_EVALUATION_APP_STORE_MODULE_NAME = 'app-operator-evaluation'

    // Register module
    if (!store.hasModule(OPERATOR_EVALUATION_APP_STORE_MODULE_NAME)) store.registerModule(OPERATOR_EVALUATION_APP_STORE_MODULE_NAME, operatorEvaluationStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(OPERATOR_EVALUATION_APP_STORE_MODULE_NAME)) store.unregisterModule(OPERATOR_EVALUATION_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()


    const {
      fetchOperatorEvaluation,
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

    } = useOperatorEvaluationList()

    fetchOperatorEvaluation()

    
    return {
      fetchOperatorEvaluation,
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

      // Filter
      avatarText,

      // UI
      resolveServiceStatusVariant,

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

