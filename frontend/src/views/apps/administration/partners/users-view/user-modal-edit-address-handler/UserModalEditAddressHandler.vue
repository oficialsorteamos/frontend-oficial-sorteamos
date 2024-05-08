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
        <b-row>
          <b-col md="3">
            <validation-provider
              #default="validationContext"
              :name="$t('user.userModalEditAddressHandler.cep')"
              rules="required|min:9"
            >
              <b-form-group
                :label="$t('user.userModalEditAddressHandler.cep')"
                label-for="task-title"
              >
                <b-form-input
                  id="address-cep"
                  v-model="user.cep"
                  autofocus
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  v-mask="'#####-####'"
                  :maxlength="9"
                  @keyup="getAddressUser(user.cep)"
                  autocomplete="off"
                  v-bind:value="userLocal.cep = user.cep"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col
            cols="12"
            xl="4"
            lg="2"
            md="5"
          >
            <div
              class="mt-2"
            >
              <b-spinner 
                :label="$t('user.userModalEditAddressHandler.loading')" 
                v-show="loading"
              />
            </div>
          </b-col>
          <b-col md="10">
            <validation-provider
              #default="validationContext"
              :name="$t('user.userModalEditAddressHandler.street')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userModalEditAddressHandler.street')"
                label-for="address-street"
              >
                <b-form-input
                  id="address-street"
                  v-model="user.street"
                  :state="getValidationState(validationContext)"
                  trim
                  :placeholder="$t('user.userModalEditAddressHandler.streetPlaceholder')"
                  v-bind:value="userLocal.street = user.street"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col md="2">
            <validation-provider
              #default="validationContext"
              :name="$t('user.userModalEditAddressHandler.number')"
            >
              <b-form-group
                :label="$t('user.userModalEditAddressHandler.number')"
                label-for="address-number"
              >
                <b-form-input
                  id="address-number"
                  v-model="user.number"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  v-bind:value="userLocal.number = user.number"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col md="12">
            <validation-provider
              #default="validationContext"
              :name="$t('user.userModalEditAddressHandler.addressComplement')"
              rules=""
            >
              <b-form-group
                :label="$t('user.userModalEditAddressHandler.addressComplement')"
                label-for="address-complement"
              >
                <b-form-input
                  id="address-complement"
                  v-model="user.address_complement"
                  :state="getValidationState(validationContext)"
                  trim
                  :placeholder="$t('user.userModalEditAddressHandler.addressComplementPlaceholder')"
                  v-bind:value="userLocal.address_complement = user.address_complement"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col md="6">
            <validation-provider
              #default="validationContext"
              :name="$t('user.userModalEditAddressHandler.district')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userModalEditAddressHandler.district')"
                label-for="address-district"
              >
                <b-form-input
                  id="address-district"
                  v-model="user.district"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  v-bind:value="userLocal.district = user.district"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col md="6">
            <validation-provider
              #default="validationContext"
              :name="$t('user.userModalEditAddressHandler.city')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userModalEditAddressHandler.city')"
                label-for="address-city"
              >
                <b-form-input
                  id="address-city"
                  v-model="user.city"
                  :state="getValidationState(validationContext)"
                  trim
                  placeholder=""
                  v-bind:value="userLocal.city = user.city"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col md="6">
            <validation-provider
              #default="{ errors }"
              :name="$t('user.userModalEditAddressHandler.state')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userModalEditAddressHandler.state')"
                label-for="vue-select"
              >
                <v-select
                  id="vue-select"
                  v-model="user.state"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="states"
                  :getOptionLabel="states => states.sta_uf"
                  v-bind:value="userLocal.state = user.state"
                  transition=""
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col md="6">
            <validation-provider
              #default="{ errors }"
              :name="$t('user.userModalEditAddressHandler.country')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userModalEditAddressHandler.country')"
                label-for="vue-select"
              >
                <v-select
                  id="vue-select"
                  v-model="user.country"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="countries"
                  :getOptionLabel="countries => countries.cou_name"
                  v-bind:value="userLocal.country = user.country"
                  transition=""
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
                </b-form-invalid-feedback>
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
            {{ $t('user.userModalEditAddressHandler.update') }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BRow, BCol, BSpinner,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import useUserModalEditAddressHandler from './useUserModalEditAddressHandler'
import formValidation from '@core/comp-functions/forms/form-validation'
import { VueMaskDirective } from 'v-mask'
import Vue from 'vue'
Vue.directive('mask', VueMaskDirective)

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BRow,
    BCol,
    BSpinner,
    BFormInput,
    BFormGroup,
    vSelect,
    BFormInvalidFeedback,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

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
    loading : {
      type: Boolean,
      required: true,
    }
  },
  data() {
    return {
      states: [],
      countries: [],
      userLocal: {
        street: ''
      }
    }
  },
  methods: {
    //Chama a função no PAI que faz a busca do endereço
    getAddressUser(cep) {
      this.$emit('get-address-user', cep)
    },
  },
  created() { 
      //Traz os Estados
    axios
      .get('/api/system/state/fetch-states/')
      .then(response => {
        this.states = response.data
      });
    
    //Traz os países
    axios
      .get('/api/system/country/fetch-countries/')
      .then(response => {
        this.countries = response.data
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
    } = useUserModalEditAddressHandler(toRefs(props), emit)

    
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

</script>