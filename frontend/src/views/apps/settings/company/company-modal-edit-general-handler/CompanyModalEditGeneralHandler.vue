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
            <b-col sm="6">
              <!-- Início da operação -->
              <validation-provider
                #default="validationContext"
                name="Start Operation"
                rules="required|min:10"
              >
                <b-form-group
                  label="Start Operation"
                  label-for="company-start-operation"
                >
                  <b-form-input
                    v-model="parametersGeneralLocal[0].par_value"
                    id="company-start-operation"
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
              {{ $t('channel.channelModalEditOptionHandler.update') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BRow, BCol,
} from 'bootstrap-vue'
import axios from '@axios'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCompanyModalEditGeneralHandler from './useCompanyModalEditGeneralHandler'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import money from 'v-money'
import Vue from 'vue'
Vue.use(money, {precision: 2})

export default {
  components: {
    BButton,
    BRow,
    BCol,
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
    parametersGeneral: {
      type: Array,
      required: true,
    },
    clearContactData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      money: {
        decimal: ',',
        thousands: '.',
        prefix: 'R$ ',
        //suffix: ' #',
        precision: 2,
        masked: false
      }
    }
  },
  created() { 
    
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
      parametersGeneralLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCompanyModalEditGeneralHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)


    return {
      // Add New Contact
      parametersGeneralLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}

</script>