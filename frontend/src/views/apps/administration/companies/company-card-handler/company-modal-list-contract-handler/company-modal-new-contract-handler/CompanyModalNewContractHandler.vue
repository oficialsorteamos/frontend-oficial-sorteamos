<template id="t">
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
          id="companyId"
          v-bind:value="contractLocal.companyId = company.id"
        />
        <b-row>
          <b-col md="12">
            <validation-provider
                #default="{ errors }"
                name="File"
              >
              <b-form-group
                :label="$t('administrationCompany.companyModalNewContractHandler.importContract')"
                label-for="vue-select"
              >
                <b-form-file
                    v-model="fileUpload"
                    accept=".pdf"
                    v-on:change="handleFileUpload"
                    :placeholder="$t('campaign.campaignModalNewMailingHandler.mailingFilePlaceholder')"
                    :drop-placeholder="$t('campaign.campaignModalNewMailingHandler.mailingFileDropPlaceholder')"
                  />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row class="mb-2" 
        >
          <b-col md="4">
            <validation-provider
              #default="validationContext"
              :name="$t('administrationCompany.companyModalNewContractHandler.startContract')"
              rules="required|min:10"
            >
              <b-form-group
                :label="$t('administrationCompany.companyModalNewContractHandler.startContract')"
                label-for="contact-birthday"
              >
                <b-form-input
                  id="contact-birthday"
                  v-model="contractLocal.com_dt_start"
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
          <b-col md="4">
            <validation-provider
              #default="validationContext"
              :name="$t('administrationCompany.companyModalNewContractHandler.endContract')"
              rules="required|min:10"
            >
              <b-form-group
                :label="$t('administrationCompany.companyModalNewContractHandler.endContract')"
                label-for="contact-birthday"
              >
                <b-form-input
                  id="contact-birthday"
                  v-model="contractLocal.com_dt_end"
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
            <feather-icon
              icon="SaveIcon"
              class="mr-50"
            />
            <span class="align-middle">{{ $t('campaign.campaignModalNewMessageHandler.save') }}</span>
          </b-button>
        </div>
      </b-form>
    </validation-observer>  
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BTable, BCard, BRow, BCol, 
  BBadge, BFormInvalidFeedback, BListGroup, BListGroupItem, BFormFile,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCompanyModalNewContractHandler from './useCompanyModalNewContractHandler'
import formValidation from '@core/comp-functions/forms/form-validation'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Vue from 'vue'
import VueClipboard from 'vue-clipboard2'

// Faz com que seja possível copiar dentro de modals
VueClipboard.config.autoSetContainer = true 

Vue.use(VueClipboard)

export default {
  components: {
    BButton,
    BForm,
    BModal,
    VBModal,
    BFormInput,
    BFormGroup,
    vSelect,
    BTable,
    BCard,
    BRow,
    BCol,
    BBadge,
    BFormInvalidFeedback,
    BListGroup,
    BListGroupItem,
    BFormFile,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,

    //Toast Notification
    ToastificationContent,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    contract: {
      type: Object,
      required: true,
    },
    company: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      fileUpload: null,
    }
  },
  methods: {
    
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
      contractLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCompanyModalNewContractHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearActionData)

    const editorOption = {
      modules: {
        toolbar: '#quill-toolbar',
      },
      placeholder: 'Escreva o conteúdo aqui',
    }

    const handleFileUpload = (event) => {
      emit('upload-file', event.target.files[0])
    }

    return {
      // Add New Event
      contractLocal,
      resetTransferLocal,
      onSubmit,
      handleFileUpload,

      //Quill Editor
      editorOption,
      
      
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}
</script>
<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';

.assignee-selector {
  ::v-deep .vs__dropdown-toggle {
  padding-left: 0;
  }
}
</style>
<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>