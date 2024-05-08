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
          <b-row>
            <b-col
              cols="12"
              class="mb-2"
            >
              <h5 class="mb-0">
                {{ $t('user.userModalHandler.personalInfo') }}
              </h5>
              <small class="text-muted">{{ $t('user.userModalHandler.personalInfoSubtitle') }}</small>
            </b-col>

            <!-- CPF -->
            <b-col md="4">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.cpf')"
                rules="required|min:14"
              >
                <b-form-group
                  label-for="user-cpf"
                  :label="$t('user.userModalHandler.cpf')+'*'"
                >
                  <b-form-input
                    v-model="userLocal.cpf"
                    id="user-name"
                    :state="getValidationState(validationContext)"
                    trim
                    :placeholder="$t('user.userModalHandler.cpfPlaceholder')"
                    type="text"
                    v-mask="'###.###.###-##'"
                    autocomplete="off"
                    :maxlength="14"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            
            <!-- Name -->
            <b-col md="8">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.name')"
                rules="required|min:3"
              >
                <b-form-group
                  label-for="user-name"
                  :label="$t('user.userModalHandler.name')+'*'"
                >
                  <b-form-input
                    v-model="userLocal.name"
                    id="user-name"
                    :state="getValidationState(validationContext)"
                    trim
                    :placeholder="$t('user.userModalHandler.namePlaceholder')"
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
            <!--
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.birthday')"
                rules="required|min:3"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.birthday')+'*'"
                  label-for="vue-select"
                >
                  <b-form-datepicker
                    v-model="userLocal.birthday"
                    id="contact-birthday"
                    :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
                    locale="pt-br"
                    :state="getValidationState(validationContext)"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>  
            </b-col>
            -->
            <!-- Birthday -->
            <b-col md="4">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.birthday')"
                rules="required|min:10"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.birthday')"
                  label-for="contact-birthday"
                >
                  <b-form-input
                    id="contact-birthday"
                    v-model="userLocal.birthday"
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
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userModalHandler.gender')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.gender')+'*'"
                  label-for="vue-select"
                  :state="errors.length > 0 ? false:null"
                >
                  <v-select
                    id="vue-select"
                    v-model="userLocal.gender"
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

            <!-- Phone number -->
            <b-col md="6">
              <input
                type="hidden"
                id="phoneNumber"
                v-bind:value="userLocal.phoneNumber = phoneNumber"
              />
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userModalHandler.phoneNumber')"
                rules="required|min:12"
              >
                <b-form-group
                  label-for="user-phone"
                  :label="$t('user.userModalHandler.phoneNumber')+'*'"
                >
                  <!-- Phone Number -->
                  <VuePhoneNumberInput  
                    v-model="userLocal.phone_number"
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
                :name="$t('user.userModalHandler.email')"
                rules="required|email"
              >
                <b-form-group
                  label-for="user-email"
                  :label="$t('user.userModalHandler.email')+'*'"
                >
                  <b-form-input
                    v-model="userLocal.email"
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
                    v-model="userLocal.cep"
                    autofocus
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-mask="'#####-####'"
                    :maxlength="9"
                    @keyup="getAddressUser(userLocal.cep)"
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
                    v-model="userLocal.street"
                    :state="getValidationState(validationContext)"
                    trim
                    :placeholder="$t('user.userModalHandler.streetPlaceholder')"
                    v-bind:value="userLocal.street = user.street"
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
                    v-model="userLocal.number"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-bind:value="userLocal.number = user.number"
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
                    v-model="userLocal.address_complement"
                    :state="getValidationState(validationContext)"
                    trim
                    :placeholder="$t('user.userModalHandler.addressComplementPlaceholder')"
                    v-bind:value="userLocal.address_complement = user.address_complement"
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
                    v-model="userLocal.district"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-bind:value="userLocal.district = user.district"
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
                    v-model="userLocal.city"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    v-bind:value="userLocal.city = user.city"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userModalHandler.state')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.state')"
                  label-for="vue-select"
                >
                  <v-select
                    id="vue-select"
                    v-model="userLocal.state"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="states"
                    :getOptionLabel="states => states.sta_uf"
                    v-bind:value="userLocal.state = user.state"
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userModalHandler.country')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.country')"
                  label-for="vue-select"
                >
                  <v-select
                    id="vue-select"
                    v-model="userLocal.country"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="countries"
                    :getOptionLabel="countries => countries.cou_name"
                    v-bind:value="userLocal.country = user.country"
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
        </validation-observer>
      </tab-content>
      
      <!-- account detail tab -->
      <tab-content
        :title="$t('user.userModalHandler.accountDetails')"
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
                {{ $t('user.userModalHandler.accountDetails') }}
              </h5>
              <small class="text-muted">
                {{ $t('user.userModalHandler.accountDetailsSubtitle') }}
              </small>
            </b-col>
            <!-- Departamentos -->
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userModalHandler.departments')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.departments')" 
                  label-for="vue-select"
                >
                  <v-select
                    id="vue-select"
                    v-model="userLocal.department"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="departments"
                    :getOptionLabel="departments => departments.dep_name"
                    transition=""
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Perfis -->
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userModalHandler.roles')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.roles')"
                  label-for="vue-select"
                >
                  <v-select
                    id="vue-select"
                    v-model="userLocal.role"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="roles"
                    :getOptionLabel="roles => roles.rol_name"
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
        </validation-observer>
      </tab-content>

      <!-- Access detail tab -->
      <tab-content
        :title="$t('user.userModalHandler.accessDetails')"
        icon="feather icon-lock"
        :before-change="validationForm"
      >
        <validation-observer
          ref="accountRules"
          tag="form"
        >
          <b-row>
            <b-col
              cols="12"
              class="mb-2"
            >
              <h5 class="mb-0">
                {{ $t('user.userModalHandler.accessDetails') }}
              </h5>
              <small class="text-muted">
                {{ $t('user.userModalHandler.accessDetailsSubtitle') }}
              </small>
            </b-col>
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalHandler.username')"
                rules="required|min:5"
              >
                <b-form-group
                  :label="$t('user.userModalHandler.username')"
                  label-for="user-username"
                >
                  <b-form-input
                    id="user-username"
                    v-model="userLocal.username"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="6">
            </b-col>
            <b-col md="6">
              <b-form-group
                :label="$t('user.userModalHandler.password')"
                label-for="password"
              >
                <validation-provider
                  #default="{ errors }"
                  :name="$t('user.userModalHandler.password')"
                  rules="required|password"
                >
                  <b-input-group
                    class="input-group-merge"
                  >
                    <b-form-input
                      id="address-password"
                      v-model="userLocal.password"
                      :state="errors.length > 0 ? false : null"
                      class="form-control-merge"
                      trim
                      placeholder=""
                      :type="passwordFieldType"
                    />
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
            <b-col md="6">
              <b-form-group
                :label="$t('user.userModalHandler.confirmPassword')"
                label-for="confirm-password"
              >
                <validation-provider
                  #default="{ errors }"
                  :name="$t('user.userModalHandler.confirmPassword')"
                  rules="required|confirmed:Senha"
                >
                  <b-input-group
                    class="input-group-merge"
                  >
                    <b-form-input
                      id="confirm-password"
                      v-model="userLocal.confirm_password"
                      :state="errors.length > 0 ? false : null"
                      class="form-control-merge"
                      trim
                      placeholder=""
                      :type="passwordFieldType"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="passwordToggleIcon"
                        @click="togglePasswordVisibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
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
} from 'bootstrap-vue'
import axios from '@axios'
import { FormWizard, TabContent } from 'vue-form-wizard'
import vSelect from 'vue-select'
import { toRefs, ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useUserModalHandler from './useUserModalHandler'
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
    user: {
      type: Object,
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
      departments: [],
      roles: [],
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
    
    //Traz os departamentos cadastrados
    axios
      .get('/api/management/department/fetch-departments/')
      .then(response => {
        this.departments = response.data.departments
      });
    
    //Traz os perfis cadastrados
    axios
      .get('/api/system/role/fetch-roles/')
      .then(response => {
        this.roles = response.data.roles
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
      userLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useUserModalHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)

    

    return {
      userLocal,
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