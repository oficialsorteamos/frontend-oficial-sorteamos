<template>

  <div>
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('call.filter') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <b-row
          class="mt-1"
        >
          <!-- Contacts -->
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('call.contacts')"
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
          <!-- Users -->
          <b-col
            lg="3"
            md="3"
          >
            <b-form-group
              :label="$t('call.users')"
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
              :label="$t('call.extensions')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="extensionFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="extensions"
                :getOptionLabel="extensions => extensions.name"
                :reduce="extensions => extensions.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            md="3"
            class="mb-1"
          >
            <!-- Language -->
            <b-form-group
              :label="$t('call.callsInitiated')"
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
            <label>{{ $t('department.show') }}</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('department.entries') }}</label>
          </b-col>

          <!-- Search -->
          <b-col
            cols="12"
            md="6"
          >
          </b-col>
        </b-row>

      </div>
      
      <b-table
        ref="refUserListTable"
        class="position-relative"
        :items="fetchCalls"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        :empty-text="$t('call.noCallFound')"
        :sort-desc.sync="isSortDirDesc"
      >

        
        <template #cell(con_name)="data">
          <div class="text-nowrap">
            <span class="align-text-top"><strong> {{ data.item.con_name }} </strong></span>
          </div>
        </template>

        <template #cell(cal_phone_contact)="data">
          <div class="text-nowrap">
            <span class="align-text-top"><strong> {{ data.item.cal_phone_contact | VMask(' +## (##) #####-####') }} </strong></span>
          </div>
        </template>
        
        <template #cell(name)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.name }}</span>
          </div>
        </template>

        <template #cell(extension_name)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.extension_name }}</span>
          </div>
        </template>

        <template #cell(ser_protocol_number)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.ser_protocol_number }}</span>
          </div>
        </template>

        <template #cell(cal_call_date)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ formatDateTimeOnlyNumber(data.item.cal_call_date) }}</span>
          </div>
        </template>

        <template #cell(cal_record_name)="data">
          <audio controls>
                  <source :src="urlBaseStorage+`public/chats/chat${data.item.chat_id}/calls/${data.item.cal_record_name}.mp3`" >
                </audio>
        </template>

        
        <template #cell(actions)="data">
          <b-dropdown
            variant="link"
            no-caret
            :right="$store.state.appConfig.isRTL"
          >

            <template #button-content>
              <feather-icon
                icon="MoreVerticalIcon"
                size="16"
                class="align-middle text-body"
              />
            </template>

            <b-dropdown-item 
              @click="removeContactBlacklist(data.item.id)"
            >
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">{{ $t('blacklist.delete') }}</span>
            </b-dropdown-item>
            <!--
            <b-dropdown-item
              @click="removeDepartment(data.item.id)"
            >
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">Delete</span>
            </b-dropdown-item>
            -->
          </b-dropdown>
        </template>
      </b-table>
      
      <div class="mx-2 mb-2">
        <b-row>

          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-start"
          >
            <span class="text-muted">{{ $t('department.showing') }} {{ dataMeta.from }} {{ $t('department.to') }} {{ dataMeta.to }} {{ $t('department.of') }} {{ dataMeta.of }} {{ $t('department.entries') }}</span>
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
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader, BFormGroup,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import axios from '@axios'
import store from '@/store'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText, formatDateTimeOnlyNumber } from '@core/utils/filter'
import useCall from './useCall'
import useContactsList from './useContactsList'
import callStoreModule from './callStoreModule'
import flatPickr from 'vue-flatpickr-component'
import {Portuguese} from 'flatpickr/dist/l10n/pt.js'
import { VueMaskFilter } from 'v-mask'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

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

    vSelect,
    flatPickr,
  },
   data() {
    return {
      users: [],
      extensions: [],
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
    //Traz os usuários cadastrados
    axios
      .get('/api/management/user/get-operators/')
      .then(response => {
        //console.log(response.data)
        this.users = response.data.users
      })

    //Traz os ramais cadastrados
    axios
      .get('/api/management/extension/get-extensions/')
      .then(response => {
        //console.log(response.data)
        this.extensions = response.data.extensions
      })
  },
  setup() {
    const CALL_APP_STORE_MODULE_NAME = 'app-call'

    // Register module
    if (!store.hasModule(CALL_APP_STORE_MODULE_NAME)) store.registerModule(CALL_APP_STORE_MODULE_NAME, callStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(CALL_APP_STORE_MODULE_NAME)) store.unregisterModule(CALL_APP_STORE_MODULE_NAME)
    })

    const isAddNewUserSidebarActive = ref(false)

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ]

    const {
      fetchCalls,
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
      urlBaseStorage,
      userFilter,
      contactFilter,
      periodFilter,
      extensionFilter,

      // UI
      resolveDepartmentStatusVariant,

    } = useCall()

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

      // Sidebar
      isAddNewUserSidebarActive,

      fetchCalls,
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
      urlBaseStorage,
      userFilter,
      contactFilter,
      periodFilter,
      extensionFilter,

      // Filter
      avatarText,

      // UI
      resolveDepartmentStatusVariant,

      statusOptions,
      formatDateTimeOnlyNumber,

      Portuguese,
      contactsSearch,
      fuseSearch,

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
</style>
