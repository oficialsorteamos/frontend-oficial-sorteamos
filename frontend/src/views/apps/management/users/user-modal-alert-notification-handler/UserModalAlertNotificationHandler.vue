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
          <validation-provider
            #default="{ errors }"
            :name="$t('user.userModalAlertNotificationHandler.departmentOrUser')"
            :rules="requiredDepartment"
          >
            <!-- Departamento para onde o alerta será encaminhado -->
            <b-form-group
              :label="$t('user.userModalAlertNotificationHandler.departments')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="optionDepartmentsSelected"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="departments"
                :closeOnSelect="false"
                :clearable="true"
                :disabled="disableDepartmentsInput"
                multiple
                :getOptionLabel="departments => departments.dep_name"
                transition=""
              />
              <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                {{ errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>
          
          <!-- Users -->
          <b-form-group
            :label="$t('user.userModalAlertNotificationHandler.users')"
            label-for="vue-select"
          >
            <v-select
              id="vue-select"
              v-model="optionUsersSelected"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="users"
              :closeOnSelect="false"
              :disabled="disableUsersInput"
              multiple
              :getOptionLabel="users => users.name"
            />
          </b-form-group>
            <!-- Message -->
            <validation-provider
              #default="{ errors }"
              :name="$t('user.userModalAlertNotificationHandler.message')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userModalAlertNotificationHandler.message')"
                label-for="vue-select"
              >
                <b-form-textarea
                  v-model="alertLocal.message"
                  id="textarea-default"
                  :placeholder="$t('user.userModalAlertNotificationHandler.messagePlaceholder')"
                  rows="3"
                  trim
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
              {{ $t('user.userModalAlertNotificationHandler.send') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormTextarea, BFormInvalidFeedback,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required } from '@validations'
import useUserModalAlertNotificationHandler from './useUserModalAlertNotificationHandler'
import formValidation from '@core/comp-functions/forms/form-validation'


export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    BFormTextarea,
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
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      requiredDepartment: 'required',
      optionDepartmentsSelected: '',
      optionUsersSelected: '',
      disableUsersInput: false,
      disableDepartmentsInput: false,
      departments: [],
      users: [],
      blocs: [],
      /*alertLocal: {
        departments: null,
        users: null,
      }*/
    }
  },
  watch: {
    optionDepartmentsSelected(optionDepartmentsSelected) {
      this.alertLocal.departments = optionDepartmentsSelected
      this.alertLocal.users = []
      //Caso exista algum departamento selecionado
      if(optionDepartmentsSelected.length > 0) {
        this.disableUsersInput = true
      }
      else {
        this.disableUsersInput = false
      }
    },
    optionUsersSelected(optionUsersSelected) {
      this.alertLocal.users = optionUsersSelected
      this.alertLocal.departments = []
      //Caso exista algum departamento selecionado
      if(optionUsersSelected.length > 0) {
        this.disableDepartmentsInput = true
        //Caso algum usuário tenha sido selecionado, retira o obrigação de preencher algum departamento
        this.requiredDepartment = ''
      }
      else {
        this.disableDepartmentsInput = false
        this.requiredDepartment = 'required'
      }
    },
  },
  created() { 
    //Traz os departamentos cadastrados
    axios
      .get('/api/management/department/fetch-departments/')
      .then(response => {
        //console.log(response.data.departments)
        this.departments = response.data.departments
      })
    
    //Traz os usuários do sistema, exceto o usuário logado
    axios
      //.get('/api/system/user/get-user/')
      .get('/api/management/user/get-user-without-logged')
      .then(response => {
        console.log(response.data)
        this.users = response.data.users
      })
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
      alertLocal,
      resetAlertLocal,
      // UI
      onSubmit,
    } = useUserModalAlertNotificationHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetAlertLocal, props.clearActionData)


    return {
      // Add New Event
      alertLocal,
      resetAlertLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}
</script>