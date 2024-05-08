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
          <input
            type="hidden"
            id="phoneNumber"
            v-bind:value="userLocal.phoneNumber = phoneNumber"
          />

          <!-- Phone Number -->
          <VuePhoneNumberInput  
            v-model="userLocal.phoneFormatted"
            :required="true"
            class="mb-1"
            @update="setPhoneNumber"
          />
          
          <!-- Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('user.userModalEditUserHandler.name')"
            rules="required"
          >
            <b-form-group
              :label="$t('user.userModalEditUserHandler.name')+'*'"
              label-for="task-title"
            >
              <b-form-input
                id="contact-name"
                v-model="userLocal.name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('user.userModalEditUserHandler.namePlaceholder')"
                type="text"
                :maxlength="40"
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
            :name="$t('user.userModalEditUserHandler.email')"
            rules="required"
          >
            <b-form-group
              :label="$t('user.userModalEditUserHandler.email')+'*'"
              label-for="task-title"
            >
              <b-form-input
                id="contact-email"
                v-model="userLocal.email"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('user.userModalEditUserHandler.emailPlaceholder')"
                type="text"
                :maxlength="70"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Gender -->
          <b-form-group
            :label="$t('user.userModalEditUserHandler.gender')+'*'"
            label-for="vue-select"
          >
            <v-select
              id="vue-select"
              v-model="userLocal.gender"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="genders"
              :getOptionLabel="genders => genders.gen_description"
              transition=""
            >
              <template #search="{attributes, events}">
                <input
                  class="vs__search"
                  :required="!userLocal.gender"
                  v-bind="attributes"
                  v-on="events"
                />
              </template>
            </v-select>
          </b-form-group>

          <!-- Birthday -->
          <!--
          <b-form-group
            :label="$t('user.userModalEditUserHandler.birthday')"
            label-for="vue-select"
          >
            <b-form-datepicker
              v-model="userLocal.birthday"
              id="contact-birthday"
              :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
              locale="pt-br"
            />
          </b-form-group>
          -->

          <validation-provider
            #default="validationContext"
            :name="$t('user.userModalEditUserHandler.birthday')"
            rules="required|min:10"
          >
            <b-form-group
              :label="$t('user.userModalEditUserHandler.birthday')"
              label-for="contact-birthday"
            >
              <b-form-input
                id="contact-birthday"
                v-model="userLocal.birthday"
                :state="getValidationState(validationContext)"
                trim
                placeholder=""
                v-mask="'##/##/####'"
                :maxlength="10"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            :name="$t('user.userModalEditUserHandler.cpf')"
            rules="required|min:14"
          >
            <b-form-group
              :label="$t('user.userModalEditUserHandler.cpf')"
              label-for="user-cpf"
            >
              <b-form-input
                id="user-cpf"
                v-model="userLocal.cpf"
                :state="getValidationState(validationContext)"
                trim
                placeholder=""
                v-mask="'###.###.###-##'"
                :maxlength="14"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- link -->
          <validation-provider
            #default="validationContext"
            :name="$t('user.userModalEditUserHandler.link')"
            rules="required"
          >
            <b-form-group
              :label="$t('user.userModalEditUserHandler.link')"
              label-for="task-title"
            >
              <b-form-input
                id="contact-ilnk"
                v-model="userLocal.link"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('user.userModalEditUserHandler.linkPlaceholder')"
                type="text"
                :maxlength="70"
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
              {{ $t('user.userModalEditUserHandler.update') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormDatepicker,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import useUserModalEditUserHandler from './useUserModalEditUserHandler'
import formValidation from '@core/comp-functions/forms/form-validation'
import { VueMaskDirective } from 'v-mask'
import VuePhoneNumberInput from 'vue-phone-number-input'
import Vue from 'vue'
Vue.directive('mask', VueMaskDirective)

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

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    //Phone Input
    VuePhoneNumberInput,

    //Máscara
    VueMaskDirective,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
    clearUserData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      genders: [],
      phoneNumber: null,
    }
  },
  methods: {
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
    },
  },
  created() { 
    //Traz os gêneros cadastrados
    axios
      .get('/api/system/gender/fetch-genders/')
      .then(response => {
        //console.log(response.data.departments)
        this.genders = response.data
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
      userLocal,
      resetUserLocal,
      // UI
      onSubmit,
    } = useUserModalEditUserHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetUserLocal, props.clearContactData)


    return {
      // Add New Contact
      userLocal,
      resetUserLocal,
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