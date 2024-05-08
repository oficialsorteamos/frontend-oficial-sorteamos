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
              <b-button
                variant="dark"
                v-b-modal.modal-add-user
              >
                <span class="text-nowrap">{{ $t('partner.addPartner') }}</span>
              </b-button>
            </div>
          </b-col>
        </b-row>
        <b-row
          class="mt-1"
        >
          <b-col
            lg="2"
            md="2"
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
      
      <!-- Parceiros cadastrados -->
      <b-row
        class="p-1"
        v-if="partners.length > 0"
      >
        <b-col
          lg="4"
          md="5"
          v-for="partner in partners"
          :key="partner.id"
          ref="refUserListTable"
        >
          <partner-card 
            :partner="partner"
            :fetch-partners="fetchPartners"
            @open-modal="openModal"
          />

          <!-- Form para cadastro de um novo usuário -->
          <b-modal
            :id="'modal-edit-partner-'+partner.id"
            :title="$t('partner.addPartner')"
            hide-footer
            size="lg"
          >
            <partner-modal-handler
              :partner="partner"
              :clear-contact-data="clearDepartmentData"
              :loading="loading"
              @get-address-user="getAddressUser"
              @update-partner="updatePartner"
              @hide-modal="hideModal"
              @open-modal="openModal"
            />
          </b-modal>
        </b-col>
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
              :total-rows="totalPartners"
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
      id="modal-add-user"
      :title="$t('partner.addPartner')"
      hide-footer
      size="lg"
    >
      <partner-modal-handler
        :partner="partnerData"
        :clear-contact-data="clearDepartmentData"
        :loading="loading"
        @get-address-user="getAddressUser"
        @add-partner="addPartner"
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
import usePartnersList from './usePartnersList'
import partnerStoreModule from './partnerStoreModule'
import PartnerModalHandler from './partner-modal-handler/PartnerModalHandler.vue'
import useAppConfig from '@core/app-config/useAppConfig'
import PartnerCard from './partner-card-handler/PartnerCard.vue'
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
    PartnerModalHandler,
    PartnerCard,
  },
  data() {
    return {
      typePartners: [],
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
    //Traz os tipos de parceiros existentes
    axios
      .get('/api/administration/partner/fetch-type-partners/')
      .then(response => {
        this.typePartners = response.data.typePartners
      });
  },
  setup() {
    const PARTNER_APP_STORE_MODULE_NAME = 'app-partner'

    // Register module
    if (!store.hasModule(PARTNER_APP_STORE_MODULE_NAME)) store.registerModule(PARTNER_APP_STORE_MODULE_NAME, partnerStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(PARTNER_APP_STORE_MODULE_NAME)) store.unregisterModule(PARTNER_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()

    const { skin } = useAppConfig()

    const blankUser = {
      type_partner: '',
      gender: '',
      par_corporate_name: '',
      par_url: '',
      par_partnership_started: '',
      par_cnpj: '',
      par_cpf: '',
      par_responsible_name: '',
      par_birthday: '',
      par_responsible_phone: '',
      par_responsible_email: '',
      par_finance_phone: '',
      par_finance_email: '',
      par_postal_code: '',
      par_address: '',
      par_address_number: '',
      par_complement: '',
      par_province: '',
      par_city: '',
      par_state: '',
      par_country: '',
    }
    const partnerData = ref(JSON.parse(JSON.stringify(blankUser)))
    //Limpa os dados do popup
    const clearDepartmentData = () => {
      partnerData.value = JSON.parse(JSON.stringify(blankUser))
    }

    //Adiciona um parceiro
    const addPartner = partnerData => {
      store.dispatch('app-partner/addPartner', partnerData)
      .then(() => {  
        refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Parceiro adicionado com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
    }

    const updatePartner = partnerData => {
      store.dispatch('app-partner/updatePartner', partnerData)
      .then(() => {  
        refetchData()
        toast({
          component: ToastificationContent,
          props: {
            title: 'Parceiro atualizado com sucesso!',
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
              store.dispatch('app-partner/removeUser', { id: userId })
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

        store.dispatch('app-partner/getAddressUser', { cep: cep })
        .then(response => {
          //Se não houve erro ao buscar o endereço
          if(!response.data.error) { 
            partnerData.value.par_address = response.data.address.logradouro 
            partnerData.value.par_province = response.data.address.bairro 
            //partnerData.value.address_complement = response.data.complemento 
            partnerData.value.par_city = response.data.address.localidade 
            partnerData.value.par_state = response.data.state 
            partnerData.value.par_country = response.data.country

          }
          else {
            partnerData.value.par_address = null 
            partnerData.value.par_address_number = null 
            partnerData.value.par_province = null 
            //addressLocal.value.address_complement = response.data.complemento 
            partnerData.value.par_city = null 
            partnerData.value.par_state = null 
            partnerData.value.par_country = null
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
      fetchPartners,
      tableColumns,
      perPage,
      currentPage,
      totalPartners,
      dataMeta,
      perPageOptions,
      searchQuery,
      typePartnerFilter,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      partners,

      // UI
      resolveDepartmentStatusVariant,
      resolveUserRoleVariant,
      resolveDepartmentSituationVariant,

    } = usePartnersList()

    fetchPartners()
    
    return {

      // Sidebar
      isAddNewUserSidebarActive,

      fetchPartners,
      tableColumns,
      perPage,
      currentPage,
      totalPartners,
      dataMeta,
      perPageOptions,
      searchQuery,
      typePartnerFilter,
      sortBy,
      isSortDirDesc,
      refUserListTable,
      refetchData,
      clearDepartmentData,
      partnerData,
      addPartner,
      updatePartner,
      removeUser,
      getAddressUser,
      partners,
      skin,

      // Filter
      avatarText,

      // UI
      resolveDepartmentStatusVariant,
      resolveUserRoleVariant,
      resolveDepartmentSituationVariant,

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
