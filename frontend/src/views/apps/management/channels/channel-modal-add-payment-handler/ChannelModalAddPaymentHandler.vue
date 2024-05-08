<template>
  <div>
    <b-tabs>
      <b-tab>
        <template #title>
          <feather-icon icon="DollarSignIcon" />
          <span>{{ $t('channel.channelModalAddPaymentHandler.payment') }}</span>
        </template>
        <form-wizard
          color="#7367F0"
          :title="null"
          :subtitle="null"
          :next-button-text="$t('user.userModalHandler.next')"
          :finish-button-text="paymentLocal.payment_method && paymentLocal.payment_method.id == 1 ?$t('campaign.campaignModalAddCreditHandler.confirm') : 'Gerar QrCode'"
          :back-button-text="$t('user.userModalHandler.previous')"
          class="steps-transparent mb-3"
          @on-complete="onSubmit"
        >
          <!-- address -->
          <tab-content
            :title="$t('campaign.campaignModalAddCreditHandler.paymentMethod')"
            icon="feather icon-credit-card"
            :before-change="validationFormAddress"
          >
            <validation-observer
              ref="addressRules"
              tag="form"
            >
              <b-row>
                <b-col
                  cols="12"
                  class="mb-2"
                >
                  <h5 class="mb-0">
                    {{ $t('campaign.campaignModalAddCreditHandler.paymentMethod') }}
                  </h5>
                  <small class="text-muted">{{ $t('campaign.campaignModalAddCreditHandler.chooseDesiredPaymentMethod') }}</small>
                </b-col>
                <input
                    type="hidden"
                    id="feeValue"
                    v-if="fee"
                    v-bind:value="paymentLocal.credit_description = creditDescription"
                  />
                <b-col md="5">
                  <validation-provider
                    #default="{ errors }"
                    :name="$t('campaign.campaignModalAddCreditHandler.paymentMethod')"
                    rules="required"
                  >
                    <b-form-group
                      :label="$t('campaign.campaignModalAddCreditHandler.paymentMethod')+'*'" 
                      label-for="vue-select"
                    >
                      <v-select
                        id="vue-select"
                        v-model="paymentLocal.payment_method"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="paymentMethods"
                        :getOptionLabel="paymentMethods => paymentMethods.pay_name"
                        transition=""
                      />
                      <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                        {{ errors[0] }}
                      </b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>
              </b-row>
              <b-row>
                <b-col 
                  md="8"
                >
                  <span
                    v-if="paymentLocal.payment_method && paymentLocal.payment_method.id == 1"
                  >
                    <validation-provider
                      #default="{ errors }"
                      :name="$t('campaign.campaignModalAddCreditHandler.cards')"
                      rules="required"
                    >
                      <b-form-group
                        :label="$t('campaign.campaignModalAddCreditHandler.cards')+'*'" 
                        label-for="vue-select"
                      >
                        <v-select
                          id="vue-select"
                          v-model="paymentLocal.credit_card"
                          :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                          :options="cards"
                          :getOptionLabel="cards => cards.description"
                          transition=""
                        />
                        <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                          {{ errors[0] }}
                        </b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </span>
                  <span v-else></span>
                </b-col>
                <b-col
                  md="1"
                >
                  <b-button
                    variant="dark"
                    class="btn-icon rounded-circle mt-2"
                    v-b-tooltip.hover.v-secondary
                    v-b-modal.modal-add-card
                    :title="$t('campaign.campaignModalAddCreditHandler.addNewCard')"
                    v-if="paymentLocal.payment_method && paymentLocal.payment_method.id == 1"
                  >
                    <feather-icon 
                      icon="PlusIcon" 
                      size="20"
                    />
                  </b-button>

                  <!-- Modal para adição de um novo cartão de crédito -->
                  <b-modal
                    id="modal-add-card"
                    :title="$t('campaign.campaignModalAddCreditHandler.addNewCard')"
                    hide-footer
                    size="lg"
                  >
                    <card-modal-add-card-handler
                      :card="cardData"
                      :clear-contact-data="clearCardData"
                      @add-card="addCard"
                      @hide-modal="hideModal"
                    />
                  </b-modal>
                </b-col>
              </b-row>
              <b-row>
                <b-col 
                  class="mb-2"
                  md="2"
                >
                  <span
                    v-if="paymentLocal.credit_card"
                  >
                    <validation-provider
                      #default="validationContext"
                      :name="$t('campaign.campaignModalAddCreditHandler.cvv')"
                      rules="required|min:3"
                    >
                      <b-form-group
                        :label="$t('campaign.campaignModalAddCreditHandler.cvv')+'*'"
                        label-for="campaign-ccv"
                      >
                        <b-form-input
                          id="campaign-ccv"
                          v-model="paymentLocal.ccv"
                          :state="getValidationState(validationContext)"
                          :maxlength="4"
                          trim
                          type="number"
                          placeholder=""
                          autocomplete="off"
                        />

                        <b-form-invalid-feedback>
                          {{ validationContext.errors[0] }}
                        </b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </span>
                </b-col>
              </b-row>
            </validation-observer>
          </tab-content>
          
          <!-- payment detail tab -->
          <tab-content
            :title="$t('campaign.campaignModalAddCreditHandler.creditDetails')"
            icon="feather icon-file-text"
            :before-change="validationFormAccess"
          >
            <validation-observer
              ref="accessRules"
              tag="form"
            >
              <b-row>
                <b-col
                  cols="12"
                  class="mb-2"
                >
                  <h5 class="mb-0">
                    {{ $t('channel.channelModalAddPaymentHandler.paymentsdetails') }}
                  </h5>
                  <small class="text-muted">
                    {{ $t('channel.channelModalAddPaymentHandler.checkPayment') }}
                  </small>
                </b-col>
                <!-- Credit -->
                <b-col md="12">
                  <b-media no-body>
                    <b-media-aside>
                      <b-avatar
                        rounded
                        size="42"
                        variant="light-success"
                      >
                        <feather-icon
                          size="18"
                          icon="DollarSignIcon"
                        />
                      </b-avatar>
                    </b-media-aside>
                    <b-media-body>
                      <h6 class="transaction-title">
                        {{ $t('channel.channelModalAddPaymentHandler.value') }}
                      </h6>
                      <small>{{ $t('channel.channelModalAddPaymentHandler.channelValue') }}</small>
                    </b-media-body>
                  </b-media>
                  <input
                    type="hidden"
                    id="feeValue"
                    v-if="fee"
                    v-bind:value="paymentLocal.credit_value = fee.fee_value"
                  />
                  <div
                    class="font-weight-bolder text-success float-right"
                    style="margin-top: -35px"
                    v-if="fee"
                  >
                    R$ {{ fee.fee_value.toString().replace('.', ',') }}
                  </div>
                </b-col>
                <!-- Payment Method -->
                <b-col md="12" class="mt-2 mb-3">
                  <b-media no-body>
                    <b-media-aside>
                      <b-avatar
                        rounded
                        size="42"
                        variant="light-primary"
                      >
                        <feather-icon
                          size="18"
                          icon="CreditCardIcon"
                        />
                      </b-avatar>
                    </b-media-aside>
                    <b-media-body>
                      <h6 class="transaction-title">
                        {{ $t('campaign.campaignModalAddCreditHandler.paymentMethod') }}
                      </h6>
                      <small>{{ $t('campaign.campaignModalAddCreditHandler.paymentMethodUsed') }}</small>
                    </b-media-body>
                  </b-media>
                  <div
                    class="font-weight-bolder text-primary float-right text-right"
                    style="margin-top: -45px; width: 200px;"
                    v-if="paymentLocal.credit_card && paymentLocal.payment_method && paymentLocal.payment_method.id == 1"
                  >
                    {{ paymentLocal.credit_card.description }}
                  </div>
                  <div
                    class="font-weight-bolder text-primary float-right text-right"
                    style="margin-top: -45px; width: 200px;"
                    v-else-if="paymentLocal.payment_method && paymentLocal.payment_method.id == 3"
                  >
                    {{ paymentLocal.payment_method.pay_name }}
                  </div>
                </b-col>
              </b-row>
            </validation-observer>
          </tab-content>
        </form-wizard>
      </b-tab>
      <b-tab>
        <template #title>
          <feather-icon icon="SettingsIcon" />
          <span>{{ $t('channel.channelModalAddPaymentHandler.settings') }}</span>
        </template>
        
        <validation-observer
          #default="{ handleSubmit }"
          ref="refFormObserver"
        >
            <!-- select 2 demo -->
          <b-form
            enctype="multipart/form-data"
            @submit.prevent="handleSubmit(onSubmitSubscription)"
          >
            <b-row
              class="mt-2 mb-2"
            >
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
              >
                <b-form-checkbox
                  v-model="paymentLocal.cha_automatic_subscription_renewal"
                  name="check-button"
                  class="custom-control-primary"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('channel.channelModalAddPaymentHandler.automaticSubscriptionRenewal') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- Cartão usado para assinatura -->
            <b-row>
              <b-col 
                md="8"
              >
                <span
                  v-if="paymentLocal.cha_automatic_subscription_renewal == 1"
                >
                  <validation-provider
                    #default="{ errors }"
                    :name="$t('campaign.campaignModalAddCreditHandler.cards')"
                    :rules="paymentLocal.cha_automatic_subscription_renewal == 1? 'required' : ''"
                  >
                    <b-form-group
                      :label="$t('campaign.campaignModalAddCreditHandler.cards')+'*'" 
                      label-for="vue-select"
                    >
                      <v-select
                        id="vue-select"
                        v-model="paymentLocal.credit_card_renewal"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="cards"
                        :getOptionLabel="cards => cards.description"
                        transition=""
                        @input="selectChanged"
                      />
                      <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                        {{ errors[0] }}
                      </b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </span>
                <span v-else></span>
              </b-col>
              <b-col
                md="1"
              >
                <b-button
                  variant="dark"
                  class="btn-icon rounded-circle mt-2"
                  v-b-tooltip.hover.v-secondary
                  v-b-modal.modal-add-card
                  :title="$t('campaign.campaignModalAddCreditHandler.addNewCard')"
                  v-if="paymentLocal.cha_automatic_subscription_renewal == 1"
                >
                  <feather-icon 
                    icon="PlusIcon" 
                    size="20"
                  />
                </b-button>
              </b-col>
            </b-row>
            <b-row>
              <b-col 
                class="mb-2"
                md="2"
              >
                <span
                  v-if="paymentLocal.credit_card_renewal && ccvVisibility"
                >
                  <validation-provider
                    #default="validationContext"
                    :name="$t('campaign.campaignModalAddCreditHandler.cvv')"
                    rules="required|min:3"
                  >
                    <b-form-group
                      :label="$t('campaign.campaignModalAddCreditHandler.cvv')+'*'"
                      label-for="campaign-ccv"
                    >
                      <b-form-input
                        id="campaign-ccv"
                        v-model="paymentLocal.ccv_renewal"
                        :state="getValidationState(validationContext)"
                        :maxlength="4"
                        trim
                        type="number"
                        placeholder=""
                        autocomplete="off"
                      />

                      <b-form-invalid-feedback>
                        {{ validationContext.errors[0] }}
                      </b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </span>
              </b-col>
            </b-row>
            
            <!-- Form Actions -->
            <div class="d-flex mt-2 modal-footer">
              <b-button
                v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                variant="primary"
                class="mr-2"
                type="submit"
              >
                {{ $t('channel.channelModalEditChannelHandler.update') }}
              </b-button>
            </div>
          </b-form>
        </validation-observer>
      </b-tab>
      <b-tab>
        <template #title>
          <feather-icon icon="ListIcon" />
          <span>{{ $t('channel.channelModalAddPaymentHandler.paymentsHistory') }}</span>
        </template>
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
          <!-- Pagamentos realizados -->
          <b-table
            class="position-relative"
            :items="channelPayments"
            responsive
            :fields="tableColumns"
            primary-key="id"
            :sort-by.sync="sortBy"
            show-empty
            :empty-text="$t('channel.channelModalAddPaymentHandler.paymentsNotFound')"
            :sort-desc.sync="isSortDirDesc"
          >

            
            <template #cell(payment_method)="data">
              <div class="text-nowrap">
                <span class="align-text-top text-capitalize"><strong> {{ data.item.payment_method.pay_name }} </strong></span>
              </div>
            </template>

            
            <template #cell(cha_value)="data">
              <div class="text-nowrap">
                <span class="align-text-top">R$ {{ data.item.cha_value.replace(".", ",") }}</span>
              </div>
            </template>

            <template #cell(created_at)="data">
              <div class="text-nowrap">
                <span class="align-text-top">{{ formatDateOnlyNumber(data.item.created_at) }}</span>
              </div>
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
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import {
  BRow, BCol, BFormGroup, BFormInput, BFormInvalidFeedback, BFormDatepicker, BSpinner, BInputGroupAppend,  BForm,
  BInputGroup, VBTooltip, BButton, BAvatar, BMediaBody, BMediaAside, BMedia, BTabs, BTab, BTable, BCard, BPagination, BFormCheckbox,
} from 'bootstrap-vue'
import store from '@/store'
import axios from '@axios'
import Ripple from 'vue-ripple-directive'
import { FormWizard, TabContent } from 'vue-form-wizard'
import vSelect from 'vue-select'
import { toRefs, ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useChannelModalAddPaymentHandler from './useChannelModalAddPaymentHandler'
import usePaymentsList from './usePaymentsList'
import CardModalAddCardHandler from '../../../financial/cards/card-modal-add-card-handler/CardModalAddCardHandler.vue'
import { formatDateOnlyNumber } from '@core/utils/filter'
import { required } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'
import { codeIconInfo } from './code'
import Cleave from 'vue-cleave-component'
import { VueMaskDirective, VueMaskFilter } from 'v-mask'
import money from 'v-money'
import Vue from 'vue'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'
Vue.filter('VMask', VueMaskFilter)
Vue.directive('mask', VueMaskDirective)
// register directive v-money and component <money>
Vue.use(money, {precision: 2})


export default {
  components: {
    FormWizard,
    TabContent,
    BRow,
    BCol,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    BFormDatepicker,
    BSpinner,
    BButton,
    BAvatar,
    BMediaBody,
    BMediaAside,
    BMedia,
    BTabs,
    BTab,
    BTable,
    BCard,
    BForm,
    BFormCheckbox,
    BPagination,
    vSelect,
    Cleave,
    // eslint-disable-next-line vue/no-unused-components
    ToastificationContent,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
    BInputGroupAppend,
    BInputGroup,

    //Máscara
    VueMaskDirective,

    CardModalAddCardHandler,
  },
  directives: {
    'b-tooltip': VBTooltip,
    Ripple,
  },
  props: {
    channel: {
      type: Object,
      required: true,
    },
    clearContactData: {
      type: Function,
      required: true,
    },
    addCard: {
      type: Function,
      required: true,
    },
    cardsData: {
      type: Number,
      required: false,
    },
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      fee: null,
      selectedContry: 'select_value',
      selectedLanguage: 'nothing_selected',
      codeIconInfo,
      paymentMethods: [],
      cards: [],
      roles: [],
      money: {
        decimal: ',',
        thousands: '.',
        prefix: 'R$ ',
        //suffix: ' #',
        precision: 2,
        masked: false
      },
      creditDescription: 'Assinatura de canal',
      ccvVisibility: false,
    }
  },
  created() { 
    //Traz os departamentos cadastrados
    axios
      .get('/api/financial/fetch-payment-methods/')
      .then(response => {
        this.paymentMethods = response.data.paymentMethods
      });
    
    //Traz os cartões cadastrados
    axios
      .get('/api/financial/fetch-cards-by-type/1')
      .then(response => {
        console.log(response.data)
        var cardsData = response.data.cards
        cardsData.forEach(this.formattedCard);
      });
    
    var typeFee = this.channel.cha_api_official? 3 : 4;
    //Traz os cartões cadastrados
    axios
      .get('/api/financial/fetch-fee-by-type/'+typeFee)
      .then(response => {
        this.fee = response.data.fee
        //console.log('fee')
        //console.log(this.fee)
      });

    //Se houver alguma assinatura ativa, colocar o cartão utilizado como pré-selecionado
    if(this.channel.subscription) {
      this.formattedOnlyCard(this.channel.subscription.card)
    }
  },
  watch: {
    //Caso haja alguma alteração
    cardsData(cards) {
      this.cards = []
      axios
      .get('/api/financial/fetch-cards-by-type/1')
      .then(response => {
        var cardsData = response.data.cards
        cardsData.forEach(this.formattedCard);
      });
    }
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    selectChanged(dataSelected) {
      //Se nenhum cartão foi selecionado, não exibe o ccv
      if(dataSelected == null) {
        this.ccvVisibility = false  
      }
      else {
        this.ccvVisibility = true
      }
    },
    formattedOnlyCard(card, index) {
      var mainCard = '';
      var optionSelectCard = {}
      if(card.car_main == 1) {
        mainCard = ' (Principal)'
      } 
      const regex = /\d{4}/g;
      const cardNumberFormatted = card.car_number.toString().replace(regex, (maths) => maths === 12 ? maths : maths + ' ')
      optionSelectCard.description = cardNumberFormatted +'  -  Venc. '+ card.car_due_month + '/' + card.car_due_year + '  ' + mainCard
      optionSelectCard.data = card
      this.paymentLocal.credit_card_renewal = optionSelectCard
      this.ccvVisibility = false
    },
    formattedCard(card, index) {
      var mainCard = '';
      var optionSelectCard = {}
      if(card.car_main == 1) {
        mainCard = ' (Principal)'
      } 
      const regex = /\d{4}/g;
      const cardNumberFormatted = card.car_number.toString().replace(regex, (maths) => maths === 12 ? maths : maths + ' ')
      optionSelectCard.description = cardNumberFormatted +'  -  Venc. '+ card.car_due_month + '/' + card.car_due_year + '  ' + mainCard
      optionSelectCard.data = card
      this.cards.push(optionSelectCard)
    },
    formSubmitted() {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Form Submitted',
          icon: 'EditIcon',
          variant: 'success',
        },
      })
    },
    validationForm() {
      return new Promise((resolve, reject) => {
        this.$refs.accountRules.validate().then(success => {
          if (success) {
            resolve(true)
          } else {
            reject()
          }
        })
      })
    },
    validationFormAddress() {
      return new Promise((resolve, reject) => {
        this.$refs.addressRules.validate().then(success => {
          if (success) {
            resolve(true)
          } else {
            reject()
          }
        })
      })
    },
    validationFormInfo() {
      return new Promise((resolve, reject) => {
        this.$refs.infoRules.validate().then(success => {
          if (success) {
            resolve(true)
          } else {
            reject()
          }
        })
      })
    },
    validationFormAccess() {
      return new Promise((resolve, reject) => {
        this.$refs.accessRules.validate().then(success => {
          if (success) {
            resolve(true)
          } else {
            reject()
          }
        })
      })
    },
  },
  setup(props,{ emit }) {
    /*
     ? This is handled quite differently in SFC due to deadlock of `useFormValidation` and this composition function.
     ? If we don't handle it the way it is being handled then either of two composition function used by this SFC get undefined as one of it's argument.
     * The Trick:

     * We created reactive property `clearFormData` and set to null so we can get `resetEventLocal` from `useCalendarEventHandler` composition function.
     * Once we get `resetEventLocal` function which is required by `useFormValidation` we will pass it to `useFormValidation` and in return we will get `clearForm` function which shall be original value of `clearFormData`.
     * Later we just assign `clearForm` to `clearFormData` and can resolve the deadlock. 😎

     ? Behind The Scene
     ? When we passed it to `useCalendarEventHandler` for first time it will be null but right after it we are getting correct value (which is `clearForm`) and assigning that correct value.
     ? As `clearFormData` is reactive it is being changed from `null` to corrent value and thanks to reactivity it is also update in `useCalendarEventHandler` composition function and it is getting correct value in second time and can work w/o any issues.
    */
    const toast = useToast()

    const {
      paymentLocal,
      resetTransferLocal,
      // UI
      onSubmit,
      onSubmitSubscription,
    } = useChannelModalAddPaymentHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)

    const blankCard = {
      cardName: "",
      cardNumber: "",
      cardMonth: "",
      cardYear: "",
      cardCvv: "",
      mainCard: false,
      corporateCard: '0',
      holder_info: {
        //car_name: ''
      }
    }
    const cardData = ref(JSON.parse(JSON.stringify(blankCard)))
    //Limpa os dados do popup
    const clearCardData = () => {
      cardData.value = JSON.parse(JSON.stringify(blankCard))
    }

    const {
      fetchChannelPayments,
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
      channelPayments,

      // UI
      resolveDepartmentStatusVariant,

    } = usePaymentsList()

    fetchChannelPayments(props.channel)

    //Atualiza os dados dos parâmetros de cobrança
    const updateSubscriptionRenewal = subscriptionData => {
      store.dispatch('app-channel/updateSubscriptionRenewal', { subscriptionData: subscriptionData })
        .then(response => {  
          emit('set-channel', response.data.channel)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Parâmetro atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }
    

    return {
      paymentLocal,
      resetTransferLocal,
      onSubmit,
      onSubmitSubscription,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,

      cardData,
      clearCardData,

      fetchChannelPayments,
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
      channelPayments,
      updateSubscriptionRenewal,

      formatDateOnlyNumber,

      // UI
      resolveDepartmentStatusVariant,

    }
  },
}
</script>