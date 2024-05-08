<template>

  <div>
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('blacklist.filter') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <div class="d-flex align-items-center justify-content-end">
          <b-form-input
            v-model="searchQuery"
            class="d-inline-block mr-1"
            :placeholder="$t('blacklist.searchPlaceholder')"
          />
          <b-button
            variant="dark"
            v-b-modal.modal-add-contact-blacklist
          >
            <span class="text-nowrap">{{ $t('blacklist.addContactBlacklist') }}</span>
          </b-button>
        </div>
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
        :items="fetchBlacklist"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        :empty-text="$t('blacklist.noBlacklistFound')"
        :sort-desc.sync="isSortDirDesc"
      >

        
        <template #cell(con_name)="data">
          <div class="text-nowrap">
            <span class="align-text-top"><strong> {{ data.item.con_name }} </strong></span>
          </div>
        </template>

        
        <template #cell(name)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.name }}</span>
          </div>
        </template>

        <template #cell(created_at)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ formatDateTimeOnlyNumber(data.item.created_at) }}</span>
          </div>
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
    <!-- Form para cadastro de um novo canal -->
    <b-modal
      id="modal-add-contact-blacklist"
      :title="$t('blacklist.addContactBlacklist')"
      hide-footer
      size="lg"
    >
      <blacklist-modal-add-contact-handler
        :contactBlacklist="blacklistData"
        @add-contact-blacklist="addContactBlacklist"
        @hide-modal="hideModal"
      />
    </b-modal>
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
import { avatarText, formatDateTimeOnlyNumber } from '@core/utils/filter'
import useBlacklist from './useBlacklist'
import blacklistStoreModule from './blacklistStoreModule'
import BlacklistModalAddContactHandler from './blacklist-modal-add-contact-handler/BlacklistModalAddContactHandler.vue'
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

    vSelect,
    BlacklistModalAddContactHandler,
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
    const BLACKLIST_APP_STORE_MODULE_NAME = 'app-blacklist'

    // Register module
    if (!store.hasModule(BLACKLIST_APP_STORE_MODULE_NAME)) store.registerModule(BLACKLIST_APP_STORE_MODULE_NAME, blacklistStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(BLACKLIST_APP_STORE_MODULE_NAME)) store.unregisterModule(BLACKLIST_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()

    const blankBlacklist = {
      contact: [],
    }
    const blacklistData = ref(JSON.parse(JSON.stringify(blankBlacklist)))
    

    //Adiciona um departmento
    const addContactBlacklist = blacklistData => {
      store.dispatch('app-blacklist/addContactBlacklist', { blacklistData: blacklistData })
        .then(() => {  
          refetchData()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Departamento adicionado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Remove um departamento
    const removeContactBlacklist = id => {
      store.dispatch('app-blacklist/removeContactBlacklist', { id: id })
        .then(() => {
          refetchData()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Contato removido da blacklist com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const isAddNewUserSidebarActive = ref(false)

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ]

    const {
      fetchBlacklist,
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

      // UI
      resolveDepartmentStatusVariant,

    } = useBlacklist()

    fetchBlacklist()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchBlacklist,
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
      blacklistData,
      addContactBlacklist,
      removeContactBlacklist,

      // Filter
      avatarText,

      // UI
      resolveDepartmentStatusVariant,

      statusOptions,
      formatDateTimeOnlyNumber,

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
