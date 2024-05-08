<template>
  <b-card>
    <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
    <validation-observer
      #default="{ handleSubmit }"
      ref="refFormObserver"
    >
      <!-- form -->
      <b-form 
        class="mt-2"
        enctype="multipart/form-data"
        @submit.prevent="handleSubmit(onSubmit)"
      >
        <b-row>
          <b-col sm="5">
            <b-form-checkbox
            v-model="legalPersonChecked"
            name="check-button"
            class="custom-control-dark mb-1"
            switch
            inline
            value="1"
            unchecked-value="0"
          >
            {{ $t('chat.chatContactGeneral.legalPerson') }}?
          </b-form-checkbox>
          </b-col>
          <b-col sm="5">
            <b-form-checkbox
            v-model="companyLocal.com_white_label"
            name="check-button"
            class="custom-control-primary mb-1"
            switch
            inline
            value="1"
            unchecked-value="0"
          >
            {{ $t('chat.chatContactGeneral.whiteLabel') }}?
          </b-form-checkbox>

          </b-col>
        </b-row>
        <b-row>
          <b-col sm="8">
            <!-- Name -->
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.name')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.name')+'*'"
                label-for="account-name"
              >
                <b-form-input
                  id="contact-name"
                  v-model="companyLocal.com_name"
                  :state="getValidationState(validationContext)"
                  trim
                  :placeholder="$t('user.userAccountSettingGeneral.namePlaceholder')"
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                />
                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col sm="4">
            <span
              v-if="legalPersonChecked == true || legalPersonChecked == '1' || legalPersonChecked == 1"
            >
              <!-- Cardholder CNPJ -->
              <validation-provider
                #default="validationContext"
                :name="$t('card.cardModalAddCardHandler.cnpj')"
                rules="min:18"
              >
                <b-form-group
                  :label="$t('card.cardModalAddCardHandler.cnpj')+'*'"
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
                rules="min:14"
              >
                <b-form-group
                  :label="$t('card.cardModalAddCardHandler.cpf')+'*'"
                  label-for="card-cvv"
                >
                  <b-form-input
                    id="card-cpf"
                    v-model="companyLocal.com_cpf"
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
          <b-col sm="12">
            <!-- Name -->
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.responsibleName')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.responsibleName')+'*'"
                label-for="account-name"
              >
                <b-form-input
                  id="contact-name"
                  v-model="companyLocal.com_responsible_name"
                  :state="getValidationState(validationContext)"
                  trim
                  :placeholder="$t('user.userAccountSettingGeneral.responsibleNamePlaceholder')"
                  type="text"
                  :maxlength="40"
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
          <b-col sm="6">
            <validation-provider
              #default="{ errors }"
              :name="$t('user.userAccountSettingGeneral.phoneNumber')"
              rules="required|min:12"
            >
              <b-form-group
                label-for="user-phone"
                :label="$t('user.userAccountSettingGeneral.phoneNumber')+'*'"
              >
                <!-- Phone Number -->
                <VuePhoneNumberInput  
                  v-model="companyLocal.com_phone"
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
          <b-col sm="6">

            <!-- Email -->
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.email')"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.email')+'*'"
                label-for="task-title"
              >
                <b-form-input
                  id="contact-email"
                  v-model="companyLocal.com_email"
                  :state="getValidationState(validationContext)"
                  trim
                  :placeholder="$t('user.userAccountSettingGeneral.emailPlaceholder')"
                  type="text"
                  :maxlength="70"
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
          <b-col sm="6">
            <validation-provider
              #default="{ errors }"
              :name="$t('user.userAccountSettingGeneral.financePhoneNumber')"
              rules="required|min:12"
            >
              <b-form-group
                label-for="user-phone"
                :label="$t('user.userAccountSettingGeneral.financePhoneNumber')+'*'"
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
          <b-col sm="6">

            <!-- Email -->
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.financeEmail')"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.financeEmail')+'*'"
                label-for="task-title"
              >
                <b-form-input
                  id="contact-email"
                  v-model="companyLocal.com_finance_email"
                  :state="getValidationState(validationContext)"
                  trim
                  :placeholder="$t('user.userAccountSettingGeneral.financeEmailPlaceholder')"
                  type="text"
                  :maxlength="70"
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
          <!--/ alert -->

          <b-col cols="12">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mt-2 mr-1"
              type="submit"
            >
              {{ $t('user.userAccountSettingGeneral.update') }}
            </b-button>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>
  </b-card>
</template>

<script>
import {
  BFormFile, BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BAlert, BCard, BCardText, BMedia, BMediaAside, BMediaBody, 
  BLink, BImg, BBadge, BFormRating, BFormInvalidFeedback, BFormDatepicker, VBTooltip, BAvatar, BFormCheckbox,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { ref, toRefs } from '@vue/composition-api'
import axios from '@axios'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import useCompanyGeneral from './useCompanyGeneral'
import vSelect from 'vue-select'
import VuePhoneNumberInput from 'vue-phone-number-input'
import { avatarText } from '@core/utils/filter'
import { VueMaskDirective } from 'v-mask'
import Vue from 'vue'
Vue.directive('mask', VueMaskDirective)

export default {
  components: {
    BButton,
    BForm,
    BImg,
    BFormFile,
    BFormGroup,
    BFormInput,
    BRow,
    BCol,
    BAlert,
    BCard,
    BCardText,
    BMedia,
    BMediaAside,
    BMediaBody,
    BLink,
    BBadge,
    BFormRating,
    BFormInvalidFeedback,
    vSelect,
    BFormDatepicker,
    BAvatar,
    BFormCheckbox,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    //Máscara
    VueMaskDirective,

    //Phone Input
    VuePhoneNumberInput,
  },
  directives: {
    Ripple,
    'b-tooltip': VBTooltip,
  },
  props: {
    clearUserData: {
      type: Function,
      required: false,
    },
    company: {
      type: Object,
      required: true,
    },
  },
  created() { 
    this.legalPersonFunction()
  },
  data() {
    return {
      genders: [],
      legalPersonChecked: '0',
    }
  },
  methods: {
    //Checa se o contato é pessoa física um jurídica
    legalPersonFunction() {
      if(this.company.com_cnpj) {
        this.companyLocal.legalPersonChecked = '1'
        this.legalPersonChecked = '1'
      }else {
        this.companyLocal.legalPersonChecked = '0'
        this.legalPersonChecked = '0'
      }
    },
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
    },
  },
  watch: {
    //Caso haja alguma alteração
    legalPersonChecked() {
      this.companyLocal.legalPersonChecked = this.legalPersonChecked
    }
  },
  setup(props, { emit }) {
    const refInputEl = ref(null)
    const previewEl = ref(null)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, previewEl)

    const {
      companyLocal,
      resetCompanyLocal,
      // UI
      onSubmit,
    } = useCompanyGeneral(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetCompanyLocal, props.clearContactData)

    //Cria um atributo na prop com o número do telefone sem o DDI
    /*const numberWithoutDdi = () => {
      props.contact.phoneFormatted = props.contact.phone.slice(2)
    }
    numberWithoutDdi()*/

    const uploadPhotoEmit = photoData => {
      emit('upload-photo', photoData)
    }

    return {
      refInputEl,
      previewEl,
      inputImageRenderer,

      companyLocal,
      resetCompanyLocal,
      // UI
      onSubmit,

      refFormObserver,
      getValidationState,
      clearForm,
      uploadPhotoEmit,
      avatarText,
    }
  },
}
import 'vue-phone-number-input/dist/vue-phone-number-input.css';
</script>
