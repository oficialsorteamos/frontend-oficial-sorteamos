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
            <validation-provider
                #default="{ errors }"
                :name="$t('campaign.campaignSettingChannel.channels')"
              >
                <b-form-group
                  :label="$t('campaign.campaignSettingChannel.channels')"
                  label-for="vue-select"
                  :state="errors.length > 0 ? false:null"
                >
                  <!-- Se o tipo de chatbot é de atendimento -->
                  <span
                    v-if="chatbot.type_chatbot.id == 1"
                  >
                    <!-- Se for chatbot para canal oficial, só permiti escolher um canal -->
                    <v-select
                      id="vue-select"
                      v-model="settingsLocal.channels"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="channelsData"
                      :selectable="(option) => !option.inUse"
                      :getOptionLabel="channelsData => channelsData.cha_name"
                      :multiple="chatbot.cha_only_official_channel == 1? false : true"
                      transition=""
                    >
                      <template #option="{ cha_name, infoMessage }">
                        <b>{{ cha_name }}</b>
                        <br />
                        <cite>{{ infoMessage }}</cite>
                      </template>
                    </v-select>
                  </span>
                  <!-- Se o tipo de chatbot é de campanha -->
                  <span
                    v-if="chatbot.type_chatbot.id == 2"
                  >
                    <!-- Se for chatbot para canal oficial, só permiti escolher um canal -->
                    <v-select
                      id="vue-select"
                      v-model="settingsLocal.channels"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="channelsData"
                      :getOptionLabel="channelsData => channelsData.cha_name"
                      :multiple="chatbot.cha_only_official_channel == 1? false : true"
                      transition=""
                    >
                    </v-select>
                  </span>
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
              {{ $t('campaign.campaignSettingChannel.update') }}
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
import useChatbotSettingChannel from './useChatbotSettingChannel'
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
    chatbot: {
      //type: Array,
      required: true,
    },
  },
  data() {
    return { 
      channelsData: [],
    }
  },
  created() {
    //Traz os canais que NÃO utilizam apis oficiais 
    axios
      .get('/api/chatbot/fetch-channels-chatbot/'+this.chatbot.id+'/'+this.chatbot.cha_only_official_channel)
      .then(response => {
        console.log(response.data)
        this.channelsData = response.data.channels
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
    } = useChatbotSettingChannel(toRefs(props), emit)

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
