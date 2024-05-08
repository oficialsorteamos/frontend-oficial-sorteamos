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
        <input
          type="hidden"
          id="campaignId"
          v-bind:value="settingsLocal.plan.updateCompanyServer = true"
        />
        <b-row>
          <b-col sm="4">
              <!-- Name -->
              <validation-provider
                #default="validationContext"
                :name="$t('administrationCompany.companySettingsPlan.users')"
                rules="required"
              >
                <b-form-group
                  :label="$t('administrationCompany.companySettingsPlan.users')"
                  label-for="account-name"
                >
                  <b-form-input
                    id="plan-total-user"
                    v-model="settingsLocal.plan.com_total_users"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
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
              <!-- Name -->
              <validation-provider
                #default="validationContext"
                :name="$t('administrationCompany.companySettingsPlan.officialChannels')"
                rules="required"
              >
                <b-form-group
                  :label="$t('administrationCompany.companySettingsPlan.officialChannels')"
                  label-for="account-name"
                >
                  <b-form-input
                    id="plan-total-user"
                    v-model="settingsLocal.plan.com_total_official_channels"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
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
              <!-- Name -->
              <validation-provider
                #default="validationContext"
                :name="$t('administrationCompany.companySettingsPlan.unofficialChannels')"
                rules="required"
              >
                <b-form-group
                  :label="$t('administrationCompany.companySettingsPlan.unofficialChannels')"
                  label-for="account-name"
                >
                  <b-form-input
                    id="plan-total-user"
                    v-model="settingsLocal.plan.com_total_unofficial_channels"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
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
           
          <!--/ alert -->

          <b-col cols="12">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mt-2 mr-1"
              type="submit"
            >
              {{ $t('voip.voipSettingAuthentication.update') }}
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
  BLink, BImg, BBadge, BFormRating, BFormInvalidFeedback, VBTooltip,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { ref, toRefs } from '@vue/composition-api'
import axios from '@axios'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import useCompanySettingPlan from './useCompanySettingPlan'
import vSelect from 'vue-select'

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

    // Form Validation
    ValidationProvider,
    ValidationObserver,

  },
  directives: {
    Ripple,
    'b-tooltip': VBTooltip,
  },
  props: {
    generalData: {
      type: Object,
      default: () => {},
    },
    company: {
      //type: Array,
      required: true,
    },
  },
  data() {
    return { 
      companySystem: [],
      companyStatus: [],
    }
  },
  created() {
    axios
      .get('/api/settings/company/get-company')
      .then(response => {
        this.companySystem = response.data
      });

    axios
      .get('/api/administration/company/fetch-company-status/A')
      .then(response => {
        this.companyStatus = response.data.companyStatus
        //Se o sistema de gestão for o da Devsky
        if(this.companySystem && this.companySystem[0].com_white_label == 0) {
          //Remove a opção de bloqueio via Parceiro White Label
          this.companyStatus.pop()
        } //Se for o sistema de gestão de um White Label
        else {
          //Remove a opção de bloqueio via Devsky
          this.companyStatus.splice(this.companyStatus.findIndex(function(i){
            return i.id == 4;
          }), 1);
        }
        
      });
  },
  methods: {
    resetForm() {
      this.optionsLocal = JSON.parse(JSON.stringify(this.generalData))
    },
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
    },
  },
  setup(props, { emit }) {
    const refInputEl = ref(null)
    const previewEl = ref(null)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, previewEl)

    const {
      settingsLocal,
      resetUserLocal,
      // UI
      onSubmit,
    } = useCompanySettingPlan(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetUserLocal, props.clearContactData)

    return {
      refInputEl,
      previewEl,
      inputImageRenderer,

      settingsLocal,
      resetUserLocal,
      // UI
      onSubmit,

      refFormObserver,
      getValidationState,
      clearForm,
    }
  },
}

import 'vue-phone-number-input/dist/vue-phone-number-input.css';
</script>
