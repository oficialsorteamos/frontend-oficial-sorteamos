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
          <!-- Type Partner -->
          <b-col sm="4">
            <!-- Name -->
            <validation-provider
              #default="validationContext"
              :name="$t('administrationCompany.companySettingsFee.monthlyFixedFee')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationCompany.companySettingsFee.monthlyFixedFee')"
                label-for="account-name"
              >
                <money
                  id="plan-total-user"
                  v-model="settingsLocal.fees[1]"
                  class="form-control"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                  v-bind="money"
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
              :name="$t('administrationCompany.companySettingsFee.feePerUser')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationCompany.companySettingsFee.feePerUser')"
                label-for="account-name"
              >
                <money
                  id="plan-total-user"
                  v-model="settingsLocal.fees[2]"
                  class="form-control"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                  v-bind="money"
                />
                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col sm="4">
            <!-- Fee Official Channel -->
            <validation-provider
              #default="validationContext"
              :name="$t('administrationCompany.companySettingsFee.feePerOfficialChannel')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationCompany.companySettingsFee.feePerOfficialChannel')"
                label-for="account-name"
              >
                <money
                  id="plan-total-user"
                  v-model="settingsLocal.fees[3]"
                  class="form-control"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                  v-bind="money"
                />
                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col sm="4">
            <!-- Fee Unofficial Channel -->
            <validation-provider
              #default="validationContext"
              :name="$t('administrationCompany.companySettingsFee.feePerUnofficialChannel')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationCompany.companySettingsFee.feePerUnofficialChannel')"
                label-for="account-name"
              >
                <money
                  id="plan-total-user"
                  v-model="settingsLocal.fees[4]"
                  class="form-control"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                  v-bind="money"
                />
                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col sm="4">
            <!-- Fee Unofficial Channel -->
            <validation-provider
              #default="validationContext"
              :name="$t('administrationCompany.companySettingsFee.feePerSendingWhatsappMessageCampaign')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationCompany.companySettingsFee.feePerSendingWhatsappMessageCampaign')"
                label-for="account-name"
              >
                <money
                  id="plan-total-user"
                  v-model="settingsLocal.fees[5]"
                  class="form-control"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                  v-bind="money"
                />
                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col sm="4">
            <!-- Fee Unofficial Channel -->
            <validation-provider
              #default="validationContext"
              :name="$t('administrationCompany.companySettingsFee.feePerSendingSmsCampaign')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationCompany.companySettingsFee.feePerSendingSmsCampaign')"
                label-for="account-name"
              >
                <money
                  id="plan-total-user"
                  v-model="settingsLocal.fees[6]"
                  class="form-control"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                  v-bind="money"
                />
                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col sm="4">
            <!-- Fee Unofficial Channel -->
            <validation-provider
              #default="validationContext"
              :name="$t('administrationCompany.companySettingsFee.feePerReturnSmsCampaign')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationCompany.companySettingsFee.feePerReturnSmsCampaign')"
                label-for="account-name"
              >
                <money
                  id="plan-total-user"
                  v-model="settingsLocal.fees[7]"
                  class="form-control"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                  v-bind="money"
                />
                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col sm="4">
            <!-- Fee Unofficial Channel -->
            <validation-provider
              #default="validationContext"
              :name="$t('administrationCompany.companySettingsFee.feePerWhatsappCallCampaign')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationCompany.companySettingsFee.feePerWhatsappCallCampaign')"
                label-for="account-name"
              >
                <money
                  id="plan-total-user"
                  v-model="settingsLocal.fees[8]"
                  class="form-control"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                  v-bind="money"
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
import money from 'v-money'
import formValidation from '@core/comp-functions/forms/form-validation'
import useCompanySettingFee from './useCompanySettingFee'
import vSelect from 'vue-select'
import Vue from 'vue'
Vue.use(money, {precision: 2})

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
        if(this.companySystem && this.companySystem[0].com_cnpj == '47.232.185/0001-23') {
          //Remove a opção de bloqueio via Parceiro White Label
          this.companyStatus.pop()
        } //Se for o sistema de gestão de um White Label
        else {
          //Remove a opção de bloqueio via Devsky
          this.companyStatus.splice(this.companyStatus.findIndex(function(i){
            return i.id === 4;
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
    } = useCompanySettingFee(toRefs(props), emit)

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
