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
            v-b-modal.modal-send-notification
          >
            <span class="text-nowrap">{{ $t('administrationNotification.sendNotification') }}</span>
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
              :label="$t('administrationNotification.company')"
              label-for="add-guests"
            >
              <v-select
                v-model="companyFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="companiesSearch"
                label="com_name"
                input-id="add-guests"
                @search="fuseSearch"
                :filterable="false"
              >

                <template #option="{com_name }">
                  <span class="ml-50 align-middle"> {{ com_name }}</span>
                </template>

                <template #selected-option="{com_name }">
                  <span class="ml-50 align-middle"> {{ com_name }}</span>
                </template>
              </v-select>
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
        :items="notifications"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        :empty-text="$t('user.noUserFound')"
        :sort-desc.sync="isSortDirDesc"
      >
        
        <!-- Título -->
        <template #cell(not_title)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.not_title }}</span>
          </div>
        </template>

        <!-- Mensagem -->
        <template #cell(not_message)="data">
          <div 
            class="text-nowrap"
            :id="'not_message'+data.index" 
          >
            <span class="align-text-top" style="white-space: pre-wrap" v-if="data.item.not_message" v-html="data.item.not_message.substring(0,50)+'...'">
            </span>
          </div>
          <b-tooltip :target="'not_message'+data.index" v-if="data.item.not_message"><span v-html="data.item.not_message"> </span> </b-tooltip>
        </template>

        <template #cell(type_users)="data">
          <span 
            class="text-nowrap"
            style="margin-right: 5px"
            v-for="typeUser in data.item.type_users"
            :key="typeUser.id"
          >
            <b-badge
              pill
              :variant="`${resolveTypeUserVariant(typeUser.id)}`"
              class="text-capitalize"
            >
              {{ typeUser.rol_name }}
            </b-badge>
          </span>
        </template>

        <!-- Total Empresas Notificadas -->
        <template #cell(companies)="data">
          <div class="text-nowrap">
            <span class="align-text-top"><span class="cursor-pointer" @click="openModal('modal-list-companies-'+data.item.id)">{{ data.item.companies.length }} Empresa(s)</span></span>
          </div>

          <!-- Form para cadastro de um novo usuário -->
          <b-modal
            :id="'modal-list-companies-'+data.item.id"
            :title="$t('administrationNotification.sendNotification')"
            hide-footer
            size="lg"
          >
            <notification-list-companies-modal-handler
              :notificationId="data.item.id"
              :clear-contact-data="clearDepartmentData"
              @send-notification="sendNotification"
              @hide-modal="hideModal"
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
              :total-rows="totalNotifications"
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
    <!-- Form para cadastro de um novo usuário -->
    <b-modal
      id="modal-send-notification"
      :title="$t('administrationNotification.sendNotification')"
      hide-footer
      size="lg"
    >
      <notification-modal-handler
        :notification="notificationData"
        :clear-contact-data="clearDepartmentData"
        @send-notification="sendNotification"
        @hide-modal="hideModal"
      />
    </b-modal>
  </div>
</template>

<script>
import {
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink, BFormGroup,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader, BTooltip,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText, formatDateOnlyNumber } from '@core/utils/filter'
import useNotificationsList from './useNotificationsList'
import notificationStoreModule from './notificationStoreModule'
import NotificationModalHandler from './notification-modal-handler/NotificationModalHandler.vue'
import NotificationListCompaniesModalHandler from './notification-list-companies-modal-handler/NotificationListCompaniesModalHandler.vue'
import useCompaniesList from './useCompaniesList'
import flatPickr from 'vue-flatpickr-component'
import {Portuguese} from 'flatpickr/dist/l10n/pt.js';
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
    flatPickr,
    NotificationModalHandler,
    NotificationListCompaniesModalHandler,
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
  setup() {
    const NOTIFICATION_APP_STORE_MODULE_NAME = 'app-notification'

    // Register module
    if (!store.hasModule(NOTIFICATION_APP_STORE_MODULE_NAME)) store.registerModule(NOTIFICATION_APP_STORE_MODULE_NAME, notificationStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(NOTIFICATION_APP_STORE_MODULE_NAME)) store.unregisterModule(NOTIFICATION_APP_STORE_MODULE_NAME)
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
    const sendNotification = notificationData => {
      store.dispatch('app-notification/sendNotification', notificationData)
      .then(() => {  
        refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Notificação enviada com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
    }

    const removeUser = userId => {
      Swal.fire({
        title: 'Remover Usuário',
        text: "Você tem certeza que deseja remover esse usuário?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        customClass: {
          confirmButton: 'btn btn-primary',
          cancelButton: 'btn btn-outline-danger ml-1',
        },
        buttonsStyling: false,
      }).then(result => {
            //Caso o usuário queira que o contato avalie o atendimento 
            if (result.value) {
              store.dispatch('app-company/removeUser', { id: userId })
                .then(() => {
                  refetchData()
                  toast({
                    component: ToastificationContent,
                    props: {
                      title: 'Usuário removido com sucesso!',
                      icon: 'CheckIcon',
                      variant: 'success',
                    },
                  })
                })
            } 
          })
    }

    const isAddNewUserSidebarActive = ref(false)

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ]

    const {
      fetchNotifications,
      notifications,
      tableColumns,
      perPage,
      currentPage,
      totalNotifications,
      dataMeta,
      perPageOptions,
      searchQuery,
      companyFilter,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,

      // UI
      resolveDepartmentStatusVariant,
      resolveUserRoleVariant,
      resolveTypeUserVariant,

    } = useNotificationsList()

    fetchNotifications()

    const {
      fetchCompanies,
      searchQuerySelect,
      companiesSearch,
    } = useCompaniesList()

    fetchCompanies()

    const fuseSearch = (search, loading) => {
      searchQuerySelect.value = search

      return null
    }
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchNotifications,
      notifications,
      tableColumns,
      perPage,
      currentPage,
      totalNotifications,
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
      sendNotification,
      removeUser,
      fuseSearch,
      Portuguese,
      companiesSearch,

      // Filter
      avatarText,

      // UI
      resolveDepartmentStatusVariant,
      resolveUserRoleVariant,
      resolveTypeUserVariant,

      statusOptions,

      formatDateOnlyNumber,

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
