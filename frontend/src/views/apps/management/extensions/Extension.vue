<template>

  <div>
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('extension.filter') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <div class="d-flex align-items-center justify-content-end">
          <b-form-input
            v-model="searchQuery"
            class="d-inline-block mr-1"
            :placeholder="$t('extension.searchPlaceholder')"
          />
          <b-button
            variant="dark"
            v-b-modal.modal-add-extension
            v-if="$can('menu_administration_partner', 'administration')"
          >
            <span class="text-nowrap">{{ $t('extension.addExtension') }}</span>
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
          </b-col>
        </b-row>

      </div>
      
      <b-table
        ref="refUserListTable"
        class="position-relative"
        :items="fetchExtensions"
        responsive
        :fields="tableColumns"
        primary-key="id"
        :sort-by.sync="sortBy"
        show-empty
        :empty-text="$t('extension.noExtensionFound')"
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

        <template #cell(extension_name)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.extension_name }}</span>
          </div>
        </template>

        <template #cell(users)="data">
          <div class="text-nowrap">
            <span
              v-for="user in data.item.users"
              :key="user.id"
            >
              <b-badge 
                variant="primary"
                  class="mr-1"
                >
                {{user.name}}
              </b-badge>
            </span>
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
              @click="openModal('modal-edit-extension-'+data.item.id)"
            >
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">{{ $t('extension.edit') }}</span>
            </b-dropdown-item>
            <b-dropdown-item 
              @click="removeExtension(data.item.id)"
              v-if="$can('menu_administration_partner', 'administration')" 
            >
              <feather-icon icon="TrashIcon"/>
              <span class="align-middle ml-50">{{ $t('extension.delete') }}</span>
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
          <!-- Form para cadastro de um novo canal -->
          <b-modal
            :id="'modal-edit-extension-'+data.item.id"
            :title="$t('extension.editExtension')"
            hide-footer
            size="lg"
          >
            <extension-modal-handler
              :extension="data.item"
              :clear-extension-data="clearExtensionData"
              @add-extension="addExtension"
              @update-extension="updateExtension"
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
      id="modal-add-extension"
      :title="$t('extension.addExtension')"
      hide-footer
      size="lg"
    >
      <extension-modal-handler
        :extension="extensionData"
        :clear-extension-data="clearExtensionData"
        @add-extension="addExtension"
        @hide-modal="hideModal"
      />
    </b-modal>
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
import useExtension from './useExtension'
import extensionStoreModule from './extensionStoreModule'
import ExtensionModalHandler from './extension-modal-handler/ExtensionModalHandler.vue'
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

    vSelect,
    ExtensionModalHandler,
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
    const EXTENSION_APP_STORE_MODULE_NAME = 'app-extension'

    // Register module
    if (!store.hasModule(EXTENSION_APP_STORE_MODULE_NAME)) store.registerModule(EXTENSION_APP_STORE_MODULE_NAME, extensionStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(EXTENSION_APP_STORE_MODULE_NAME)) store.unregisterModule(EXTENSION_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()

    const blankExtension = {
      name: '',
      description: '',
      secret: '',
      voip: {
        id: null,
      }
    }
    const extensionData = ref(JSON.parse(JSON.stringify(blankExtension)))
    //Limpa os dados do popup
    const clearExtensionData = () => {
      extensionData.value = JSON.parse(JSON.stringify(blankExtension))
    }
    

    //Adiciona um ramal
    const addExtension = extensionData => {
      store.dispatch('app-extension/addExtension', { extensionData })
        .then(response => {
          if(response.data.errorMessage == '') {
            refetchData()
            toast({
              component: ToastificationContent,
              props: {
                title: 'Ramal adicionado com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }
          else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Não foi possível adicionar o ramal!',
                text: response.data.errorMessage,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
        })
    }

    //Atualiza os dados do departamento
    const updateExtension = extensionData => {
      store.dispatch('app-extension/updateExtension',  extensionData)
        .then(response => {
          refetchData()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Ramal atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Remove um ramal
    const removeExtension = id => {
      store.dispatch('app-extension/removeExtension', { id: id })
        .then(() => {
          refetchData()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Ramal removido com sucesso!',
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
      fetchExtensions,
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

    } = useExtension()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchExtensions,
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
      extensionData,
      clearExtensionData,
      addExtension,
      updateExtension,
      removeExtension,

      formatDateTimeOnlyNumber,

      // Filter
      avatarText,

      // UI
      resolveDepartmentStatusVariant,

      statusOptions,
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
