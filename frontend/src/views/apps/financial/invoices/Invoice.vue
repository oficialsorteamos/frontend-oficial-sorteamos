<template>
  <div>
    <b-card 
      no-body
      class="p-1"
    >
      <b-card-header class="pb-50">
        <h5>
          {{ $t('invoice.filters') }}
        </h5>
      </b-card-header>
      <b-card-body>
        <b-row>
          <b-col
            md="3"
            class="mb-1"
          >
            <!-- Language -->
            <b-form-group
              :label="$t('invoice.status')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="statusFilter"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="statusPayments"
                :getOptionLabel="statusPayments => statusPayments.sta_name"
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
            cols="12"
            md="11"
            class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
          >
            <label>{{ $t('invoice.show') }}</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector d-inline-block mx-50"
            />
            <label>{{ $t('invoice.entries') }}</label>
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
        ref="refInvoiceListTable"
        :items="invoiceItems"
        :fields="tableColumns"
        responsive
        show-empty
        :empty-text="$t('invoice.noInvoicesFound')"
      >
      <!-- Ações -->
      <template #cell(actions)="row">
        <!-- Se o status for AGUARDANDO PAGAMENTO ou VENCIDO -->
        <span
          v-if="row.item.status_id == 1 || row.item.status_id == 4"
        >
          <a :href="row.item.inv_url_invoice" target="_blank" rel="noopener noreferrer">
            <feather-icon
              icon="DownloadIcon"
              size="16"
              class="cursor-pointer"
              v-if="row"
              v-b-tooltip.hover.v-secondary
              :title="$t('invoice.downloadInvoice')"
            />
          </a>
          <img 
            :src="require('@/assets/images/icons/pix.png')" 
            width="16px" 
            alt=""
            class="cursor-pointer"
            @click="openModal('modal-pix-qrcode'); getPixQrcode(row.item.api_payment_invoice_id)"
          >
        </span>
      </template>

      <template #cell(show)="row">
        <b-form-checkbox
          v-model="row.detailsShowing"
          plain
          class="vs-checkbox-con"
          @change="row.toggleDetails"
        >
          <span class="vs-checkbox">
          </span>
        </b-form-checkbox>
      </template>

      <template #row-details="row">
        <b-card>
          <invoice-details
            :invoice-data="row.item"
          />
        </b-card>
      </template>

      <template #cell(inv_closing)="data">
        <div class="text-nowrap">
          <span class="align-text-top">{{ formatDate(data.item.inv_closing) }}</span>
        </div>
      </template>

      <template #cell(inv_due)="data">
        <div class="text-nowrap">
          <span class="align-text-top">{{ formatDate(data.item.inv_due) }}</span>
        </div>
      </template>

      <template  #cell(total_invoice_value)="data">
        R$ {{ data.item.total_invoice_value.replace(".", ",") }}
      </template>

      <template  #cell(status)="data">
        <b-badge
          :variant="resolveStatusVariant(data.value.sta_name)" 
        >
          {{ data.value.sta_name }}
        </b-badge>
      </template>
    </b-table>
    <!-- Modal que apresenta o Qrcode para pagamento via PIX -->
    <b-modal
      id="modal-pix-qrcode"
      :title="$t('invoice.generatePixQrcode')"
      hide-footer
      size="lg"
    >
      <invoice-modal-pix-qrcode-handler
        :qrcode="pixQrcode"
      />
    </b-modal>
    <!-- Pagination -->
    <div class="mx-2 mb-2">
      <b-row>
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-start"
        >
          <span class="text-muted">{{ $t('campaign.showing') }} {{ dataMetaTemplate.from }} {{ $t('campaign.to') }} {{ dataMetaTemplate.to }} {{ $t('campaign.of') }} {{ dataMetaTemplate.of }} {{ $t('campaign.entries') }}</span>
        </b-col>
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-end"
        >

          <b-pagination
            v-model="currentPage"
            :total-rows="totalInvoices"
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
import { formatDate } from '@core/utils/filter'
import useInvoicesList from './useInvoicesList'
import invoiceStoreModule from './invoiceStoreModule'
import InvoiceModalPixQrcodeHandler from './invoice-modal-pix-qrcode-handler/InvoiceModalPixQrcodeHandler.vue'
import InvoiceDetails from './InvoiceDetails.vue'
import Vue from 'vue'
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
    VBModal,
    BFormGroup,
    BSpinner,
    BFormCheckbox,

    vSelect,
    InvoiceDetails,
    InvoiceModalPixQrcodeHandler,
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
      statusPayments: [],
    }
  },
  created() { 
    //Status de pagamento
    axios
      .get('/api/financial/fetch-status-payments')
      .then(response => {
        console.log(response.data)
        this.statusPayments = response.data.statusPayments
      });
  },
  setup() {
    const INVOICE_APP_STORE_MODULE_NAME = 'app-invoice'

    // Register module
    if (!store.hasModule(INVOICE_APP_STORE_MODULE_NAME)) store.registerModule(INVOICE_APP_STORE_MODULE_NAME, invoiceStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(INVOICE_APP_STORE_MODULE_NAME)) store.unregisterModule(INVOICE_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()


    const pixQrcode = ref('')

    const getPixQrcode = (chargeIdApi)  => {
      pixQrcode.value = ''
      //Traz as mensagens rápidas cadastradas
      store.dispatch('app-invoice/getPixQrcode', { chargeIdApi: chargeIdApi })
        .then(response => {
          console.log('qrcode')
          console.log(response.data)
          pixQrcode.value = response.data.qrcode
        })
        .catch(error => {
        })
    }
    


    const {
      invoiceItems,
      perPage,
      currentPage,
      perPageOptions,
      refInvoiceListTable,
      categoryFilter,
      statusFilter,
      dataMetaTemplate,
      tableColumns,
      totalInvoices,
      fetchInvoices,

      resolveStatusVariant,

    } = useInvoicesList()

    fetchInvoices()
    
    return {
      invoiceItems,
      perPage,
      currentPage,
      perPageOptions,
      refInvoiceListTable,
      categoryFilter,
      statusFilter,
      dataMetaTemplate,
      totalInvoices,
      tableColumns,
      
      fetchInvoices,

      resolveStatusVariant,
      formatDate,
      getPixQrcode,
      pixQrcode,
      
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
