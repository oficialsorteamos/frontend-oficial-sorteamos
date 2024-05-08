<template>
  <div>
      <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <b-form
          enctype="multipart/form-data"
          @submit.prevent="handleSubmit(onSubmit)"
        > 

          <!-- Trunk -->
          <!--
          <validation-provider
            #default="validationContext"
            :name="$t('extension.extensionModalHandler.voip')"
            rules="required"
          >
            <b-form-group
              :label="$t('extension.extensionModalHandler.voip')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="extensionLocal.voip.id"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="voips"
                :getOptionLabel="voips => voips.voi_name"
                :reduce="voips => voips.id"
                transition=""
              />
            <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>
          -->
          
          <!-- Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('extension.extensionModalHandler.extensionNumber')"
            rules="required|integer"
          >
            <b-form-group
              :label="$t('extension.extensionModalHandler.extensionNumber')+'*'"
              label-for="department-name"
            >
              <b-form-input
                id="department-name"
                v-model="extensionLocal.name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('extension.extensionModalHandler.extensionNumberPlaceholder')"
                type="text"
                :maxlength="4"
                autocomplete="off"
                v-if="$can('menu_administration_partner', 'administration')"
              />

              <b-form-input
                id="department-name"
                v-model="extensionLocal.name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('extension.extensionModalHandler.extensionNumberPlaceholder')"
                type="text"
                :maxlength="4"
                autocomplete="off"
                v-else
                :readonly="true"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Description -->
          <validation-provider
            #default="validationContext"
            :name="$t('extension.extensionModalHandler.extensionDescription')"
            rules="required|min:10"
          >
            <b-form-group
              :label="$t('extension.extensionModalHandler.extensionDescription')+'*'"
              label-for="department-description"
            >
              <b-form-input
                id="department-description"
                v-model="extensionLocal.description"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('extension.extensionModalHandler.descriptionPlaceholder')"
                type="text"
                :maxlength="120"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Secret -->
          <!--
          <validation-provider
            #default="validationContext"
            :name="$t('extension.extensionModalHandler.extensionSecret')"
            rules="required"
          >
            <b-form-group
              :label="$t('extension.extensionModalHandler.extensionSecret')+'*'"
              label-for="department-description"
            >
              <b-form-input
                id="department-description"
                v-model="extensionLocal.secret"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('extension.extensionModalHandler.extensionSecretPlaceholder')"
                type="password"
                :maxlength="120"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>
          -->

          <!-- Users -->
          <validation-provider
            #default="{ errors }"
            :name="$t('administrationNotification.notificationModalHandler.typeUsersReceiveMessage')"
            rules="required"
          >
            <b-form-group
              :label="$t('administrationNotification.notificationModalHandler.typeUsersReceiveMessage')+'*'"
              label-for="vue-select"
              :state="errors.length > 0 ? false:null"
            >
              <v-select
                id="vue-select"
                v-model="extensionLocal.users"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                multiple
                :options="users"
                :getOptionLabel="users => users.name"
                transition=""
              />
              <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                {{ errors[0] }}
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
              {{ extensionLocal.id ? $t('extension.extensionModalHandler.update') : $t('extension.extensionModalHandler.add') }}
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
import axios from '@axios'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useExtensionModalHandler from './useExtensionModalHandler'
import { required } from '@validations'
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
    BFormDatepicker,
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
    extension: {
      type: Object,
      required: true,
    },
    clearExtensionData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      voips: '',
      users: [],
    }
  },
  created() {
    //Traz os usuários cadastrados
    axios
      .get('/api/integration/voip/get-voips/')
      .then(response => {
        //console.log(response.data)
        this.voips = response.data.voips
      })

      axios
      .get('/api/management/user/get-users-by-status/A')
      .then(response => {
        //console.log(response.data)
        this.users = response.data
      })
  },
  methods: {
    
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
      extensionLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useExtensionModalHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearExtensionData)

    return {
      extensionLocal,
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