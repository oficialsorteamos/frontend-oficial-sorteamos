<template>
  <div>
    <form-wizard
      color="#7367F0"
      :title="null"
      :subtitle="null"
      :next-button-text="$t('user.userModalEditAccessDetailHandler.next')"
      :finish-button-text="$t('user.userModalEditAccessDetailHandler.submit')"
      :back-button-text="$t('user.userModalEditAccessDetailHandler.previous')"
      class="steps-transparent mb-3"
      @on-complete="onSubmit"
    >

      <!-- account detail tab -->
      <tab-content
        :title="$t('user.userModalEditAccessDetailHandler.accountDetails')"
        icon="feather icon-file-text"
        :before-change="validationFormAccess"
      >
        <validation-observer
          ref="accessRules"
          tag="form"
        >
          <b-row>
            <b-col
              cols="12"
              class="mb-2"
            >
              <h5 class="mb-0">
                {{ $t('user.userModalEditAccessDetailHandler.accountDetails') }}
              </h5>
              <small class="text-muted">
                {{ $t('user.userModalEditAccessDetailHandler.accountDetailsSubtitle') }}
              </small>
            </b-col>
            <!-- Departamentos -->
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userModalEditAccessDetailHandler.departments')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalEditAccessDetailHandler.departments')"
                  label-for="vue-select"
                >
                  <v-select
                    id="vue-select"
                    v-model="userLocal.departments[0]"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="departments"
                    :getOptionLabel="departments => departments.dep_name"
                    transition=""
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Perfis -->
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userModalEditAccessDetailHandler.roles')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userModalEditAccessDetailHandler.roles')"
                  label-for="vue-select"
                >
                  <v-select
                    id="vue-select"
                    v-model="userLocal.roles[0]"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="roles"
                    :getOptionLabel="roles => roles.rol_name"
                    transition=""
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
        </validation-observer>
      </tab-content>

      <!-- Access detail tab -->
      <tab-content
        :title="$t('user.userModalEditAccessDetailHandler.accessDetails')"
        icon="feather icon-lock"
        :before-change="validationForm"
      >
        <validation-observer
          ref="accountRules"
          tag="form"
        >
          <b-row>
            <b-col
              cols="12"
              class="mb-2"
            >
              <h5 class="mb-0">
                {{ $t('user.userModalEditAccessDetailHandler.accessDetails') }}
              </h5>
              <small class="text-muted">
                {{ $t('user.userModalEditAccessDetailHandler.accessDetailsSubtitle') }}
              </small>
            </b-col>
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                :name="$t('user.userModalEditAccessDetailHandler.username')"
                rules="required|min:5"
              >
                <b-form-group
                  :label="$t('user.userModalEditAccessDetailHandler.username')"
                  label-for="user-username"
                >
                  <b-form-input
                    id="user-username"
                    v-model="userLocal.username"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col md="6">
            </b-col>
            <!-- Password -->
            <b-col md="6">
              <b-form-group
                :label="$t('user.userModalEditAccessDetailHandler.password')"
                label-for="password"
              >
                <validation-provider
                  #default="{ errors }"
                  :name="$t('user.userModalEditAccessDetailHandler.password')"
                  rules="password"
                >
                  <b-input-group
                    class="input-group-merge"
                  >
                    <b-form-input
                      id="address-password"
                      v-model="password"
                      :state="errors.length > 0 ? false : null"
                      class="form-control-merge"
                      trim
                      placeholder=""
                      :type="passwordFieldType"
                    />
  
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
            <!-- Confirm Password só é obrigatório caso o campo password seja preenchido-->
            <b-col md="6">
              <b-form-group
                :label="$t('user.userModalEditAccessDetailHandler.confirmPassword')"
                label-for="confirm-password"
              >
                <validation-provider
                  #default="{ errors }"
                  :name="$t('user.userModalEditAccessDetailHandler.confirmPassword')"
                  :rules="password.length > 0 ? 'confirmed:Nova Senha|required' : 'confirmed:Nova Senha'"
                >
                  <b-input-group
                    class="input-group-merge"
                  >
                    <b-form-input
                      id="confirm-password"
                      v-model="userLocal.confirm_password"
                      :state="errors.length > 0 ? false : null"
                      class="form-control-merge"
                      trim
                      placeholder=""
                      :type="passwordFieldType"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="passwordToggleIcon"
                        @click="togglePasswordVisibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>
            </b-col>
          </b-row>
        </validation-observer>
      </tab-content>
    </form-wizard>
  </div>
</template>

<script>
import {
  BRow, BCol, BFormGroup, BFormInput, BFormInvalidFeedback, BFormDatepicker, BSpinner, BInputGroupAppend, BInputGroup,
} from 'bootstrap-vue'
import axios from '@axios'
import { FormWizard, TabContent } from 'vue-form-wizard'
import vSelect from 'vue-select'
import { toRefs, ref } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useUserModalEditAccessDetailHandler from './useUserModalEditAccessDetailHandler'
import { required } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'
import { codeIconInfo } from './code'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'


export default {
  components: {
    FormWizard,
    TabContent,
    BRow,
    BCol,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    BFormDatepicker,
    BSpinner,
    vSelect,
    // eslint-disable-next-line vue/no-unused-components
    ToastificationContent,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
    BInputGroupAppend,
    BInputGroup,

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
  mixins: [togglePasswordVisibility],
  data() {
    return {
      departments: [],
      roles: [],
      password: '',
    }
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
  },
  created() { 
    //Traz os departamentos cadastrados
    axios
      .get('/api/management/department/fetch-departments/')
      .then(response => {
        this.departments = response.data.departments
      });
    
    //Traz os perfis cadastrados
    axios
      .get('/api/system/role/fetch-roles/')
      .then(response => {
        this.roles = response.data.roles
      });
  },
  methods: {
    formSubmitted() {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Form Submitted',
          icon: 'EditIcon',
          variant: 'success',
        },
      })
    },
    validationForm() {
      return new Promise((resolve, reject) => {
        this.$refs.accountRules.validate().then(success => {
          if (success) {
            resolve(true)
          } else {
            reject()
          }
        })
      })
    },
    validationFormAccess() {
      return new Promise((resolve, reject) => {
        this.$refs.accessRules.validate().then(success => {
          if (success) {
            resolve(true)
          } else {
            reject()
          }
        })
      })
    },
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
      resetTransferLocal,
      // UI
      onSubmit,
    } = useUserModalEditAccessDetailHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearUserData)

    

    return {
      userLocal,
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