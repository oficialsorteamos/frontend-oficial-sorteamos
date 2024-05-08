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
          <!-- new password -->
          <b-col md="12">
            <validation-provider
              #default="{ errors }"
              :name="$t('campaign.campaignSettingFrequency.frequency')"
              rules="required"
            >
              <b-form-group
                :label="$t('campaign.campaignSettingFrequency.frequencyLabel')"
                label-for="vue-select"
                :state="errors.length > 0 ? false:null"
              >
                <v-select
                  id="vue-select"
                  v-model="settingsLocal.settings[0].operating_frequency"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="operatingFrequency"
                  :getOptionLabel="operatingFrequency => operatingFrequency.ope_description"
                  transition=""
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

          <!-- new password -->
          <b-col md="6">
            <validation-provider
              #default="{ errors }"
              :name="$t('campaign.campaignSettingFrequency.numberShots')"
              rules="required"
            >
              <b-form-group
                :label="$t('campaign.campaignSettingFrequency.numberShots')"
                label-for="vue-select"
                :state="errors.length > 0 ? false:null"
              >
                <v-select
                  id="vue-select"
                  v-model="settingsLocal.settings[0].number_shot_frequency"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="numberShotsFrequency"
                  :getOptionLabel="numberShotsFrequency => numberShotsFrequency.num_shots"
                  transition=""
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
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
              {{ $t('campaign.campaignSettingFrequency.update') }}
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
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import useCampaignSettingFrequency from './useCampaignSettingFrequency'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'
import axios from '@axios'
import vSelect from 'vue-select'

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
    vSelect,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  props: {
    campaign: {
      type: Object,
      required: true,
    },
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      operatingFrequency: [],
      numberShotsFrequency: [],
    }
  },
  created() { 
    //Traz as frequências de operação
    axios
      .get('/api/campaign/fetch-operating-frequency')
      .then(response => {
        console.log(response.data)
        this.operatingFrequency = response.data.operatingFrequency
      });

    //Traz os números de disparos por frequência
    axios
      .get('/api/campaign/fetch-number-shots-frequency')
      .then(response => {
        console.log(response.data)
        this.numberShotsFrequency = response.data.numberShotsFrequency
      });
  },
  setup(props, {emit}) {

    const {
      settingsLocal,
      resetUserLocal,
      // UI
      onSubmit,
    } = useCampaignSettingFrequency(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
    } = formValidation(resetUserLocal, props.clearContactData)

    return {
      settingsLocal,
      resetUserLocal,
      // UI
      onSubmit,

      refFormObserver,
      getValidationState,
    }
  }
}
</script>
