<template>
  <div>
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
            md="11"
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
            md="1"
            class="text-right"
          >
            
          </b-col>
        </b-row>
      </div>
      
    <b-table
      ref="refCreditListTable"
      :items="fees"
      :fields="tableColumns"
      responsive
      show-empty
      :empty-text="$t('credit.noCreditsFound')"
    >

      <template  #cell(type_fee)="data">
        <div class="text-nowrap">
          <span class="align-text-top">{{data.item.type_fee.typ_description}}</span>
        </div>
      </template>

      <template  #cell(fee_value)="data">
        R$ {{ data.item.fee_value.replace(".", ",") }}
      </template>

      <template  #cell(status)="data">
        <b-badge
          :variant="resolveStatusVariant(data.value.sta_name)" 
        >
          {{ data.value.sta_name }}
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
            @click="openModal('modal-edit-fee-'+data.item.id)"
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
        <!-- Form para cadastro de um novo canal -->
        <b-modal
          :id="'modal-edit-fee-'+data.item.id"
          title="Edit Fee"
          hide-footer
          size="sm"
        >
          <fee-modal-edit-fee-handler
            :fee="data.item"
            :clear-contact-data="clearPaymentData"
            @update-fee="updateFee"
            @hide-modal="hideModal"
          />
        </b-modal>
      </template>
    </b-table>
    
    <!-- Pagination -->
    <div class="mx-2 mb-2">
      <b-row>
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-start"
        >
          <span class="text-muted">{{ $t('campaign.showing') }} {{ dataMeta.from }} {{ $t('campaign.to') }} {{ dataMeta.to }} {{ $t('campaign.of') }} {{ dataMeta.of }} {{ $t('campaign.entries') }}</span>
        </b-col>
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-end"
        >

          <b-pagination
            v-model="currentPage"
            :total-rows="totalCredits"
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
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink, BFormGroup, BSpinner,
  BBadge, BDropdown, BDropdownItem, BPagination, BCardBody, BCardHeader, VBTooltip, VBModal, BFormCheckbox,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import axios from '@axios'
import Ripple from 'vue-ripple-directive'
import { ref, onUnmounted } from '@vue/composition-api'
import { formatDateOnlyNumber, maskCardNumber } from '@core/utils/filter'
import useFeesList from './useFeesList'
import feeStoreModule from './feeStoreModule'
import FeeModalEditFeeHandler from './fee-modal-edit-fee-handler/FeeModalEditFeeHandler.vue'
import flatPickr from 'vue-flatpickr-component'
// localization is optional
import {Portuguese} from 'flatpickr/dist/l10n/pt.js';
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
    VBModal,
    BFormGroup,
    BSpinner,
    BFormCheckbox,
    flatPickr,

    vSelect,
    FeeModalEditFeeHandler,
  },
  directives: {
    'b-tooltip': VBTooltip,
    'b-modal': VBModal,
    Ripple,
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      console.log('open modal')
      console.log(modalName)
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
    handleTemplateClick(templateData) {
      //Abre o modal para atualização de mensagem rápida
      this.$emit('edit-template', templateData)
      this.$emit('open-modal', 'modal-new-template-message')  
    },
  },
   data() {
    return {
      categories: [],
      statusTemplate: [],
    }
  },
  setup() {
    const FEE_APP_STORE_MODULE_NAME = 'app-fee'

    // Register module
    if (!store.hasModule(FEE_APP_STORE_MODULE_NAME)) store.registerModule(FEE_APP_STORE_MODULE_NAME, feeStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(FEE_APP_STORE_MODULE_NAME)) store.unregisterModule(FEE_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()
    
    const blankPayment = {
      credit_value: '',
      payment_method: [],
      credit_card: null,
    }
    const paymentData = ref(JSON.parse(JSON.stringify(blankPayment)))
    //Limpa os dados do popup
    const clearPaymentData = () => {
      paymentData.value = JSON.parse(JSON.stringify(blankPayment))
    }

    //Atualiza os dados do departamento
    const updateFee = feeData => {
      store.dispatch('app-fee/updateFee', { feeData: feeData })
        .then(() => {  
          fetchFees()
          toast({
            component: ToastificationContent,
            props: {
              title: 'Taxa atualizada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const {
      fees,
      perPage,
      currentPage,
      perPageOptions,
      refCreditListTable,
      periodFilter,
      dataMeta,
      tableColumns,
      totalCredits,
      fetchFees,
      cardsData,

      resolveStatusVariant,
      checkFlagCard,

    } = useFeesList()

    fetchFees()
    
    return {
      fees,
      perPage,
      currentPage,
      perPageOptions,
      refCreditListTable,
      periodFilter,
      dataMeta,
      totalCredits,
      tableColumns,
      updateFee,
      
      fetchFees,
      cardsData,
      paymentData,
      clearPaymentData,

      resolveStatusVariant,
      formatDateOnlyNumber,
      maskCardNumber,
      checkFlagCard,
      Portuguese,
      
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

#btn-emoji-default {
  height: auto !important;
  width: 25px !important;
  margin: 0 !important;
}
#btn-emoji-default > div > img.emoji {
  width: 17px !important;
  height: 17px !important;
}
</style>
