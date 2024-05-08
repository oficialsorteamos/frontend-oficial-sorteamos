<template>
  <div>
    <form-wizard
      color="#7367F0"
      :title="null"
      :subtitle="null"
      :next-button-text="$t('user.userModalHandler.next')"
      :finish-button-text="$t('user.userModalHandler.submit')"
      :back-button-text="$t('user.userModalHandler.previous')"
      class="steps-transparent mb-3"
      @on-complete="onSubmit"
    >
      
      <!-- personal details -->
      <tab-content
        :title="$t('user.userModalHandler.personalInfo')"
        icon="feather icon-user"
        :before-change="validationFormInfo"
      >
        <validation-observer
          ref="infoRules"
          tag="form"
        >
          <b-row
            v-if="!isWhiteLabel"
          >
            <b-col
              cols="12"
              class="mb-2"
            >
              <h5 class="mb-0">
                {{ $t('user.userModalHandler.personalInfo') }}
              </h5>
              <small class="text-muted">{{ $t('user.userModalHandler.personalInfoSubtitle') }}</small>
            </b-col>

            <b-col md="12">
              <b-form-checkbox
                v-model="naturalPersonChecked"
                name="check-button"
                class="custom-control-dark mb-1"
                switch
                inline
                value="1"
                unchecked-value="0"
              >
                {{ $t('partner.partnerModalHandler.naturalPerson') }}?
              </b-form-checkbox>
            </b-col>
          </b-row>
          <b-row
            v-if="!isWhiteLabel"
          >
            <!-- Type Partner -->
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('administrationCompany.companyModalHandler.partner')"
              >
                <b-form-group
                  :label="$t('administrationCompany.companyModalHandler.partner')"
                  label-for="vue-select"
                  :state="errors.length > 0 ? false:null"
                >
                  <v-select
                    id="vue-select"
                    v-model="companyLocal.partner"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="partners"
                    :getOptionLabel="partners => partners.par_corporate_name"
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
            <!-- CPF -->
            <b-col md="4">
              <span
                v-if="naturalPersonChecked == false || naturalPersonChecked == '0' || naturalPersonChecked == 0"
              >
                <!-- Cardholder CNPJ -->
                <validation-provider
                  #default="validationContext"
                  :name="$t('partner.partnerModalHandler.cnpj')"
                  rules="required|min:18"
                >
                  <b-form-group
                    :label="$t('partner.partnerModalHandler.cnpj')+'*'"
                    label-for="card-cvv"
                  >
                    <b-form-input
                      id="card-cnpj"
                      v-model="companyLocal.com_cnpj"
                      :state="getValidationState(validationContext)"
                      trim
                      type="text"
                      v-mask="'##.###.###/####-##'"
                      :maxlength="18"
                      autocomplete="off"
                      :disabled="isWhiteLabel == 1? true : false"
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
                <validation-provider
                  #default="validationContext"
                  :name="$t('partner.partnerModalHandler.cpf')"
                  rules="required|min:14"
                >
                  <b-form-group
                    label-for="user-cpf"
                    :label="$t('partner.partnerModalHandler.cpf')+'*'"
                  >
                    <b-form-input
                      v-model="companyLocal.com_cpf"
                      id="user-name"
                      :state="getValidationState(validationContext)"
                      trim
                      :placeholder="$t('partner.partnerModalHandler.cpfPlaceholder')"
                      type="text"
                      v-mask="'###.###.###-##'"
                      autocomplete="off"
                      :maxlength="14"
                      :disabled="isWhiteLabel == 1? true : false"
                    />
                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </span>
            </b-col>
            
            <!-- Name -->
            <b-col md="8">
              <validation-provider
                #default="validationContext"
                :name="$t('partner.partnerModalHandler.name')"
                rules="required|min:3"
              >
                <b-form-group
                  label-for="user-name"
                  :label="$t('partner.partnerModalHandler.name')+'*'"
                >
                  <b-form-input
                    v-model="companyLocal.com_name"
                    id="user-name"
                    :state="getValidationState(validationContext)"
                    trim
                    type="text"
                    autocomplete="off"
                    :disabled="isWhiteLabel == 1? true : false"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Name -->
            <b-col md="12"
              v-if="naturalPersonChecked == 0"
            >
              <validation-provider
                #default="validationContext"
                :name="$t('partner.partnerModalHandler.responsibleName')"
                rules="required|min:3"
              >
                <b-form-group
                  label-for="user-name"
                  :label="$t('partner.partnerModalHandler.responsibleName')+'*'"
                >
                  <b-form-input
                    v-model="companyLocal.com_responsible_name"
                    id="user-name"
                    :state="getValidationState(validationContext)"
                    trim
                    type="text"
                    autocomplete="off"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Birthday -->
            <b-col md="4"
              v-if="naturalPersonChecked == 1"
            >
              <validation-provider
                #default="validationContext"
                :name="$t('partner.partnerModalHandler.birthday')"
                rules="required|min:10"
              >
                <b-form-group
                  :label="$t('partner.partnerModalHandler.birthday')"
                  label-for="contact-birthday"
                >
                  <b-form-input
                    id="contact-birthday"
                    v-model="companyLocal.com_birthday"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-mask="'##/##/####'"
                    :maxlength="10"
                    autocomplete="off"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Gender -->
            <b-col md="4"
              v-if="naturalPersonChecked == 1"
            >
              <validation-provider
                #default="{ errors }"
                :name="$t('partner.partnerModalHandler.gender')"
                rules="required"
              >
                <b-form-group
                  :label="$t('partner.partnerModalHandler.gender')+'*'"
                  label-for="vue-select"
                  :state="errors.length > 0 ? false:null"
                >
                  <v-select
                    id="vue-select"
                    v-model="companyLocal.gender"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="genders"
                    :getOptionLabel="genders => genders.gen_description"
                    transition=""
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Responsible Phone number -->
            <b-col md="6">
              <input
                type="hidden"
                id="phoneNumber"
                v-bind:value="companyLocal.phoneNumber = phoneNumber"
              />
              <validation-provider
                #default="{ errors }"
                :name="$t('partner.partnerModalHandler.responsiblePhone')"
                rules="required|min:12"
              >
                <b-form-group
                  label-for="user-phone"
                  :label="$t('partner.partnerModalHandler.responsiblePhone')+'*'"
                >
                  <!-- Phone Number -->
                  <VuePhoneNumberInput  
                    v-model="companyLocal.com_responsible_phone"
                    :required="true"
                    class="mb-1"
                    @update="setPhoneNumber"
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Responsible Email -->
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                :name="$t('partner.partnerModalHandler.responsibleEmail')"
                rules="required|email"
              >
                <b-form-group
                  label-for="user-email"
                  :label="$t('partner.partnerModalHandler.responsibleEmail')+'*'"
                >
                  <b-form-input
                    v-model="companyLocal.com_responsible_email"
                    id="user-email"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder="example@email.com"
                    type="text"
                    autocomplete="off"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Finance Phone number -->
            <b-col md="6">
              <input
                type="hidden"
                id="phoneNumber"
                v-bind:value="companyLocal.financialPhoneNumber = phoneNumber"
              />
              <validation-provider
                #default="{ errors }"
                :name="$t('partner.partnerModalHandler.financePhoneNumber')"
                :rules="companyLocal.partner? 'required|min:12' : 'min:12'"
              >
                <b-form-group
                  label-for="user-phone"
                  :label="$t('partner.partnerModalHandler.financePhoneNumber')"
                >
                  <!-- Phone Number -->
                  <VuePhoneNumberInput  
                    v-model="companyLocal.com_finance_phone"
                    :required="true"
                    class="mb-1"
                    @update="setPhoneNumber"
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Email -->
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                :name="$t('partner.partnerModalHandler.financeEmail')"
                :rules="companyLocal.partner? 'required|email' : 'email'"
              >
                <b-form-group
                  label-for="user-email"
                  :label="$t('partner.partnerModalHandler.financeEmail')"
                >
                  <b-form-input
                    v-model="companyLocal.com_finance_email"
                    id="user-email"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder="example@email.com"
                    type="text"
                    autocomplete="off"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- URL -->
            <b-col md="12">
              <validation-provider
                #default="validationContext"
                :name="$t('partner.partnerModalHandler.name')"
                rules="min:5"
              >
                <b-form-group
                  label-for="user-name"
                  :label="$t('administrationCompany.companyModalHandler.url')"
                >
                  <b-form-input
                    v-model="companyLocal.com_url"
                    id="user-name"
                    :state="getValidationState(validationContext)"
                    trim
                    type="text"
                    autocomplete="off"
                    :disabled="isWhiteLabel == 1? true : false"
                  />
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
        :title="$t('user.userModalHandler.address')"
        icon="feather icon-map-pin"
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
                {{ $t('user.userModalHandler.address') }}
              </h5>
              <small class="text-muted">{{ $t('user.userModalHandler.addressSubtitle') }}</small>
            </b-col>
            <b-col md="3">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.cep')"
                rules="required|min:9"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.cep')"
                  label-for="task-title"
                >
                  <b-form-input
                    id="address-cep"
                    v-model="companyLocal.com_postal_code"
                    autofocus
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-mask="'#####-####'"
                    :maxlength="9"
                    @keyup="getAddressUser(companyLocal.com_postal_code)"
                    autocomplete="off"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col
              cols="12"
              xl="4"
              lg="2"
              md="5"
            >
              <div
                class="mt-2"
              >
                <b-spinner 
                  :label="$t('user.userModalHandler.loading')" 
                  v-show="loading"
                />
              </div>
            </b-col>
            <b-col md="10">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.street')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.street')"
                  label-for="address-street"
                >
                  <b-form-input
                    id="address-street"
                    v-model="companyLocal.com_address"
                    :state="getValidationState(validationContext)"
                    trim
                    :placeholder="$t('user.userModalHandler.streetPlaceholder')"
                    v-bind:value="companyLocal.com_address = company.com_address"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="2">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.number')"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.number')"
                  label-for="address-number"
                >
                  <b-form-input
                    id="address-number"
                    v-model="companyLocal.com_address_number"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-bind:value="companyLocal.com_address_number = company.com_address_number"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="12">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.addressComplement')"
                rules=""
              >
                <b-form-group
                  :label="$t('user.userModalHandler.addressComplement')"
                  label-for="address-complement"
                >
                  <b-form-input
                    id="address-complement"
                    v-model="companyLocal.com_complement"
                    :state="getValidationState(validationContext)"
                    trim
                    :placeholder="$t('user.userModalHandler.addressComplementPlaceholder')"
                    v-bind:value="companyLocal.com_complement = company.com_complement"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.district')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.district')"
                  label-for="address-district"
                >
                  <b-form-input
                    id="address-district"
                    v-model="companyLocal.com_province"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-bind:value="companyLocal.com_province = company.com_province"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.city')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.city')"
                  label-for="address-city"
                >
                  <b-form-input
                    id="address-city"
                    v-model="companyLocal.com_city"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-bind:value="companyLocal.com_city = company.com_city"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.state')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.state')"
                  label-for="address-district"
                >
                  <b-form-input
                    id="address-state"
                    v-model="companyLocal.com_state"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-bind:value="companyLocal.com_state = company.com_state"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.country')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.country')"
                  label-for="address-district"
                >
                  <b-form-input
                    id="address-country"
                    v-model="companyLocal.com_country"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-bind:value="companyLocal.com_country = company.com_country"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            
          </b-row>
        </validation-observer>
      </tab-content>
    </form-wizard>
  </div>
