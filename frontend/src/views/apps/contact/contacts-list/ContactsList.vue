<template>

  <div>

    <user-list-add-new
      :is-add-new-user-sidebar-active.sync="isAddNewUserSidebarActive"
      :role-options="roleOptions"
      :plan-options="planOptions"
      @refetch-data="refetchData"
    />

    <!-- Filters 
    <users-list-filters
      :role-filter.sync="roleFilter"
      :plan-filter.sync="planFilter"
      :status-filter.sync="statusFilter"
      :role-options="roleOptions"
      :plan-options="planOptions"
      :status-options="statusOptions"
    />
    -->
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('contacts.contactsList.filters') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <div class="d-flex align-items-center justify-content-end">
          <b-form-input
            v-model="searchQuery"
            class="d-inline-block mr-1"
            :placeholder="$t('contacts.contactsList.search')"
          />
          <b-button
            variant="primary"
            v-b-modal.modal-edit-contact
          >
            <span class="text-nowrap">{{ $t('contacts.contactsList.addUser') }}</span>
          </b-button>
        </div>
      </b-card-body>
    </b-card>

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
            <label>{{ $t('contacts.contactsList.show') }}</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('contacts.contactsList.entries') }}</label>
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
            :block-contact="blockContact"
            :unlock-contact="unlockContact"
            :base-url-storage="baseUrlStorage"
          />
        </b-col>
      </b-row>
      <!--
      <b-table
        ref="refUserListTable"
        class="position-relative"
        :items="fetchUsers"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        empty-text="No matching records found"
        :sort-desc.sync="isSortDirDesc"
      >

        
        <template #cell(user)="data">
          <b-media vertical-align="center">
            <template #aside>
              <b-avatar
                size="32"
                :src="data.item.avatar"
                :text="avatarText(data.item.fullName)"
                :variant="`light-${resolveUserRoleVariant(data.item.role)}`"
                :to="{ name: 'apps-users-view', params: { id: data.item.id } }"
              />
            </template>
            <b-link
              :to="{ name: 'apps-users-view', params: { id: data.item.id } }"
              class="font-weight-bold d-block text-nowrap"
            >
              {{ data.item.fullName }}
            </b-link>
            <small class="text-muted">@{{ data.item.username }}</small>
          </b-media>
        </template>

        
        <template #cell(role)="data">
          <div class="text-nowrap">
            <feather-icon
              :icon="resolveUserRoleIcon(data.item.role)"
              size="18"
              class="mr-50"
              :class="`text-${resolveUserRoleVariant(data.item.role)}`"
            />
            <span class="align-text-top text-capitalize">{{ data.item.role }}</span>
          </div>
        </template>

        
        <template #cell(status)="data">
          <b-badge
            pill
            :variant="`light-${resolveUserStatusVariant(data.item.status)}`"
            class="text-capitalize"
          >
            {{ data.item.status }}
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
            <b-dropdown-item :to="{ name: 'apps-users-view', params: { id: data.item.id } }">
              <feather-icon icon="FileTextIcon" />
              <span class="align-middle ml-50">Details</span>
            </b-dropdown-item>

            <b-dropdown-item :to="{ name: 'apps-users-edit', params: { id: data.item.id } }">
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">Edit</span>
            </b-dropdown-item>

            <b-dropdown-item>
              <feather-icon icon="TrashIcon" />
              <span class="align-middle ml-50">Delete</span>
            </b-dropdown-item>
          </b-dropdown>
        </template>

      </b-table>
      -->
      <div class="mx-2 mb-2">
        <b-row>

          <b-col
            cols="12"
            sm="6"
            class="d-flex align-items-center justify-content-center justify-content-sm-start"
          >
            <span class="text-muted">{{ $t('contacts.contactsList.showing') }} {{ dataMeta.from }} to {{ dataMeta.to }} of {{ dataMeta.of }} {{ $t('contacts.contactsList.entries') }}</span>
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
    <!-- Form para editar dados do contato -->
    <b-modal
      id="modal-edit-contact"
      :title="$t('contacts.contactViewUserInfoCard.addContact')"
      hide-footer
      size="sm"
    >
      <contact-modal-edit-contact-handler
        :contact="contactData"
        :clear-contact-data="clearContactData"
        @add-contact="addContact"
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
import axios from '@axios'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText } from '@core/utils/filter'
import useAppConfig from '@core/app-config/useAppConfig'
import UsersListFilters from './UsersListFilters.vue'
import useUsersList from './useUsersList'
import userStoreModule from '../contactStoreModule'
import UserListAddNew from './UserListAddNew.vue'
import CardAdvanceProfile from './contact-card-profile-handler/CardAdvanceProfile.vue'
import ContactModalEditContactHandler from '../contacts-edit/contact-modal-edit-contact-handler/ContactModalEditContactHandler.vue'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    UsersListFilters,
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
    ContactModalEditContactHandler,

    vSelect,
  },
  data() {
    return {
      baseUrlStorage: '',
    }
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
  },
  created() { 
    axios
      .get('/api/chat/get-base-url-storage/')
      .then(response => {
        //console.log(response.data)
        this.baseUrlStorage = response.data.baseUrlStorage
      })
  },
  setup() {
    const USER_APP_STORE_MODULE_NAME = 'app-contact'
    const { skin } = useAppConfig()

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()
    const isAddNewUserSidebarActive = ref(false)

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

    const blankContact = {
      'phoneNumber': '',
      'con_name': '',
      'email': '',
      'gender': [],
      'birthday': '',

    }
    const contactData = ref(JSON.parse(JSON.stringify(blankContact)))

    //Limpa os dados do popup
    const clearContactData = () => {
      contactData.value = JSON.parse(JSON.stringify(blankContact))
    }

    //Adiciona um novo contato
    const addContact = contactData => {
      store.dispatch('app-contact/addContact', contactData)
        .then(response => {
          fetchContacts()
          toast({
            component: ToastificationContent,
            props: {
              title: response.data.message? response.data.message : 'Contato adicionado com sucesso!',
              icon: 'CheckIcon',
              variant: response.data.message? 'danger' : 'success',
            },
          })
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
              title: 'Não foi possível salvar o contato',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
    }

    const {
      fetchContacts,
      blockContact,
      unlockContact,
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
      contacts,

      // UI
      resolveUserRoleVariant,
      resolveUserRoleIcon,
      resolveUserStatusVariant,

      // Extra Filters
      roleFilter,
      planFilter,
      statusFilter,
    } = useUsersList()

    fetchContacts()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchContacts,
      blockContact,
      unlockContact,
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
      contacts,
      contactData,

      clearContactData,
      addContact,

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
</style>
