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
            v-if="!contact.id"
          />

          <!-- Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('contacts.contactModalEditContactHandler.name')"
            rules="required"
          >
            <b-form-group
              :label="$t('contacts.contactModalEditContactHandler.name')+'*'"
              label-for="task-title"
            >
              <b-form-input
                id="contact-name"
                v-model="contactLocal.con_name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('contacts.contactModalEditContactHandler.namePlaceholder')"
                type="text"
                :maxlength="30"
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
            :name="$t('contacts.contactModalEditContactHandler.email')"
            rules=""
          >
            <b-form-group
              :label="$t('contacts.contactModalEditContactHandler.email')"
              label-for="task-title"
            >
              <b-form-input
                id="contact-email"
                v-model="contactLocal.email"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('contacts.contactModalEditContactHandler.emailPlaceholder')"
                type="text"
                :maxlength="100"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Gender -->
          <b-form-group
            :label="$t('contacts.contactModalEditContactHandler.gender')+'*'"
            label-for="vue-select"
          >
            <v-select
              id="vue-select"
              v-model="contactLocal.gender"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="genders"
              :getOptionLabel="genders => genders.gen_description"
              transition=""
            >
              <template #search="{attributes, events}">
                <input
                  class="vs__search"
                  :required="!contactLocal.gender"
                  v-bind="attributes"
                  v-on="events"
                />
              </template>
            </v-select>
          </b-form-group>

          <!-- Birthday -->
          <b-form-group
            :label="$t('contacts.contactModalEditContactHandler.birthday')"
            label-for="vue-select"
          >
            <b-form-datepicker
              v-model="contactLocal.birthday"
              id="contact-birthday"
              :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
              locale="pt-br"
            />
          </b-form-group>

          <!-- Tag -->
          <validation-provider
            #default="{ errors }"
            :name="$t('contacts.contactModalEditContactHandler.tag')"
            rules=""
            v-if="contact.id"
          >
            <b-form-group
              :label="$t('contacts.contactModalEditContactHandler.tag')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="contactLocal.tags"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="tags"
                :getOptionLabel="tags => tags.tag_name"
                multiple
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
              {{ contact.id? $t('contacts.contactModalEditContactHandler.update') : $t('contacts.contactModalEditContactHandler.add') }}
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
import useContactModalEditContactHandler from './useContactModalEditContactHandler'
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
    contact: {
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
      genders: [],
      tags: [],
      number: '',
    }
  },
  methods: {
    //Insere emojis
    setPhoneNumber: function(data) {
      this.contactLocal.phoneNumber = data.formattedNumber
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
    
    //Traz as etiquetas de usuário
    axios
      .get('/api/management/tag/fetch-tags-type/1')
      .then(response => {
        console.log(response.data)
        this.tags = response.data.tags
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
      contactLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useContactModalEditContactHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)


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
</script>