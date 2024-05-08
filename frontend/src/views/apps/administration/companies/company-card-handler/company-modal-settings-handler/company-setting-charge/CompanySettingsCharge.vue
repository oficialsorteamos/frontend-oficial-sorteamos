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
        <b-card
          class="card-transaction"
          no-body
        >
          <b-card-body>
            <!-- Monthly Fee -->
            <b-row>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[1]"
                  name="check-button"
                  class="custom-control-success"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.monthlyFee') }}
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                v-if="parameterLocal.charges[1] == 1"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[2]"
                  name="check-button"
                  class="custom-control-success"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.proportionalCharge') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- User Charge -->
            <b-row
              class="mt-2"
            >
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[3]"
                  name="check-button"
                  class="custom-control-info"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.chargeAdditionalUser') }}
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                v-if="parameterLocal.charges[3] == 1"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[4]"
                  name="check-button"
                  class="custom-control-info"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.proportionalCharge') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- Official Channel Charge -->
            <b-row
              class="mt-2"
            >
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[5]"
                  name="check-button"
                  class="custom-control-primary"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.chargeAdditionalOfficialChannel') }}
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                v-if="parameterLocal.charges[5] == 1"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[6]"
                  name="check-button"
                  class="custom-control-primary"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.proportionalCharge') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- Unofficial Channel Charge -->
            <b-row
              class="mt-2"
            >
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[7]"
                  name="check-button"
                  class="custom-control-dark"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.chargeAdditionalUnofficialChannel') }}
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                v-if="parameterLocal.charges[7]== 1"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[8]"
                  name="check-button"
                  class="custom-control-dark"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.proportionalCharge') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <b-row
              class="mt-2"
            >
              <b-col
                cols="12"
                xl="12"
                lg="12"
                md="12"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[9]"
                  name="check-button"
                  class="custom-control-warning"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.chargeSendingCampaignMessage') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- Se for uma cobrança de envio de SMS -->
            <b-row
              class="mt-2"
            >
              <b-col
                cols="12"
                xl="12"
                lg="12"
                md="12"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[10]"
                  name="check-button"
                  class="custom-control-warning"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.chargeSendingCampaignSmsMessage') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- Se for uma cobrança de retorno do contato por SMS -->  
            <b-row
              class="mt-2"
            >
              <b-col
                cols="12"
                xl="12"
                lg="12"
                md="12"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[11]"
                  name="check-button"
                  class="custom-control-warning"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.chargeReturnCampaignSmsMessage') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <b-row
              class="mt-2"
            >
              <b-col
                cols="12"
                xl="12"
                lg="12"
                md="12"
              >
                <b-form-checkbox
                  v-model="parameterLocal.charges[12]"
                  name="check-button"
                  class="custom-control-warning"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                >
                  {{ $t('company.companyDetailsTabCard.chargeCallWhatsappCampaignSmsMessage') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>

        <b-row>
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
  BLink, BImg, BBadge, BFormRating, BFormInvalidFeedback, VBTooltip, BCardBody, BFormCheckbox,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { ref, toRefs } from '@vue/composition-api'
import axios from '@axios'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import money from 'v-money'
import formValidation from '@core/comp-functions/forms/form-validation'
import useCompanySettingCharge from './useCompanySettingCharge'
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
    BCardBody,
    BFormCheckbox,

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
      parameterLocal,
      resetUserLocal,
      // UI
      onSubmit,
    } = useCompanySettingCharge(toRefs(props), emit)

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

      parameterLocal,
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
