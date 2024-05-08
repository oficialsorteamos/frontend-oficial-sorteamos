<template>

  <div>
    <b-button
      variant="dark"
      class="btn-icon rounded-circle mb-1"
      v-b-tooltip.hover.v-secondary
      :title="$t('campaign.contactsMailing.back')"
      :to="{ name: 'apps-campaign' }"
    >
      <feather-icon 
        icon="ArrowLeftIcon" 
        size="16"
      />
    </b-button>
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('campaign.contactsMailing.filter') }}
        </h5>
      </b-card-header>
      <b-card-body>
        
          <b-row
            class="mb-1"
          >
            <b-col
              lg="12"
              md="12"
            >
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                :placeholder="$t('campaign.contactsMailing.searchPlaceholder')"
              />
            </b-col>
          </b-row>
          <b-row>
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
                  :getOptionLabel="status => status.mai_description"
                  :reduce="status => status.id"
                  transition=""
                />
              </b-form-group>
            </b-col>
          </b-row>
          <!--
          <b-button
            variant="primary"
            v-b-modal.modal-add-department
          >
            <span class="text-nowrap">{{ $t('department.addDepartment') }}</span>
          </b-button>
          -->
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
        style="background-color: white;"
      >
        <div class="action-left">
          <b-form-checkbox
            :checked="selectAllEmailCheckbox"
            :indeterminate="isSelectAllEmailCheckboxIndeterminate"
            @change="selectAllCheckboxUpdate"
          >
            {{ $t('campaign.contactsMailing.selectAll') }}
          </b-form-checkbox>
        </div>
        <!-- Só mostra o botão de remover contatos se houver algum contato -->
        <div
          v-show="mailingData.length"
          class="align-items-center"
        >
          <feather-icon
            icon="FileTextIcon"
            v-b-tooltip.hover.v-secondary
            :title="$t('campaign.contactsMailing.downloadMailing')"
            size="20"
            class="cursor-pointer ml-1"
            @click="downloadMailing"
          />
          <feather-icon
            v-show="$route.params.folder !== 'trash'"
            icon="TrashIcon"
            v-b-tooltip.hover.v-secondary
            :title="selectedContactsMailing.length > 0? $t('campaign.contactsMailing.removeContacts') : $t('campaign.contactsMailing.removeAllContacts')"
            :stroke="selectedContactsMailing.length > 0? '#82868B' : '#EA5455'"
            size="20"
            class="cursor-pointer ml-1"
            @click="removeContactMailing('trash')"
          />
        </div>
      </div>
      
      <b-table
        ref="refUserListTable"
        class="position-relative"
        :items="fetchMailing"
        responsive
        :fields="tableColumns"
        primary-key="con_phone"
        :sort-by.sync="sortBy"
        show-empty
        :empty-text="$t('campaign.contactsMailing.noContactsFound')"
        :sort-desc.sync="isSortDirDesc"
      >
        <template #cell(id)="data">
          <div class="user-action">
            <!-- Apenas exibe o select caso o mailing tenha status de Aguardando o Envio -->
            <b-form-checkbox
              :checked="selectedContactsMailing.includes(data.item.id)"
              @change="toggleSelectedMail(data.item.id)"
              @click.native.stop
               v-if="data.item.status_id == 1"
            />
          </div>
        </template>
        
        <template #cell(con_name)="data">
          <div class="text-nowrap">
            <span 
              class="align-text-top text-capitalize"
              v-b-tooltip.hover.v-secondary
              :title="data.item.con_name"
            >
              <strong> {{ data.item.con_name.length > 25? data.item.con_name.substring(0,25)+'...' : data.item.con_name }} </strong>
            </span>
          </div>
        </template>

        
        <template #cell(con_phone)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.con_phone | VMask(' +## (##) #####-####') }}</span>
          </div>
        </template>

        <template #cell(mes_content)="data">
          <div 
            class="text-nowrap"
            :id="'mes_content'+data.index" 
          >
            <span class="align-text-top" style="white-space: pre-wrap" v-if="data.item.mes_content" v-html="data.item.mes_content.substring(0,50)+'...'">
            </span>
            <span class="align-text-top" style="white-space: pre-wrap" v-if="data.item.tem_body" v-html="data.item.tem_body.substring(0,50)+'...'">
            </span>
            <span class="align-text-top" style="white-space: pre-wrap" v-if="data.item.qui_content" v-html="data.item.qui_content.substring(0,50)+'...'">
            </span>
          </div>
          <b-tooltip :target="'mes_content'+data.index" v-if="data.item.mes_content"><span v-html="data.item.mes_content"> </span> </b-tooltip>
          <b-tooltip :target="'mes_content'+data.index" v-else-if="data.item.tem_body"><span v-html="data.item.tem_body"> </span> </b-tooltip>
          <b-tooltip :target="'mes_content'+data.index" v-else><span v-html="data.item.qui_content"> </span> </b-tooltip>
        </template>
        

        <template #cell(cha_name)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ data.item.cha_name }}</span>
          </div>
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
        
        <template #cell(mai_description)="data">
          <b-badge
            pill
            :variant="`light-${resolveDepartmentStatusVariant(data.item.mai_description)}`"
            class="text-capitalize"
          >
            {{ data.item.mai_description }}
          </b-badge>
        </template>

        <template #cell(mai_dt_sending)="data">
          <div class="text-nowrap">
            <span class="align-text-top">{{ formatDateTimeOnlyNumber(data.item.mai_dt_sending) }}</span>
          </div>
        </template>

        <!-- Contato retornou (respondeu) ou não -->
        <template #cell(mai_contact_returned)="data">
          <div class="text-nowrap">
              <b-badge
                pill
                :variant="data.item.mai_contact_returned == 0? 'danger' : 'success'"
              >
                {{ data.item.mai_contact_returned == 0? $t('campaign.contactsMailing.no') : $t('campaign.contactsMailing.yes') }}
              </b-badge>
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
              @click="openModal('modal-edit-department-'+data.item.id)"
            >
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">{{ $t('department.edit') }}</span>
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
import { avatarText } from '@core/utils/filter'
import useContactsMailingList from './useContactsMailingList'
import campaignStoreModule from '../../campaignStoreModule'
import { formatDateTimeOnlyNumber } from '@core/utils/filter'
import { VueMaskFilter } from 'v-mask'
import router from '@/router'
import Swal from 'sweetalert2'
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
    BFormGroup,
    BCardHeader,
    VBTooltip,
    BTooltip,
    BFormCheckbox,

    vSelect,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      status: [],
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
    //Traz os status de atendimento cadastrados
    axios
      .get('/api/campaign/fetch-status-mailng/')
      .then(response => {
        //console.log(response.data)
        this.status = response.data
      })
  },
  setup() {
      const CAMPAIGN_APP_STORE_MODULE_NAME = 'app-campaign'

    // Register module
    if (!store.hasModule(CAMPAIGN_APP_STORE_MODULE_NAME)) store.registerModule(CAMPAIGN_APP_STORE_MODULE_NAME, campaignStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(CAMPAIGN_APP_STORE_MODULE_NAME)) store.unregisterModule(CAMPAIGN_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()


    const isAddNewUserSidebarActive = ref(false)

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ]

    const {
      fetchMailing,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      statusFilter,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      mailingData,

      // UI
      resolveDepartmentStatusVariant,

    } = useContactsMailingList()

    fetchMailing()

    // ------------------------------------------------
    // Mailing Selection
    // ------------------------------------------------

    const selectedContactsMailing = ref([])
    const toggleSelectedMail = mailId => {
      const mailIndex = selectedContactsMailing.value.indexOf(mailId)

      if (mailIndex === -1) selectedContactsMailing.value.push(mailId)
      else selectedContactsMailing.value.splice(mailIndex, 1)
    }
    const selectAllEmailCheckbox = computed(() => mailingData.value.length && (mailingData.value.length === selectedContactsMailing.value.length))
    const isSelectAllEmailCheckboxIndeterminate = computed(() => Boolean(selectedContactsMailing.value.length) && mailingData.value.length !== selectedContactsMailing.value.length)
    const selectAllCheckboxUpdate = val => {
      selectedContactsMailing.value = val ? mailingData.value.map(mail => mail.id) : []
    }

      // ------------------------------------------------
    // Mailing Actions
    // ------------------------------------------------

    const removeContactMailing = () => {
      Swal.fire({
        title: selectedContactsMailing.value.length > 0? "Remover Contatos do Mailing" : "Remover TODOS os Contatos do Mailing",
        html: selectedContactsMailing.value.length > 0? "Você realmente quer remover esse(s) contato(s) do mailing?" : "Você realmente quer remover TODOS os contatos do mailing? <br> <spam style='font-size: 12px'>*<b>Somente os contatos que não houveram tentativa de envio serão removidos</b></spam>",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Cancelar',
        customClass: {
          confirmButton: 'btn btn-primary',
          cancelButton: 'btn btn-outline-danger ml-1',
        },
        buttonsStyling: false,
      }).then(result => {
        if (result.value) {
          store.dispatch('app-campaign/removeContactMailing', {
            contactIds: selectedContactsMailing.value,
            campaignId: router.currentRoute.params.id,
          })
          .then(() => {
            //Atualiza a lista de contatos do mailing
            refetchData()
            toast({
              component: ToastificationContent,
              props: {
                title: 'Contato(s) do mailing removidos com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }) //Após excluir os contatos, remove a seleção 
          .finally(() => { selectedContactsMailing.value = [] })
        }
      })
    }

    const downloadMailing = () => {
      //Chama a loading screen
      Vue.prototype.$isLoading(true)
      store.dispatch('app-campaign/downloadMailing', {
        campaignId: router.currentRoute.params.id,
        status: statusFilter.value,
      })
      .then(response => {
        console.log(response.data)
        const anchor = document.createElement("a")
        anchor.setAttribute("download", response.data.filename)
        anchor.setAttribute("href", response.data.linkData)
        document.body.appendChild(anchor)
        anchor.click();
        document.body.removeChild(anchor)
        //Atualiza a lista de contatos do mailing
        //refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Download do mailing realizado com sucesso!',
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
    
    return {
      // Sidebar
      isAddNewUserSidebarActive,

      fetchMailing,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      statusFilter,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      mailingData,

      // Filter
      avatarText,

      // UI
      resolveDepartmentStatusVariant,

      statusOptions,

      toggleSelectedMail,
      selectAllEmailCheckbox,
      isSelectAllEmailCheckboxIndeterminate,
      selectAllCheckboxUpdate,
      removeContactMailing,
      selectedContactsMailing,
      downloadMailing,
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

