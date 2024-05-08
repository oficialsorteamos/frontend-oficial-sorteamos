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
          <!-- Status -->
          <b-form-checkbox
            v-model="departmentLocal.dep_status"
            name="check-button"
            class="mb-1"
            switch
            inline
            value="A"
            unchecked-value="I"
            v-if="departmentLocal.id"
          >
            {{ $t('department.departmentModalHandler.status') }}
            
          </b-form-checkbox> 
          
          <!-- Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('department.departmentModalHandler.name')"
            rules="required"
          >
            <b-form-group
              :label="$t('department.departmentModalHandler.name')+'*'"
              label-for="department-name"
            >
              <b-form-input
                id="department-name"
                v-model="departmentLocal.dep_name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('department.departmentModalHandler.namePlaceholder')"
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
            :name="$t('department.departmentModalHandler.description')"
            rules="required|min:10"
          >
            <b-form-group
              :label="$t('department.departmentModalHandler.description')"
              label-for="department-description"
            >
              <b-form-input
                id="department-description"
                v-model="departmentLocal.dep_description"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('department.departmentModalHandler.descriptionPlaceholder')"
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
              {{ departmentLocal.id ? $t('department.departmentModalHandler.update') : $t('department.departmentModalHandler.add') }}
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
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useDepartmentModalHandler from './useDepartmentModalHandler'
import { required } from '@validations'
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
    department: {
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
    }
  },
  methods: {
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
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
      departmentLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useDepartmentModalHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)

    return {
      departmentLocal,
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