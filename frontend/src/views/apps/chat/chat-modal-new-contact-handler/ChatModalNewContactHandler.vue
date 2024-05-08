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
          <!-- Phone Number -->
          <VuePhoneNumberInput  
            v-model="number"
            :required="true"
            class="mb-1"
            @update="setPhoneNumber"
          />

          <!-- Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('chat.chatModalNewContactHandler.name')"
          >
            <b-form-group
              :label="$t('chat.chatModalNewContactHandler.name')"
              label-for="task-title"
            >
              <b-form-input
                id="contact-name"
                v-model="contactLocal.name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('chat.chatModalNewContactHandler.namePlaceholder')"
                type="text"
                :maxlength="30"
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
              {{ $t('chat.chatModalNewContactHandler.add') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useChatModalNewContactHandler from './useChatModalNewContactHandler'
import formValidation from '@core/comp-functions/forms/form-validation'
import VuePhoneNumberInput from 'vue-phone-number-input'
//Vue.component('vue-phone-number-input', VuePhoneNumberInput)

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    BFormInvalidFeedback,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    VuePhoneNumberInput,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    newContact: {
      type: Object,
      required: true,
    },
    clearTransferData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      number: '',
      /*contactLocal: {
        phoneNumber: '',
      }*/
    }
  },
  methods: {
    //Insere emojis
    setPhoneNumber: function(data) {
      this.contactLocal.phoneNumber = data.formattedNumber
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
      contactLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useChatModalNewContactHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearActionData)


    return {
      // Add New Contact
      contactLocal,
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