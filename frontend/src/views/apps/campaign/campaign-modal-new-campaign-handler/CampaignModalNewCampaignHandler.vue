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
           <!-- Departamento para onde o atendimento será transferido -->
          <b-form-group
            :label="$t('campaign.campaignModalNewCampaignHandler.typeCampaign')"
            label-for="vue-select"
          >
            <v-select
              id="vue-select"
              v-model="campaignLocal.type_campaign"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="typecampaigns"
              :getOptionLabel="typecampaigns => typecampaigns.cam_description"
              transition=""
              :disabled="campaign.totalContactsProcessed >0? true : false"
            >
              <template #search="{attributes, events}">
                <input
                  class="vs__search"
                  :required="!campaignLocal.type_campaign"
                  v-bind="attributes"
                  v-on="events"
                />
              </template>
            </v-select>
          </b-form-group>
          <!-- Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('campaign.campaignModalNewCampaignHandler.name')"
            rules="required"
          >
            <b-form-group
              :label="$t('campaign.campaignModalNewCampaignHandler.name')+'*'"
              label-for="campaign-name"
            >
              <b-form-input
                id="campaign-name"
                v-model="campaignLocal.cam_name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('campaign.campaignModalNewCampaignHandler.namePlaceholder')"
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
            :name="$t('campaign.campaignModalNewCampaignHandler.description')"
          >
            <b-form-group
              :label="$t('campaign.campaignModalNewCampaignHandler.description')"
              label-for="campaign-description"
            >
              <b-form-input
                id="campaign-description"
                v-model="campaignLocal.cam_description"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('campaign.campaignModalNewCampaignHandler.descriptionPlaceholder')"
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
              {{ campaignLocal.id ? $t('campaign.campaignModalNewCampaignHandler.update') : $t('campaign.campaignModalNewCampaignHandler.add') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback,
} from 'bootstrap-vue'
import store from '@/store'
import axios from '@axios'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs, ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCampaignModalNewCampaignHandler from './useCampaignModalNewCampaignHandler'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    BFormInvalidFeedback,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    campaign: {
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
      typecampaigns: [],
    }
  },
  created() { 
    //Traz as tags cadastradas para contatos
    axios
      .get('/api/campaign/fetch-type-campaigns/')
      .then(response => {
        console.log('type campaigns')
        console.log(response.data)
        this.typecampaigns = response.data.typeCampaigns
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
      campaignLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCampaignModalNewCampaignHandler(toRefs(props), emit)

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
      campaignLocal,
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