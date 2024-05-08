<template>
  <div>
    <b-row>
      <b-col
        md="9"
      >
        <b-card 
          no-body
          class="p-1 mr-2"
        >
          <b-card-header class="pb-50">
            <h5>
              {{ $t('contacts.contactsList.filters') }}
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
                  :label="$t('credit.period')"
                  label-for="vue-select"
                >
                  <flat-pickr
                    v-model="periodFilter"
                    class="form-control"
                    :config="{
                                mode: 'range',
                                wrap: true, // set wrap to true only when using 'input-group'
                                altFormat: 'd/m/Y',
                                altInput: true,
                                dateFormat: 'Y-m-d',
                                locale: Portuguese, // locale for this instance only          
                            }"
                  />
                </b-form-group>
              </b-col>
            </b-row>
            <b-row
              
            >
              <b-col
                lg="2"
                md="2"
              >
                <b-button
                variant="primary"
                @click="clearFilter"
                style="margin-bottom: -15px"
              >
                <span class="text-nowrap">{{ $t('credit.clear') }}</span>
              </b-button>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-col>
      <b-col
        md="3"
      >
        <b-card
          no-body
        >
          <b-card-body style="padding-bottom: 2.5rem;">
            <b-row class="d-flex justify-content-center">
              <b-col
                xl="12"
                md="4"
                sm="6"
              >
                <statistic-card-horizontal
                  icon="DollarSignIcon"
                  color="success"
                  :statistic="balance"
                  :statistic-title="$t('credit.balance')"
                />
              </b-col>
              <b-button
                v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                variant="dark"
                style="margin-top: -5px"
                v-b-modal.modal-add-credit
              >
                {{ $t('credit.addCredit') }}
              </b-button>
            </b-row>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
    
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
        :items="creditItems"
        :fields="tableColumns"
        responsive
        show-empty
        :empty-text="$t('credit.noCreditsFound')"
      >

      <template  #cell(payment_method)="data">
        <div class="text-nowrap">
          <span class="align-text-top">{{ data.value.pay_name }} {{'('+ maskCardNumber(data.item.card.car_number) }}
            <span
              v-if="checkFlagCard(data.item.card.car_number) == 'visa'"
            >
              <img src="@/assets/images/cards/visa.png" width="35" alt="">
            </span>
            <span
              v-else-if="checkFlagCard(data.item.card.car_number) == 'mastercard'"
            >
              <img src="@/assets/images/cards/mastercard.png" width="30" alt="">
            </span>
            <span
              v-else-if="checkFlagCard(data.item.card.car_number) == 'amex'"
            >
              <img src="@/assets/images/cards/amex.png" width="30" alt="">
            </span>
             {{')'}}
          </span>
        </div>
      </template>

      <template #cell(created_at)="data">
        <div class="text-nowrap">
          <span class="align-text-top">{{ formatDateOnlyNumber(data.item.created_at) }}</span>
        </div>
      </template>

      <template  #cell(cre_value)="data">
        R$ {{ data.item.cre_value.replace(".", ",") }}
      </template>

      <template  #cell(status)="data">
        <b-badge
          :variant="resolveStatusVariant(data.value.sta_name)" 
        >
          {{ data.value.sta_name }}
        </b-badge>
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
  <!-- Modal para adição de crédito para utilização da campanha -->
  <b-modal
    id="modal-add-credit"
    :title="$t('credit.addCredit')"
    hide-footer
    size="lg"
  >
    <campaign-modal-add-credit-handler
      :payment="paymentData"
      :cards-data="cardsData"
      :clear-contact-data="clearPaymentData"
      :add-card="addCard"
      @add-credit="addCredit"
      @hide-modal="hideModal"
    />
  </b-modal>
    
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
import useCreditsList from './useCreditsList'
import creditStoreModule from './creditStoreModule'
import ChatModalNewTemplateMessageHandler from '../../chat/chat-modal-new-template-message-handler/ChatModalNewTemplateMessageHandler.vue'
import StatisticCardHorizontal from '@core/components/statistics-cards/StatisticCardHorizontal.vue'
import CampaignModalAddCreditHandler from '../../campaign/campaign-modal-add-credit-handler/CampaignModalAddCreditHandler.vue'
import flatPickr from 'vue-flatpickr-component'
// localization is optional
import {Portuguese} from 'flatpickr/dist/l10n/pt.js';


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
    ChatModalNewTemplateMessageHandler,
    StatisticCardHorizontal,
    CampaignModalAddCreditHandler,
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
    const CREDIT_APP_STORE_MODULE_NAME = 'app-credit'

    // Register module
    if (!store.hasModule(CREDIT_APP_STORE_MODULE_NAME)) store.registerModule(CREDIT_APP_STORE_MODULE_NAME, creditStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(CREDIT_APP_STORE_MODULE_NAME)) store.unregisterModule(CREDIT_APP_STORE_MODULE_NAME)
    })

    

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

    const {
      creditItems,
      perPage,
      currentPage,
      perPageOptions,
      refCreditListTable,
      periodFilter,
      loadingRefresh,
      dataMeta,
      tableColumns,
      totalCredits,
      fetchCredits,
      addCard,
      addCredit,
      clearFilter,
      cardsData,
      balance,

      resolveStatusVariant,
      checkFlagCard,

    } = useCreditsList()

    fetchCredits()
    
    return {
      creditItems,
      perPage,
      currentPage,
      perPageOptions,
      refCreditListTable,
      periodFilter,
      loadingRefresh,
      dataMeta,
      totalCredits,
      tableColumns,
      
      fetchCredits,
      addCard,
      addCredit,
      clearFilter,
      cardsData,
      balance,
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
