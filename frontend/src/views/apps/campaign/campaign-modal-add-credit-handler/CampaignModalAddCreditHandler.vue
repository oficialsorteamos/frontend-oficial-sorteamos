<template>
  <div>
    <form-wizard
      color="#7367F0"
      :title="null"
      :subtitle="null"
      :next-button-text="$t('user.userModalHandler.next')"
      :finish-button-text="$t('campaign.campaignModalAddCreditHandler.confirm')"
      :back-button-text="$t('user.userModalHandler.previous')"
      class="steps-transparent mb-3"
      @on-complete="onSubmit"
    >
      
      <!-- personal details -->
      <tab-content
        :title="$t('campaign.campaignModalAddCreditHandler.amountCredited')"
        icon="feather icon-dollar-sign"
        :before-change="validationFormInfo"
      >
        <validation-observer
          ref="infoRules"
          tag="form"
        >
          <b-row>
            <b-col
              cols="12"
              class="mb-2"
            >
              <h5 class="mb-0">
                {{ $t('campaign.campaignModalAddCreditHandler.amountCredited') }}
              </h5>
              <small class="text-muted">{{ $t('campaign.campaignModalAddCreditHandler.enterAmountCredited') }}</small>
            </b-col>

            <!-- Valor a ser creditado -->
            <b-col md="3">
              <validation-provider
                #default="validationContext"
                :name="$t('campaign.campaignModalAddCreditHandler.value')"
                rules="required|between:50,200000"
              >
                <b-form-group
                  label-for="payment-value"
                  :label="$t('campaign.campaignModalAddCreditHandler.value')+'*'"
                >
                  <money
                    v-model="paymentLocal.credit_value"
                    id="payment-value"
                    class="form-control"
                    :state="getValidationState(validationContext)"
                    placeholder="Ex: 50,00"
                    autocomplete="off"
                    v-bind="money"
                  />
                  <!-- Se o canal estiver ativo e o mesmo não for oficial -->
                  <span 
                    v-if="paymentLocal.credit_value < 50"
                    style="font-size: 0.857rem; color: #ea5455;"
                  >
                    {{ $t('campaign.campaignModalAddCreditHandler.minimumCredit') }}
                  </span>
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
        </validation-observer>
      </tab-content>

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
                {{ $t('campaign.campaignModalAddCreditHandler.creditDetails') }}
              </h5>
              <small class="text-muted">
                {{ $t('campaign.campaignModalAddCreditHandler.CreditEntryDetails') }}
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
                    {{ $t('campaign.campaignModalAddCreditHandler.creditValue') }}
                  </h6>
                  <small>{{ $t('campaign.campaignModalAddCreditHandler.amountCreditInserted') }}</small>
                </b-media-body>
              </b-media>
              <div
                class="font-weight-bolder text-success float-right"
                style="margin-top: -35px"
                v-if="paymentLocal.credit_value != ''"
              >
                R$ {{ paymentLocal.credit_value.toString().replace('.', ',') }}
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
                v-if="paymentLocal.credit_card"
              >
                {{ paymentLocal.credit_card.description }}
              </div>
            </b-col>
          </b-row>
        </validation-observer>
      </tab-content>
    </form-wizard>
  </div>
</template>

<script>
import {
  BRow, BCol, BFormGroup, BFormInput, BFormInvalidFeedback, BFormDatepicker, BSpinner, BInputGroupAppend, 
  BInputGroup, VBTooltip, BButton, BAvatar, BMediaBody, BMediaAside, BMedia,
} from 'bootstrap-vue'
import axios from '@axios'
import { FormWizard, TabContent } from 'vue-form-wizard'
import vSelect from 'vue-select'
import { toRefs, ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCampaignModalAddCreditHandler from './useCampaignModalAddCreditHandler'
import CardModalAddCardHandler from '../../financial/cards/card-modal-add-card-handler/CardModalAddCardHandler.vue'
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
  },
  props: {
    payment: {
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
      }
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

      console.log('this.cards')
      console.log(this.cards)
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

    const {
      paymentLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCampaignModalAddCreditHandler(toRefs(props), emit)

    
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

    

    return {
      paymentLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,

      cardData,
      clearCardData,

    }
  },
}
</script>