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
        <b-row>
          <b-col
            lg="12"
            md="12"
          >
            <div class="d-flex align-items-center justify-content-end">
              <b-form-input
                v-model="searchQuery"
                class="d-inline-block mr-1"
                :placeholder="$t('user.search')"
              />
              <!-- Só exibe o botão de adicionar uma nova empresa caso não seja um ambiente de White Label -->
              <b-button
                variant="dark"
                v-b-modal.modal-add-company
                v-if="!isWhiteLabel"
              >
                <span class="text-nowrap">{{ $t('administrationCompany.addCompany') }}</span>
              </b-button>
            </div>
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
              :label="$t('administrationCompany.companyStatus')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="companyStatusFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="companyStatus"
                :getOptionLabel="companyStatus => companyStatus.com_description"
                :reduce="companyStatus => companyStatus.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="2"
            md="2"
            v-if="!isWhiteLabel"
          >
            <b-form-group
              :label="$t('administrationCompany.typePartner')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="typePartnerFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="typePartners"
                :getOptionLabel="typePartners => typePartners.typ_description"
                :reduce="typePartners => typePartners.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="3"
            md="3"
            v-if="!isWhiteLabel"
          >
            <b-form-group
              :label="$t('administrationCompany.partners')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="partnerFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="partners"
                :getOptionLabel="partners => partners.par_corporate_name"
                :reduce="partners => partners.id"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="2"
            md="2"
            v-if="!isWhiteLabel"
          >
            <b-form-group
              :label="$t('administrationCompany.overdueInvoice')+'?'"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="overdueInvoiceFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="overdueInvoiceOptions"
                :getOptionLabel="overdueInvoiceOptions => overdueInvoiceOptions.label"
                :reduce="overdueInvoiceOptions => overdueInvoiceOptions.code"
                transition=""
              />
            </b-form-group>
          </b-col>
          <b-col
            lg="2"
            md="2"
          >
            <b-form-group
              :label="$t('administrationCompany.contractsExpiringIn')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="daysDueContractFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="daysDueContractOptions"
                :getOptionLabel="daysDueContractOptions => daysDueContractOptions.label"
                :reduce="daysDueContractOptions => daysDueContractOptions.code"
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
      :style="skin != 'dark'? 'background-color: #F5F5F5' : ''"
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

      <!-- Empresas cadastrados -->
      <b-row
        class="p-1"
        v-if="companies.length > 0"
      >
        <b-col
          lg="4"
          md="5"
          v-for="company in companies"
          :key="company.id"
          ref="refUserListTable"
        >
          <company-card 
            :company="company"
            :is-white-label="isWhiteLabel"
            :fetch-companies="fetchCompanies"
            @open-modal="openModal"
          />

          <b-modal
            :id="'modal-edit-company-'+company.id"
            :title="$t('administrationCompany.addCompany')"
            hide-footer
            size="lg"
          >
            <company-modal-handler
              :company="company"
              :is-white-label="isWhiteLabel"
              :clear-contact-data="clearDepartmentData"
              :loading="loading"
              @get-address-user="getAddressUser"
              @add-company="addCompany"
              @update-company="updateCompany"
              @hide-modal="hideModal"
            />
          </b-modal>
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
              :total-rows="totalCompanies"
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
      id="modal-add-company"
      :title="$t('administrationCompany.addCompany')"
      hide-footer
      size="lg"
    >
      <company-modal-handler
        :company="companyData"
        :clear-contact-data="clearDepartmentData"
        :loading="loading"
        @get-address-user="getAddressUser"
        @add-company="addCompany"
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
import store from '@/store'
import axios from '@axios'
import { ref, onUnmounted } from '@vue/composition-api'
import { avatarText, formatDateOnlyNumber } from '@core/utils/filter'
import useCompaniesList from './useCompaniesList'
import companyStoreModule from './companyStoreModule'
import useAppConfig from '@core/app-config/useAppConfig'
import CompanyModalHandler from './company-modal-handler/CompanyModalHandler.vue'
import CompanyCard from './company-card-handler/CompanyCard.vue'
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

    vSelect,
    CompanyModalHandler,
    CompanyCard,
  },
  data() {
    return {
      typePartners: [],
      partners: [],
      companyStatus: [],
      overdueInvoiceOptions: [{label: 'Sim', code: 'S'}, {label: 'Não', code: 'N'}],
      daysDueContractOptions: [{label: 'Vencido', code: 'V'}, {label: '30 dias', code: '30'}, {label: '60 dias', code: '60'}, {label: '90 dias', code: '90'}],
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
    axios
      .get('/api/administration/company/fetch-company-status/A')
      .then(response => {
        this.companyStatus = response.data.companyStatus
      });

    //Traz os tipos de parceiros existentes
    axios
      .get('/api/administration/partner/fetch-type-partners/')
      .then(response => {
        this.typePartners = response.data.typePartners
      });

    //Traz os parceiros cadastrados
    axios
      .get('/api/administration/partner/get-partners-by-status/A')
      .then(response => {
        this.partners = response.data.partners
        this.partners.unshift( {id: 0, par_corporate_name: 'Sem Parceiro'} )
      })
  },
  setup() {
    const COMPANY_APP_STORE_MODULE_NAME = 'app-company'

    // Register module
    if (!store.hasModule(COMPANY_APP_STORE_MODULE_NAME)) store.registerModule(COMPANY_APP_STORE_MODULE_NAME, companyStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(COMPANY_APP_STORE_MODULE_NAME)) store.unregisterModule(COMPANY_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()

    const blankUser = {
      partner: '',
      gender: '',
      com_name: '',
      com_cnpj: '',
      com_cpf: '',
      com_responsible_name: '',
      com_birthday: '',
      com_responsible_phone: '',
      com_responsible_email: '',
      com_finance_phone: '',
      com_finance_email: '',
      com_url: '',
      com_postal_code: '',
      com_address: '',
      com_address_number: '',
      com_complement: '',
      com_province: '',
      com_city: '',
      com_state: '',
      com_country: '',
    }

    const { skin } = useAppConfig()

    const companyData = ref(JSON.parse(JSON.stringify(blankUser)))
    //Limpa os dados do popup
    const clearDepartmentData = () => {
      companyData.value = JSON.parse(JSON.stringify(blankUser))
    }

    //Adiciona uma empresa
    const addCompany = companyData => {
      store.dispatch('app-company/addCompany', companyData)
      .then(() => {  
        refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Empresa adicionada com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
    }

    const updateCompany = companyData => {
      store.dispatch('app-company/updateCompany', companyData)
      .then(() => {  
        refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Empresa atualizada com sucesso!',
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

    const loading = ref(false)
    
    //Busca o endereço com base no CEP digitado
    const getAddressUser = cep => {
      //Se foi digitado todos os caracteres do CEP
      if(cep.length == 9) {
        //Mostra o spinner
        loading.value = true

        store.dispatch('app-company/getAddressUser', { cep: cep })
        .then(response => {
          //Se não houve erro ao buscar o endereço
          if(!response.data.error) { 
            companyData.value.com_address = response.data.address.logradouro 
            companyData.value.com_province = response.data.address.bairro 
            //companyData.value.address_complement = response.data.complemento 
            companyData.value.com_city = response.data.address.localidade 
            companyData.value.com_state = response.data.state.sta_name 
            companyData.value.com_country = response.data.country.cou_name

          }
          else {
            companyData.value.com_address = null 
            companyData.value.com_address_number = null 
            companyData.value.com_province = null 
            //addressLocal.value.address_complement = response.data.complemento 
            companyData.value.com_city = null 
            companyData.value.com_state = null 
            companyData.value.com_country = null
            toast({
              component: ToastificationContent,
              props: {
                title: response.data.error,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
           
          //console.log(response.data)

          //Esconde o spinner
          loading.value = false
        })
        .catch(error => {
        })
      }
    }

    const isAddNewUserSidebarActive = ref(false)

    const statusOptions = [
      { label: 'Pending', value: 'pending' },
      { label: 'Active', value: 'active' },
      { label: 'Inactive', value: 'inactive' },
    ]

    const {
      fetchCompanies,
      companies,
      isWhiteLabel,
      tableColumns,
      perPage,
      currentPage,
      totalCompanies,
      dataMeta,
      perPageOptions,
      searchQuery,
      typePartnerFilter,
      partnerFilter,
      companyStatusFilter,
      overdueInvoiceFilter,
      daysDueContractFilter,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,

      // UI
      resolveDepartmentStatusVariant,
      resolveUserRoleVariant,
      resolveCompanySituationVariant,

    } = useCompaniesList()

    fetchCompanies()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchCompanies,
      companies,
      isWhiteLabel,
      tableColumns,
      perPage,
      currentPage,
      totalCompanies,
      dataMeta,
      perPageOptions,
      searchQuery,
      typePartnerFilter,
      partnerFilter,
      companyStatusFilter,
      overdueInvoiceFilter,
      daysDueContractFilter,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      clearDepartmentData,
      companyData,
      addCompany,
      updateCompany,
      removeUser,
      getAddressUser,
      skin,

      // Filter
      avatarText,

      // UI
      resolveDepartmentStatusVariant,
      resolveUserRoleVariant,
      resolveCompanySituationVariant,

      statusOptions,

      //Spinner
      loading,

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
