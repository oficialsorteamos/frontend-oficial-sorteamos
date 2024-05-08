<template>

  <div>
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('user.filter') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <div class="d-flex align-items-center justify-content-end">
          <b-form-input
            v-model="searchQuery"
            class="d-inline-block mr-1"
            :placeholder="$t('administrationNotification.search')"
          />
          <b-button
            variant="dark"
            @click="updateInstances()"
          >
            <span class="text-nowrap">{{ $t('instance.update') }}</span>
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
              :label="$t('instance.apis')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="apiFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="apis"
                :getOptionLabel="apis => apis.api_name"
                :reduce="apis => apis.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="2"
            md="2"
          >
            <b-form-group
              :label="$t('instance.instanceStatus')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="instanceStatusFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="status"
                :getOptionLabel="status => status.ins_description"
                :reduce="status => status.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="2"
            md="2"
          >
            <b-form-group
              :label="$t('instance.connectionStatus')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="instanceConnectionStatusFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="connectionStatus"
                :getOptionLabel="connectionStatus => connectionStatus.ins_description"
                :reduce="connectionStatus => connectionStatus.id"
                transition=""
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
            cols="6"
            md="6"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <label>{{ $t('user.show') }}</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('user.entries') }}</label>
          </b-col>
        </b-row>
      </div>
      <!-- Contatos cadastrados -->
      <!--
      <b-row
        class="p-1"
      >
        <b-col
          lg="3"
          md="5"
          v-for="contact in contacts"
          :key="contact.id"
          ref="refUserListTable"
        >
          <card-advance-profile 
            :contact="contact"
          />
        </b-col>
      </b-row>
      -->

      
      <b-table
        ref="refUserListTable"
        class="position-relative"
        :items="instances"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        :empty-text="$t('user.noUserFound')"
        :sort-desc.sync="isSortDirDesc"
      >
        
        <!-- Título -->
        <template #cell(ins_name)="data">
          <div class="font-weight-bold">
            <span class="align-text-top">{{ data.item.ins_name }}</span>
          </div>
        </template>

        <template #cell(ins_token)="data">
          <span class="align-text-top">{{ data.item.ins_token.length > 40? data.item.ins_token.substring(0,40)+'...' : data.item.ins_token }}</span>
        </template>

        <template #cell(ins_dt_created)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ formatDateTimeOnlyNumber(data.item.ins_dt_created) }}</span>
          </div>
        </template>

        <template #cell(status)="data">
          <b-badge
            pill
            :variant="resolveInstanceStatusVariant(data.item.status.id)"
            class="text-capitalize"
          >

            {{ data.item.status.ins_description }}
          </b-badge>
        </template>

        <template #cell(connection_status)="data">
          <b-badge
            pill
            :variant="resolveConnectionStatusVariant(data.item.connection_status.id)"
            class="text-capitalize"
          >

            {{ data.item.connection_status.ins_description }}
          </b-badge>
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
              @click="disconnectInstance(data.item)"
            >
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">{{ $t('department.edit') }}</span>
            </b-dropdown-item>
            
            <b-dropdown-item
              @click="removeInstance(data.item)"
            >
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">Delete</span>
            </b-dropdown-item>
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
            <span class="text-muted">{{ $t('user.showing') }} {{ dataMeta.from }} {{ $t('user.to') }} {{ dataMeta.to }} {{ $t('user.of') }} {{ dataMeta.of }} {{ $t('user.entries') }}</span>
          </b-col>
          <!-- Pagination -->
          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-end"
          >

            <b-pagination
              v-model="currentPage"
              :total-rows="totalInstances"
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
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader, BTooltip,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import axios from '@axios'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText, formatDateTimeOnlyNumber } from '@core/utils/filter'
import useInstancesList from './useInstancesList'
import instanceStoreModule from './instanceStoreModule'
import { VueMaskFilter } from 'v-mask'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)
import Swal from 'sweetalert2'
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
    BFormGroup,
    BTooltip,

    vSelect,
  },
  data() {
    return {
      status: [],
      connectionStatus: [],
      apis: [],
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
    //Traz os status de uma instância
    axios
      .get('/api/administration/instance/get-instance-status/')
      .then(response => {
        //console.log(response.data)
        this.status = response.data
      })

    //Traz os status de conexão de uma instância
    axios
      .get('/api/administration/instance/get-instance-connection-status/')
      .then(response => {
        //console.log(response.data)
        this.connectionStatus = response.data
      })

    //Traz as API'S ativas
    axios
      .get('/api/administration/instance/get-apis-by-official/0')
      .then(response => {
        //console.log(response.data)
        this.apis = response.data
      })
  },
  setup() {
    const INSTANCE_APP_STORE_MODULE_NAME = 'app-instance'

    // Register module
    if (!store.hasModule(INSTANCE_APP_STORE_MODULE_NAME)) store.registerModule(INSTANCE_APP_STORE_MODULE_NAME, instanceStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(INSTANCE_APP_STORE_MODULE_NAME)) store.unregisterModule(INSTANCE_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()

    const blankNotification = {
      companies: '',
      typeUsers: '',
      not_title: '',
      not_message: '',
    }
    const notificationData = ref(JSON.parse(JSON.stringify(blankNotification)))
    //Limpa os dados do popup
    const clearDepartmentData = () => {
      notificationData.value = JSON.parse(JSON.stringify(blankNotification))
    }

    //Adiciona uma empresa
    const updateInstances = () => {
      Vue.prototype.$isLoading(true)
      store.dispatch('app-instance/updateInstances', )
      .then(() => {  
        refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Instâncias atualizadas com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
      .finally(() => {
        //Esconde a loading screen
        Vue.prototype.$isLoading(false) 
      })
    }

    const disconnectInstance = instanceData => {
      Vue.prototype.$isLoading(true)
      store.dispatch('app-instance/disconnectInstance', instanceData )
      .then(() => {  
        refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Instância desconectada com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
      .finally(() => {
        //Esconde a loading screen
        Vue.prototype.$isLoading(false) 
      })
    }

    const removeInstance = instanceData => {
      Vue.prototype.$isLoading(true)
      store.dispatch('app-instance/removeInstance', instanceData )
      .then(() => {  
        refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Instância removida com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
      .finally(() => {
        //Esconde a loading screen
        Vue.prototype.$isLoading(false) 
      })
    }

    const isAddNewUserSidebarActive = ref(false)

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ]

    const {
      fetchInstances,
      instances,
      tableColumns,
      perPage,
      currentPage,
      totalInstances,
      dataMeta,
      perPageOptions,
      searchQuery,
      companyFilter,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      instanceStatusFilter,
      instanceConnectionStatusFilter,
      apiFilter,

      // UI
      resolveInstanceStatusVariant,
      resolveConnectionStatusVariant,

    } = useInstancesList()

    fetchInstances()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchInstances,
      instances,
      tableColumns,
      perPage,
      currentPage,
      totalInstances,
      dataMeta,
      perPageOptions,
      searchQuery,
      companyFilter,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      clearDepartmentData,
      notificationData,
      updateInstances,
      instanceStatusFilter,
      instanceConnectionStatusFilter,
      apiFilter,

      // Filter
      avatarText,

      // UI
      resolveInstanceStatusVariant,
      resolveConnectionStatusVariant,

      statusOptions,

      formatDateTimeOnlyNumber,
      disconnectInstance,
      removeInstance,

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
