<template>
  <b-card>
    <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
    <validation-observer
      #default="{ handleSubmit }"
      ref="refFormObserver"
    >
      <!-- form -->
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="handleSubmit(onSubmit)"
      >
        <b-row>
          <b-col
            cols="12"
            class="mb-2"
          >
            <h5 class="mb-0">
              {{ $t('user.userModalHandler.address') }}
            </h5>
            <small class="text-muted">{{ $t('chat.chatContactAddress.contactAddress') }}</small>
          </b-col>
          <b-col md="4">
            <input
              type="hidden"
              id="contactId"
              v-bind:value="companyLocal.id = company.id"
            />
            <input
              type="hidden"
              id="contactId"
              v-bind:value="companyLocal.userId = contactId"
            />
            <input
              type="hidden"
              id="typeUserId"
              v-bind:value="companyLocal.typeUserId = 2"
            />
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
                label-for="address-district"
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

          <!-- buttons -->
          <b-col cols="12">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mt-1 mr-1"
              type="submit"
            >
              {{ $t('user.userAccountSettingPassword.update') }}
            </b-button>
          </b-col>
          <!--/ buttons -->
        </b-row>
      </b-form>
    </validation-observer>
  </b-card>
</template>

<script>
import {
  BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BInputGroup, BInputGroupAppend, BFormInvalidFeedback,
  BSpinner,
} from 'bootstrap-vue'
import axios from '@axios'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import vSelect from 'vue-select'
import formValidation from '@core/comp-functions/forms/form-validation'
import useCompanyAddress from './useCompanyAddress'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'

export default {
  components: {
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BRow,
    BCol,
    BCard,
    BInputGroup,
    BInputGroupAppend,
    BFormInvalidFeedback,
    BSpinner,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    vSelect,
  },
  directives: {
    Ripple,
  },
  props: {
    clearUserData: {
      type: Function,
      required: true,
    },
    company: {
      type: Object,
      required: false,
    },
    contactId: {
      type: Number,
      required: false,
    },
    addressId: {
      type: Number,
      required: false,
    },
    loading : {
      type: Boolean,
      required: true,
    }
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      states: [],
      countries: [],
    }
  },
   methods: {
    getAddressUser(cep) {
      console.log(cep)
      //Fecha o Modal
      this.$emit('get-address-user', cep)
    },
  },
  created() {
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
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
  },
  setup(props, {emit}) {

    const {
      companyLocal,
      resetContactLocal,
      // UI
      onSubmit,
    } = useCompanyAddress(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
    } = formValidation(resetContactLocal, props.clearContactData)

    return {
      companyLocal,
      resetContactLocal,
      // UI
      onSubmit,

      refFormObserver,
      getValidationState,
    }
  }
}
</script>
