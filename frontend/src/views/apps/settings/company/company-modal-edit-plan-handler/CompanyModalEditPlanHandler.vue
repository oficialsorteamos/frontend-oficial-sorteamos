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
            <!-- Valor do plano -->
            <b-col md="6">
              <validation-provider
                #default="validationContext"
                name="Plan Value"
                rules="required"
              >
                <b-form-group
                  label-for="plan-value"
                  label="Plan Value*"
                >
                  <money
                    v-model="planLocal.pla_value"
                    id="plan-value"
                    class="form-control"
                    :state="getValidationState(validationContext)"
                    placeholder="Ex: 50,00"
                    autocomplete="off"
                    v-bind="money"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
          <b-row>
            <b-col sm="6">
              <!-- Name -->
              <validation-provider
                #default="validationContext"
                name="Total Users"
                rules="required"
              >
                <b-form-group
                  label="Total Users*"
                  label-for="account-name"
                >
                  <b-form-input
                    id="plan-total-user"
                    v-model="planLocal.pla_total_user"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    type="text"
                    :maxlength="40"
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
            <b-col sm="6">
              <!-- Name -->
              <validation-provider
                #default="validationContext"
                name="Total Official Channels"
                rules="required"
              >
                <b-form-group
                  label="Total Official Channels*"
                  label-for="account-name"
                >
                  <b-form-input
                    id="plan-total-user"
                    v-model="planLocal.pla_total_official_channel"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    type="text"
                    :maxlength="40"
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
            <b-col sm="6">
              <!-- Name -->
              <validation-provider
                #default="validationContext"
                name="Total Unofficial Channels"
                rules="required"
              >
                <b-form-group
                  label="Total Unofficial Channels*"
                  label-for="account-name"
                >
                  <b-form-input
                    id="plan-total-user"
                    v-model="planLocal.pla_total_unofficial_channel"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder=""
                    type="text"
                    :maxlength="40"
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
import useCompanyModalEditPlanHandler from './useCompanyModalEditPlanHandler'
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
    plan: {
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
      planLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCompanyModalEditPlanHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)


    return {
      // Add New Contact
      planLocal,
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