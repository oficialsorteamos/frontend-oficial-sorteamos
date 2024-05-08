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
            id="feeId"
            v-bind:value="paymentOrderLocal.id = paymentOrder.id"
          />
          <b-row>
            <b-col
              sm="12"
            >
              <validation-provider
                #default="{ errors }"
                :name="$t('campaign.campaignModalAddCreditHandler.paymentMethod')"
                rules="required"
              >
                <b-form-group
                  :label="$t('campaign.campaignModalAddCreditHandler.paymentMethod')+'*'" 
                  label-for="vue-select"
                >
                  <v-select
                    id="vue-select"
                    v-model="paymentOrderLocal.status"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="paymentOrderStatus"
                    :getOptionLabel="paymentOrderStatus => paymentOrderStatus.par_description"
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
              {{ paymentOrderLocal.id ? $t('department.departmentModalHandler.update') : $t('department.departmentModalHandler.add') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormDatepicker, BFormCheckbox, BRow, BCol,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import axios from '@axios'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import usePaymentOrderModalEditPaymentOrderHandler from './usePaymentOrderModalEditPaymentOrderHandler'
import { required } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import money from 'v-money'
import Vue from 'vue'
Vue.use(money, {precision: 2})

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
    BRow,
    BCol,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    paymentOrder: {
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
      },
      paymentOrderStatus: [],
    }
  },
  methods: {
    
  },
  created() {
    axios
      .get('/api/administration/partner/payment-order/fetch-payment-order-status')
      .then(response => {
        this.paymentOrderStatus = response.data.paymentOrderStatus
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
      paymentOrderLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = usePaymentOrderModalEditPaymentOrderHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)

    return {
      paymentOrderLocal,
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