</template>

<script>
import {
  BRow, BCol, BFormGroup, BFormInput, BFormInvalidFeedback, BFormDatepicker, BSpinner, BInputGroupAppend, BInputGroup,
  BFormCheckbox,
} from 'bootstrap-vue'
import axios from '@axios'
import { FormWizard, TabContent } from 'vue-form-wizard'
import vSelect from 'vue-select'
import { toRefs, ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCompanyModalHandler from './useCompanyModalHandler'
import { required } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import VuePhoneNumberInput from 'vue-phone-number-input'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'
import { codeIconInfo } from './code'
import { VueMaskDirective } from 'v-mask'
import Vue from 'vue'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'
Vue.directive('mask', VueMaskDirective)


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
    vSelect,
    BFormCheckbox,
    // eslint-disable-next-line vue/no-unused-components
    ToastificationContent,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
    BInputGroupAppend,
    BInputGroup,

    //Máscara
    VueMaskDirective,

    //Phone Input
    VuePhoneNumberInput,
  },
  props: {
    company: {
      type: Object,
      required: true,
    },
    isWhiteLabel: {
      type: Number,
      required: true,
    },
    clearContactData: {
      type: Function,
      required: true,
    },
    loading : {
      type: Boolean,
      required: true,
    }
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      phoneNumber: '',

      selectedContry: 'select_value',
      selectedLanguage: 'nothing_selected',
      codeIconInfo,
      countryName: [
        { value: 'select_value', text: 'Select Value' },
        { value: 'Russia', text: 'Russia' },
        { value: 'Canada', text: 'Canada' },
        { value: 'China', text: 'China' },
        { value: 'United States', text: 'United States' },
        { value: 'Brazil', text: 'Brazil' },
        { value: 'Australia', text: 'Australia' },
        { value: 'India', text: 'India' },
      ],
      languageName: [
        { value: 'nothing_selected', text: 'Nothing Selected' },
        { value: 'English', text: 'English' },
        { value: 'Chinese', text: 'Mandarin Chinese' },
        { value: 'Hindi', text: 'Hindi' },
        { value: 'Spanish', text: 'Spanish' },
        { value: 'Arabic', text: 'Arabic' },
        { value: 'Malay', text: 'Malay' },
        { value: 'Russian', text: 'Russian' },
      ],
      genders: [],
      states: [],
      countries: [],
      naturalPersonChecked: '0',
      partners: [],
    }
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
  },
  created() { 
    //Traz os gêneros cadastrados
    axios
      .get('/api/system/gender/fetch-genders/')
      .then(response => {
        this.genders = response.data
      });
      //Traz os Estados
    axios
      .get('/api/system/state/fetch-states/')
      .then(response => {
        this.states = response.data
      });
    
    //Traz os países
    axios
      .get('/api/system/country/fetch-countries/')
      .then(response => {
        this.countries = response.data
      });

    //Traz os parceiros cadastrados
    axios
      .get('/api/administration/partner/get-partners-by-status/A')
      .then(response => {
        console.log('partners')
        console.log(response.data)
        this.partners = response.data.partners
      });
  },
  methods: {
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
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
    getAddressUser(cep) {
      console.log(cep)
      //Fecha o Modal
      this.$emit('get-address-user', cep)
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
      companyLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCompanyModalHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)

    

    return {
      companyLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,

    }
  },
}

import 'vue-phone-number-input/dist/vue-phone-number-input.css';
</script>