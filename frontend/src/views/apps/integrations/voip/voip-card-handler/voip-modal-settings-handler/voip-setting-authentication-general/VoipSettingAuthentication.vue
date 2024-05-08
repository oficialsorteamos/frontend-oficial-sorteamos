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
          <b-col sm="12">
            <!-- Name -->
            <validation-provider
              #default="validationContext"
              :name="$t('voip.voipSettingAuthentication.user')"
              rules="required"
            >
              <b-form-group
                :label="$t('voip.voipSettingAuthentication.user')+'*'"
                label-for="chatbot-name"
              >
                <b-form-input
                  id="chatbot-name"
                  v-model="settingsLocal.setting.voi_user"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="30"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col sm="12">
            <!-- Name -->
            <validation-provider
              #default="validationContext"
              :name="$t('voip.voipSettingAuthentication.secret')"
              rules="required"
            >
              <b-form-group
                :label="$t('voip.voipSettingAuthentication.secret')+'*'"
                label-for="chatbot-name"
              >
                <b-form-input
                  id="chatbot-name"
                  v-model="settingsLocal.setting.voi_secret"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="30"
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
import useVoipSettingaAuthentication from './useVoipSettingaAuthentication'
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
    voip: {
      //type: Array,
      required: true,
    },
  },
  data() {
    return { 
      channelsData: [],
    }
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
    } = useVoipSettingaAuthentication(toRefs(props), emit)

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
