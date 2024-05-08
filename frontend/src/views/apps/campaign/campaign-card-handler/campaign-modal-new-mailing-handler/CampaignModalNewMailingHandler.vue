<template>
  <div>
      <!-- select 2 demo -->
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="onSubmit"
      >
        <input
          type="hidden"
          id="campaignId"
          v-bind:value="mailingLocal.campaignId = campaignId"
        />
        <validation-provider
            #default="{ errors }"
            name="File"
            :rules="requiredFileUpload"
          >
          <p>
            Baixe o modelo do mailing clicando <b class="cursor-pointer" @click="$emit('download-mailing-model', '')">aqui</b>
          </p>
          <b-form-group
            :label="$t('campaign.campaignModalNewMailingHandler.mailingFile')"
            label-for="vue-select"
          >
            <b-form-file
                v-model="fileUpload"
                accept=".csv"
                v-on:change="handleFileUpload"
                :placeholder="$t('campaign.campaignModalNewMailingHandler.mailingFilePlaceholder')"
                :drop-placeholder="$t('campaign.campaignModalNewMailingHandler.mailingFileDropPlaceholder')"
              />
            <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                {{ errors[0] }}
              </b-form-invalid-feedback>
          </b-form-group>
        </validation-provider>

        <!-- Tag -->
        <b-form-group
          :label="$t('campaign.campaignModalNewMailingHandler.tags')"
          label-for="vue-select"
        >
          <v-select
            id="vue-select"
            v-model="mailingLocal.tag"
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :options="tags"
            multiple
            :getOptionLabel="tags => tags.tag_name"
            transition=""
          >
           <!--Tag é obrigatório caso nenhum arquivo (mailing) tenha sido selecionado para upload -->
            <template #search="{attributes, events}">
              <input
                class="vs__search"
                :required="!fileUpload && !mailingLocal.tag"
                v-bind="attributes"
                v-on="events"
              />
            </template>
          </v-select>
        </b-form-group>
        <!-- Form Actions -->
        <div class="d-flex mt-2 modal-footer">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-2"
            type="submit"
          >
            {{ $t('campaign.campaignModalNewMailingHandler.save') }}
          </b-button>
        </div>
      </b-form>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormFile, BFormInvalidFeedback,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCampaignModalMailingHandler from './useCampaignModalMailingHandler'
import formValidation from '@core/comp-functions/forms/form-validation'

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    BFormFile,
    BFormInvalidFeedback,
    vSelect,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    mailing: {
      type: Object,
      required: true,
    },
    campaignId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return { 
      tags: [],
      requiredFileUpload: 'required',
      fileUpload: null,
    }
  },
  created() { 
    //Traz as tags cadastradas para contatos
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

    const handleFileUpload = (event) => {
      emit('upload-file', event.target.files[0])
    }

    const {
      mailingLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCampaignModalMailingHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearActionData)


    return {
      mailingLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,

      handleFileUpload,
    }
  },
}
</script>