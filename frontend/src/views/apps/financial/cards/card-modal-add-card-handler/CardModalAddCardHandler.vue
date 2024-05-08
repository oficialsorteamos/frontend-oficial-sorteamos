<template>
  <div>
      <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <!-- select 2 demo -->
        <b-form
          enctype="multipart/form-data"
          @submit.prevent="handleSubmit(onSubmit)"
        >
          <!-- Se não atualização de dados do titular do cartão -->
          <span
            v-if="!cardLocal.id"
          >
            <!-- Interactive Card -->
            <vue-paycard :value-fields="cardLocal" :labels="labels" />
            
            <div class="mt-3 text-center">
              <span>{{ $t('card.cardModalAddCardHandler.acceptedFlags') }}: </span>
              <span>
                <img src="@/assets/images/cards/visa.png" width="40" alt="">
              </span>
              <span>
                <img src="@/assets/images/cards/mastercard.png" width="40" alt="">
              </span>
              <span>
                <img src="@/assets/images/cards/elo.png" width="40" alt="">
              </span>
              <span>
                <img src="@/assets/images/cards/amex.png" width="40" alt="">
              </span>
              <span>
                <img src="@/assets/images/cards/hipercard.png" width="40" alt="">
              </span>
              <span>
                <img src="@/assets/images/cards/discover.png" width="40" alt="">
              </span>
            </div>

            <h5 class="mb-1 mt-3">{{ $t('card.cardModalAddCardHandler.cardData') }}</h5>

          </span>
          <!-- Offcial API -->
          <b-form-checkbox
            v-model="cardLocal.car_main"
            name="check-button"
            class="mb-1"
            switch
            inline
            value="1"
            unchecked-value="0"
          >
            {{ $t('card.cardModalAddCardHandler.useDefaultPaymentMethod') }}
          </b-form-checkbox>
          <span
            v-if="!cardLocal.id"
          >
            <b-row>
              <b-col md="6">
                <!-- Cardholder Name -->
                <validation-provider
                  #default="validationContext"
                  :name="$t('card.cardModalAddCardHandler.cardholderName')"
                  rules="required"
                >
                  <b-form-group
                    :label="$t('card.cardModalAddCardHandler.cardholderName')+'*'"
                    label-for="channel-name"
                  >
                    <b-form-input
                      :id="inputFields.cardName"
                      v-model="cardLocal.cardName"
                      :state="getValidationState(validationContext)"
                      trim
                      :placeholder="$t('card.cardModalAddCardHandler.cardholderNamePlaceholder')"
                      type="text"
                      :maxlength="100"
                      autocomplete="off"
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>

              <b-col md="6">
                <!-- Card Number -->
                <validation-provider
                  #default="validationContext"
                  :name="$t('card.cardModalAddCardHandler.cardNumber')"
                  rules="required"
                >
                  <b-form-group
                    :label="$t('card.cardModalAddCardHandler.cardNumber')+'*'"
                    label-for="card-number"
                  >
                    <b-form-input
                      :id="inputFields.cardNumber"
                      v-model="cardLocal.cardNumber"
                      :state="getValidationState(validationContext)"
                      trim
                      placeholder=""
                      type="text"
                      v-mask="'#### #### #### ####'"
                      :maxlength="120"
                      autocomplete="off"
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>
            </b-row>
            
            <b-row>
              <b-col md="3">
                <validation-provider
                  #default="{ errors }"
                  :name="$t('card.cardModalAddCardHandler.expirationDate')"
                  rules="required"
                >
                  <b-form-group
                    label="Expiration Date" 
                    label-for="vue-select"
                  >
                    <v-select
                      :id="inputFields.cardMonth"
                      v-model="cardLocal.cardMonth"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="expirationMonths"
                      transition=""
                    />
                    <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                      {{ errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>

              <b-col md="3">
                <validation-provider
                  #default="{ errors }"
                  :name="$t('card.cardModalAddCardHandler.expirationDate')"
                  rules="required"
                >
                  <b-form-group
                    :label="$t('card.cardModalAddCardHandler.expirationDate')+'*'" 
                    label-for="vue-select"
                  >
                    <v-select
                      :id="inputFields.cardYear"
                      v-model="cardLocal.cardYear"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="expirationYears"
                      transition=""
                    />
                    <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                      {{ errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>
              <b-col md="3">
                <!-- Cardholder Name -->
                <validation-provider
                  #default="validationContext"
                  :name="$t('card.cardModalAddCardHandler.cvv')"
                  rules="required"
                >
                  <b-form-group
                    :label="$t('card.cardModalAddCardHandler.cvv')+'*'"
                    label-for="card-cvv"
                  >
                    <b-form-input
                      :id="inputFields.cardCvv"
                      v-model="cardLocal.cardCvv"
                      :state="getValidationState(validationContext)"
                      trim
                      type="number"
                      :maxlength="4"
                      autocomplete="off"
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>
            </b-row>

            <hr class="mb-1"/>

            <h5 class="mb-1">{{ $t('card.cardModalAddCardHandler.holderData') }}</h5>
          </span>
          <b-form-checkbox
            v-model="corporateCardChecked"
            name="check-button"
            class="custom-control-dark mb-1"
            switch
            inline
            value="1"
            unchecked-value="0"
          >
            {{ $t('card.cardModalAddCardHandler.corporateCard') }}?
          </b-form-checkbox>
          <b-row>
            <b-col md="8">
              <!-- Holder Full Name -->
              <validation-provider
                #default="validationContext"
                :name="$t('card.cardModalAddCardHandler.fullName')"
                rules="required"
              >
                <b-form-group
                  :label="$t('card.cardModalAddCardHandler.fullName')+'*'"
                  label-for="card-cvv"
                >
                  <b-form-input
                    id="card-holderFullName"
                    v-model="cardLocal.holder_info.car_name"
                    :state="getValidationState(validationContext)"
                    trim
                    type="text"
                    :maxlength="150"
                    autocomplete="off"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>

          <b-row>
            <b-col md="8">
              <!-- Cardholder Email -->
              <validation-provider
                #default="validationContext"
                :name="$t('card.cardModalAddCardHandler.email')"
                rules="required|email"
              >
                <b-form-group
                  :label="$t('card.cardModalAddCardHandler.email')+'*'"
                  label-for="card-cvv"
                >
                  <b-form-input
                    id="card-holderFullName"
                    v-model="cardLocal.holder_info.car_email"
                    :state="getValidationState(validationContext)"
                    trim
                    type="text"
                    :maxlength="150"
                    autocomplete="off"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <b-col md="4">
              <span
                v-if="corporateCardChecked == true || corporateCardChecked == '1' || corporateCardChecked == 1"
              >
                <!-- Cardholder CNPJ -->
                <validation-provider
                  #default="validationContext"
                  :name="$t('card.cardModalAddCardHandler.cnpj')"
                  rules="required|min:18"
                >
                  <b-form-group
                    :label="$t('card.cardModalAddCardHandler.cnpj')+'*'"
                    label-for="card-cvv"
                  >
                    <b-form-input
                      id="card-cnpj"
                      v-model="cardLocal.holder_info.car_cnpj"
                      :state="getValidationState(validationContext)"
                      trim
                      type="text"
                      v-mask="'##.###.###/####-##'"
                      :maxlength="18"
                      autocomplete="off"
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </span>
              <span
                v-else
              >
                <!-- Cardholder Name -->
                <validation-provider
                  #default="validationContext"
                  :name="$t('card.cardModalAddCardHandler.cpf')"
                  rules="required|min:14"
                >
                  <b-form-group
                    :label="$t('card.cardModalAddCardHandler.cpf')+'*'"
                    label-for="card-cvv"
                  >
                    <b-form-input
                      id="card-cpf"
                      v-model="cardLocal.holder_info.car_cpf"
                      :state="getValidationState(validationContext)"
                      trim
                      type="text"
                      v-mask="'###.###.###-##'"
                      :maxlength="14"
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

          <b-row>
            <b-col md="3">
              <!-- Postal Code -->
              <validation-provider
                #default="validationContext"
                :name="$t('card.cardModalAddCardHandler.postalCode')"
              >
                <b-form-group
                  :label="$t('card.cardModalAddCardHandler.postalCode')"
                  label-for="card-number"
                  rules="required"
                >
                  <b-form-input
                    id="inputFields.cardNumber"
                    v-model="cardLocal.holder_info.car_postal_code"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    type="text"
                    v-mask="'##.###-###'"
                    :maxlength="10"
                    autocomplete="off"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="3">
              <!-- Address Number -->
              <validation-provider
                #default="validationContext"
                :name="$t('card.cardModalAddCardHandler.addressNumber')"
                rules="required"
              >
                <b-form-group
                  :label="$t('card.cardModalAddCardHandler.addressNumber')+'*'"
                  label-for="card-address-number"
                >
                  <b-form-input
                    id="addressNumber"
                    v-model="cardLocal.holder_info.car_address_number"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    type="number"
                    :maxlength="10"
                    autocomplete="off"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="3">
              <!-- Phone Number -->
              <validation-provider
                #default="validationContext"
                :name="$t('card.cardModalAddCardHandler.phoneNumber')"
                rules="required"
              >
                <b-form-group
                  :label="$t('card.cardModalAddCardHandler.phoneNumber')+'*'"
                  label-for="card-address-number"
                >
                  <b-form-input
                    id="phoneNumber"
                    v-model="cardLocal.holder_info.car_phone"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    type="text"
                    v-mask="'(##) #####-####'"
                    :maxlength="15"
                    autocomplete="off"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
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
              {{ cardLocal.id ? $t('card.cardModalAddCardHandler.update') : $t('card.cardModalAddCardHandler.add') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormDatepicker, BFormCheckbox, BRow, BCol,
} from 'bootstrap-vue'
import store from '@/store'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs, ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCardModalAddCardHandler from './useCardModalAddCardHandler'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { VueMaskDirective } from 'v-mask'
import { VuePaycard } from "vue-paycard"
import Vue from 'vue'
Vue.directive('mask', VueMaskDirective)


export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    BFormInvalidFeedback,
    BFormDatepicker,
    BFormCheckbox,
    BRow,
    BCol,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    VuePaycard,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    card: {
      type: Object,
      required: true,
    },
    clearContactData: {
      type: Function,
      required: false,
    },
  },
  data() {
    return {
      corporateCardChecked: '0', 
      inputFields: {
        cardNumber: 'v-card-number',
        cardName: 'v-card-name',
        cardMonth: 'v-card-month',
        cardYear: 'v-card-year',
        cardCvv: 'v-card-cvv'
      },
      labels : {
        cardName : "NOME NO CARTÃO",
        cardHolder : "Nome Titular",
        cardMonth : "MM",
        cardYear : "AA",
        cardExpires : "Expira",
        cardCvv : "CVV",
      },
      expirationMonths:[ '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
      expirationYears:[ '2022', '2023', '2024', '2025', '2026', '2027', '2028', '2029', '2030', '2031', '2032', '2033', '2034', '2035', '2036'],
    }
  },
  methods: {
    //Checa se o cartão é corporativo. Se for, marca o checkbox
    corporateCardCheck() {
      if(this.card.holder_info.car_cnpj) {
        this.cardLocal.corporateCard = '1'
        this.corporateCardChecked = '1'
      }else {
        this.cardLocal.corporateCard = '0'
        this.corporateCardChecked = '0'
      }
    },
  },
  created() {
    this.corporateCardCheck()
  },
  watch: {
    //Caso haja alguma alteração
    corporateCardChecked() {
      this.cardLocal.corporateCard = this.corporateCardChecked
    }
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
      cardLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCardModalAddCardHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)


    return {
      // Add New Contact
      cardLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}
</script>