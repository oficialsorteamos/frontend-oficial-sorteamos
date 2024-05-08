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
          <input
            type="hidden"
            id="phoneNumber"
            v-bind:value="channelLocal.phoneNumber = phoneNumber"
          />
          <!-- Offcial API -->
          <b-form-checkbox
            v-model="channelLocal.cha_api_official"
            name="check-button"
            class="mb-1"
            switch
            inline
            value="1"
            unchecked-value="0"
            :disabled="((channelLocal.cha_api_official == 1 && officialChannelParameter.par_value == 1) || (channelLocal.cha_api_official == 0 && unofficialChannelParameter.par_value == 1)) && channelLocal.cha_subscription == 1"
          >
            {{ $t('channel.channelModalEditChannelHandler.officialApi') }}
            
          </b-form-checkbox>
          <span
            v-if="channelLocal.cha_api_official == 1"
          >
            <!-- API associado ao Canal -->
            <validation-provider
              #default="{ errors }"
              name="API"
              :rules="channelLocal.cha_api_official == 1? 'required' : ''"
            >
              <b-form-group
                label="API"
                label-for="vue-select"
              >
                <v-select
                  id="vue-select"
                  v-model="channelLocal.api"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="apis"
                  :getOptionLabel="apis => apis.api_name"
                  transition=""
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </span>
          
          <validation-provider
            #default="{ errors }"
            :name="$t('channel.channelModalEditChannelHandler.phoneNumber')"
            rules="required|min:9"
          >
            <b-form-group
              label-for="user-phone"
              :label="$t('channel.channelModalEditChannelHandler.phoneNumber')+'*'"
            >
              <!-- Phone Number -->
              <VuePhoneNumberInput  
                v-model="channelLocal.cha_phone_number"
                :required="true"
                class="mb-1"
                @update="setPhoneNumber"
                @input="checkPhoneExist"
                :default-country-code="channelLocal.cha_country_code"
                @keyup="checkPhoneExist(channelLocal.cha_phone_number)"
                :disabled="channel.cha_status == 'A' && channelLocal.cha_api_official == '0'? true : false"
              />
              <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                {{ errors[0] }}
              </b-form-invalid-feedback>
              <!-- Se o canal estiver ativo e o mesmo não for oficial -->
              <span 
                v-if="channel.cha_status == 'A' && channelLocal.cha_api_official == '0'"
                style="font-size: 0.857rem; color: #ea5455;"
              >
                Para editar o número do canal, você deve desabilitá-lo primeiro
              </span>
              <span 
                v-if="errorMessage"
                style="font-size: 0.857rem; color: #ea5455;"
              >
                {{ $t('channel.channelModalEditChannelHandler.numberAlreadyUsed') }}
              </span>
            </b-form-group>
          </validation-provider>
          
          <!-- Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('channel.channelModalEditChannelHandler.name')"
            rules="required"
          >
            <b-form-group
              :label="$t('channel.channelModalEditChannelHandler.name')+'*'"
              label-for="channel-name"
            >
              <b-form-input
                id="channel-name"
                v-model="channelLocal.cha_name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('channel.channelModalEditChannelHandler.namePlaceholder')"
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
            :name="$t('channel.channelModalEditChannelHandler.description')"
          >
            <b-form-group
              :label="$t('channel.channelModalEditChannelHandler.description')"
              label-for="channel-description"
            >
              <b-form-input
                id="channel-description"
                v-model="channelLocal.cha_description"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('channel.channelModalEditChannelHandler.descriptionPlaceholder')"
                type="text"
                :maxlength="120"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Company Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('channel.channelModalEditChannelHandler.company')"
          >
            <b-form-group
              :label="$t('channel.channelModalEditChannelHandler.company')"
              label-for="channel-company"
            >
              <b-form-input
                id="channel-company"
                v-model="channelLocal.cha_company_name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('channel.channelModalEditChannelHandler.companyPlaceholder')"
                type="text"
                :maxlength="60"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Email -->
          <validation-provider
            #default="validationContext"
            :name="$t('channel.channelModalEditChannelHandler.email')"
            rules="email"
          >
            <b-form-group
              :label="$t('channel.channelModalEditChannelHandler.email')"
              label-for="channel-email"
            >
              <b-form-input
                id="channel-email"
                v-model="channelLocal.cha_company_email"
                :state="getValidationState(validationContext)"
                trim
                placeholder="example@example.com"
                type="text"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Site -->
          <validation-provider
            #default="validationContext"
            :name="$t('channel.channelModalEditChannelHandler.site')"
            rules="url"
          >
            <b-form-group
              :label="$t('channel.channelModalEditChannelHandler.site')"
              label-for="channel-site"
            >
              <b-form-input
                id="channel-site"
                v-model="channelLocal.cha_company_site"
                :state="getValidationState(validationContext)"
                trim
                placeholder="http://www.example.com.br"
                type="text"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Address -->
          <validation-provider
            #default="validationContext"
            :name="$t('channel.channelModalEditChannelHandler.address')"
          >
            <b-form-group
              :label="$t('channel.channelModalEditChannelHandler.address')"
              label-for="channel-address"
            >
              <b-form-input
                id="channel-address"
                v-model="channelLocal.cha_company_address"
                :state="getValidationState(validationContext)"
                trim
                placeholder=""
                type="text"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Se for API da Z-API -->
          <!--
          <span
            v-if="channelLocal.cha_api_official == 0"
          >
            
            <validation-provider
              #default="validationContext"
              :name="$t('channel.channelModalEditChannelHandler.instanceId')"
              :rules="channelLocal.cha_api_official == 0? 'required' : ''"
              v-show="channelLocal.cha_api_official == 0? true : false"
            >
              <b-form-group
                :label="$t('channel.channelModalEditChannelHandler.instanceId')+'*'"
                label-for="channel-name"
              >
                <b-form-input
                  id="channel-name"
                  v-model="channelLocal.cha_channel_id_api"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="150"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

            <validation-provider
              #default="validationContext"
              :name="$t('channel.channelModalEditChannelHandler.instanceToken')"
              :rules="channelLocal.cha_api_official == 0? 'required' : ''"
              v-show="channelLocal.cha_api_official == 0? true : false"
            >
              <b-form-group
                :label="$t('channel.channelModalEditChannelHandler.instanceToken')+'*'"
                label-for="channel-name"
              >
                <b-form-input
                  id="channel-name"
                  v-model="channelLocal.cha_session_token"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="150"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </span>
          -->
          
          <!-- Se for API da GUPSHUP -->
          <span
            v-if="channelLocal.api && channelLocal.api.id == 1"
          >
            <!-- App Name -->
            <validation-provider
              #default="validationContext"
              :name="$t('channel.channelModalEditChannelHandler.appName')"
              :rules="channelLocal.api.id == 1? 'required' : ''"
              v-show="channelLocal.cha_api_official == 1? true : false"
            >
              <b-form-group
                :label="$t('channel.channelModalEditChannelHandler.appName')+'*'"
                label-for="channel-name"
              >
                <b-form-input
                  id="channel-name"
                  v-model="channelLocal.cha_app_name_api"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="150"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

            <!-- App ID -->
            <validation-provider
              #default="validationContext"
              :name="$t('channel.channelModalEditChannelHandler.appId')"
              :rules="channelLocal.api.id == 1? 'required' : ''"
              v-show="channelLocal.cha_api_official == 1? true : false"
            >
              <b-form-group
                :label="$t('channel.channelModalEditChannelHandler.appId')+'*'"
                label-for="channel-name"
              >
                <b-form-input
                  id="channel-name"
                  v-model="channelLocal.cha_channel_id_api"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="150"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </span>

          <!-- Se for API CLOUD -->
          <span
            v-if="channelLocal.api && channelLocal.api.id == 4"
          >
            <!-- App ID -->
            <validation-provider
              #default="validationContext"
              :name="$t('channel.channelModalEditChannelHandler.appId')"
              :rules="channelLocal.api.id == 1? 'required' : ''"
              v-show="channelLocal.cha_api_official == 1? true : false"
            >
              <b-form-group
                :label="$t('channel.channelModalEditChannelHandler.appId')+'*'"
                label-for="channel-name"
              >
                <b-form-input
                  id="channel-name"
                  v-model="channelLocal.cha_app_id_api"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="150"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

            <!-- Number ID -->
            <validation-provider
              #default="validationContext"
              :name="$t('channel.channelModalEditChannelHandler.numberId')"
              :rules="channelLocal.api.id == 1? 'required' : ''"
              v-show="channelLocal.cha_api_official == 1? true : false"
            >
              <b-form-group
                :label="$t('channel.channelModalEditChannelHandler.numberId')+'*'"
                label-for="channel-name"
              >
                <b-form-input
                  id="channel-name"
                  v-model="channelLocal.cha_channel_id_api"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="150"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

            <!-- App Name -->
            <validation-provider
              #default="validationContext"
              :name="$t('channel.channelModalEditChannelHandler.wabaId')"
              :rules="channelLocal.api.id == 4? 'required' : ''"
              v-show="channelLocal.cha_api_official == 1? true : false"
            >
              <b-form-group
                :label="$t('channel.channelModalEditChannelHandler.wabaId')+'*'"
                label-for="channel-name"
              >
                <b-form-input
                  id="channel-name"
                  v-model="channelLocal.whatsapp_business_account_id"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  type="text"
                  :maxlength="500"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </span>

          <!-- API Key. Só é exibido se o canal for oficial -->
          <validation-provider
            #default="validationContext"
            :name="$t('channel.channelModalEditChannelHandler.apiKey')"
            :rules="channelLocal.cha_api_official == 1? 'required' : ''"
            v-show="channelLocal.cha_api_official == 1? true : false"
          >
            <b-form-group
              :label="$t('channel.channelModalEditChannelHandler.apiKey')+'*'"
              label-for="channel-company"
            >
              <b-form-input
                id="channel-company"
                v-model="channelLocal.cha_api_key"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('channel.channelModalEditChannelHandler.apiKeyPlaceholder')"
                type="text"
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
              :disabled="channelLocal.cha_phone_number == '' || errorMessage || channelLocal.cha_name == ''"
              type="submit"
            >
              {{ channelLocal.id ? $t('channel.channelModalEditChannelHandler.update') : $t('channel.channelModalEditChannelHandler.add') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormDatepicker, BFormCheckbox,
} from 'bootstrap-vue'
import store from '@/store'
import axios from '@axios'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs, ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useChannelModalEditChannelHandler from './useChannelModalEditChannelHandler'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import VuePhoneNumberInput from 'vue-phone-number-input'

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    BFormInvalidFeedback,
    BFormDatepicker,
    BFormCheckbox,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    //Phone Input
    VuePhoneNumberInput,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    channel: {
      type: Object,
      required: true,
    },
    officialChannelParameter: {
      type: Object,
      required: true,
    },
    unofficialChannelParameter: {
      type: Object,
      required: true,
    },
    clearContactData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      phoneNumber: '',
      apis: [],
    }
  },
  methods: {
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
    },
  },
  created() { 
    //Traz os departamentos cadastrados
    axios
      .get('/api/management/channel/fetch-apis-communication/1')
      .then(response => {
        console.log(response.data)
        this.apis = response.data.apis
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
      channelLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useChannelModalEditChannelHandler(toRefs(props), emit)

    const errorMessage = ref(false)

    //Verifica se o número de telefone já está cadastro em outro canal
    const checkPhoneExist = phoneNumber => {
      //Se foi digitado entre 12 e 14 caracteres e não seja o próprio número atual do canal
      if(phoneNumber && phoneNumber.length >= 12 && phoneNumber.length <= 14 && props.channel.cha_phone_number != phoneNumber.replace(/\D/g, '')) {
        
        store.dispatch('app-channel/checkPhoneExist', { phoneNumber: phoneNumber })
        .then(response => {
          console.log(response.data.error)
          //Habilita o erro ou não
          errorMessage.value = response.data.error
        })
        .catch(error => {
        })
      }
    }

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)


    return {
      // Add New Contact
      channelLocal,
      errorMessage,
      resetTransferLocal,
      onSubmit,
      checkPhoneExist,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}

import 'vue-phone-number-input/dist/vue-phone-number-input.css';
</script>