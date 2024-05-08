<template>
  <div>
      <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <!-- select 2 demo -->
        <b-form
          enctype="multipart/form-data"
          @submit.prevent="handleSubmit(onSubmit)"
        >
          <!-- Offcial API -->
          <b-form-checkbox
            v-model="chatbotLocal.cha_only_official_channel"
            name="check-button"
            class="mb-1"
            switch
            inline
            value="1"
            unchecked-value="0"
          >
            {{ $t('chatbot.chatbotModalNewChatbotHandler.onlyOfficialChannels') }} 
            
          </b-form-checkbox>
          <b-row>
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userModalHandler.gender')"
                rules="required"
              >
                <b-form-group
                  :label="$t('chatbot.chatbotModalNewChatbotHandler.typeChatbot')+'*'"
                  label-for="vue-select"
                  :state="errors.length > 0 ? false:null"
                >
                  <v-select
                    id="vue-select"
                    v-model="chatbotLocal.type_chatbot"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="typeChatbots"
                    :getOptionLabel="typeChatbots => typeChatbots.typ_description"
                    transition=""
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
          
          <!-- Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('chatbot.chatbotModalNewChatbotHandler.name')"
            rules="required"
          >
            <b-form-group
              :label="$t('chatbot.chatbotModalNewChatbotHandler.name')+'*'"
              label-for="chatbot-name"
            >
              <b-form-input
                id="chatbot-name"
                v-model="chatbotLocal.cha_name"
                :state="getValidationState(validationContext)"
                trim
                placeholder="Chatbot name"
                type="text"
                :maxlength="30"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Description -->
          <validation-provider
            #default="validationContext"
            :name="$t('chatbot.chatbotModalNewChatbotHandler.description')"
            rules="required"
          >
            <b-form-group
              :label="$t('chatbot.chatbotModalNewChatbotHandler.description')+'*'"
              label-for="campaign-description"
            >
              <b-form-input
                id="chatbot-description"
                v-model="chatbotLocal.cha_description"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('chatbot.chatbotModalNewChatbotHandler.chatbotDescriptionPlaceholder')"
                type="text"
                :maxlength="120"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Form Actions -->
          <div class="d-flex mt-2 modal-footer">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              {{ chatbotLocal.id ? $t('chatbot.chatbotModalNewChatbotHandler.update') : $t('chatbot.chatbotModalNewChatbotHandler.add') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormCheckbox, BRow, BCol,
} from 'bootstrap-vue'
import store from '@/store'
import axios from '@axios'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs, ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useChatbotModalNewChatbotHandler from './useChatbotModalNewChatbotHandler'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    BFormCheckbox,
    vSelect,
    BFormInvalidFeedback,
    BRow,
    BCol,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    chatbot: {
      type: Object,
      required: true,
    },
    clearCampaignData: {
      type: Function,
      required: false,
    },
  },
  data() {
    return {
      phoneNumber: '',
      typeChatbots: [],
    }
  },
  created() { 
    //Traz os departamentos cadastrados
    axios
      .get('/api/chatbot/fetch-type-chatbots/')
      .then(response => {
        //console.log(response.data.departments)
        this.typeChatbots = response.data.typeChatbots
      });
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
      chatbotLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useChatbotModalNewChatbotHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)


    return {
      // Add New Contact
      chatbotLocal,
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