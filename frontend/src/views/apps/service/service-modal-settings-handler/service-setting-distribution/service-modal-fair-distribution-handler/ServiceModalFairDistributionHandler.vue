<template>
  <div>
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
            id="fairDistributionId"
            v-bind:value="settingsLocal.fairDistributionId = fairDistribution.id"
          />
          <!--  
          <b-row class="mb-1">
            <b-col md="6">
              <b-form-checkbox
                v-model="settingsLocal.fai_main"
                name="check-button"
                switch
                inline
                :value="1"
                :unchecked-value="0"
              >
                {{ $t('services.serviceModalFairDistributionHandler.main') }}
              </b-form-checkbox>
            </b-col>
          </b-row>
          -->
          <b-row>
            <b-col sm="12">
              <!-- Name -->
              <validation-provider
                #default="validationContext"
                :name="$t('services.serviceModalFairDistributionHandler.name')"
                rules="required"
              >
                <b-form-group
                  :label="$t('services.serviceModalFairDistributionHandler.name')+'*'"
                  label-for="campaign-name"
                >
                  <b-form-input
                    id="campaign-name"
                    v-model="settingsLocal.fai_name"
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
          </b-row>

          <b-row>
            <b-col sm="12">
              <!-- Description -->
              <validation-provider
                #default="validationContext"
                :name="$t('services.serviceModalFairDistributionHandler.description')"
                rules="required"
              >
                <b-form-group
                  :label="$t('services.serviceModalFairDistributionHandler.description')"
                  label-for="campaign-description"
                >
                  <b-form-input
                    id="campaign-description"
                    v-model="settingsLocal.fai_description"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    type="text"
                    :maxlength="120"
                    autocomplete="off"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
          <b-row>
            <b-col sm="12">
              <b-form-group
                :label="$t('services.serviceModalFairDistributionHandler.channels')"
                label-for="vue-select"
              >
                <v-select
                  id="vue-select"
                  v-model="settingsLocal.channels"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="channelsData"
                  :getOptionLabel="channelsData => channelsData.cha_name"
                  multiple
                  transition=""
                >
                  <template #search="{attributes, events}">
                    <input
                      class="vs__search"
                      :required="!settingsLocal.channels || settingsLocal.channels.length == 0"
                      v-bind="attributes"
                      v-on="events"
                    />
                  </template>
                </v-select>
              </b-form-group>
            </b-col>
            <!-- Se tiver pelo menos um canal selecionado, mostra os usuários -->
            <b-col sm="12"
              v-if=" settingsLocal.channels && settingsLocal.channels.length > 0"
            >
              <validation-provider
                  #default="{ errors }"
                  :name="$t('services.serviceModalFairDistributionHandler.users')"
                  rules="required"
                >
                  <b-form-group
                    :label="$t('services.serviceModalFairDistributionHandler.users')"
                    label-for="vue-select"
                    :state="errors.length > 0 ? false:null"
                  >
                    <v-select
                      id="vue-select"
                      v-model="settingsLocal.users"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="usersData"
                      :getOptionLabel="usersData => usersData.name"
                      multiple
                      transition=""
                    >
                      <template #search="{attributes, events}">
                        <input
                          class="vs__search"
                          :required="!settingsLocal.users"
                          v-bind="attributes"
                          v-on="events"
                        />
                      </template>
                    </v-select>
                  </b-form-group>
                </validation-provider>
            </b-col>
          </b-row>
          <!-- Form Actions -->
          <div class="d-flex mt-2 modal-footer">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              {{ $t('services.serviceModalFairDistributionHandler.save') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormFile, BFormInvalidFeedback, BRow, BCol, BFormCheckbox,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCampaignModalMailingHandler from './useCampaignModalMailingHandler'
import formValidation from '@core/comp-functions/forms/form-validation'

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    BFormFile,
    BFormInvalidFeedback,
    vSelect,
    BRow,
    BCol,
    BFormCheckbox,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    fairDistribution: {
      type: Object,
      required: true,
    },
    campaignId: {
      type: Number,
      required: false,
    },
  },
  data() {
    return { 
      channelsData: [],
      usersData: [],
    }
  },
  created() {
    //Traz os canais
    axios
      .get('/api/management/channel/fetch-channels-by-status/A')
      .then(response => {
        console.log(response.data)
        this.channelsData = response.data.channels
      });

    //Traz os operadores
    axios
      .get('/api/management/user/get-users-by-role/2')
      .then(response => {
        console.log(response.data)
        this.usersData = response.data
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

    const handleFileUpload = (event) => {
      emit('upload-file', event.target.files[0])
    }

    const {
      settingsLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCampaignModalMailingHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearActionData)


    return {
      settingsLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,

      handleFileUpload,
    }
  },
}
</script>