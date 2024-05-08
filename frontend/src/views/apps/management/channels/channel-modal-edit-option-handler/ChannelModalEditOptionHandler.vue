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
          <!-- Departamento para onde o atendimento será transferido -->
          <validation-provider
            #default="{ errors }"
            :name="$t('channel.channelModalEditOptionHandler.department')"
            rules=""
          >
            <b-form-group
              :label="$t('channel.channelModalEditOptionHandler.department')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="channelLocal.parameters[2].department"
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

          <!-- Configuração para Transferência Igualitária -->
          <validation-provider
            #default="{ errors }"
            :name="$t('channel.channelModalEditOptionHandler.fairDistribution')"
            rules=""
          >
            <b-form-group
              :label="$t('channel.channelModalEditOptionHandler.fairDistribution')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="channelLocal.parameters[3].fair_distribution"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="fairDistributions"
                :getOptionLabel="fairDistributions => fairDistributions.fai_name"
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
              {{ $t('channel.channelModalEditOptionHandler.update') }}
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
import axios from '@axios'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useChannelModalEditOptionHandler from './useChannelModalEditOptionHandler'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'

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

  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    channel: {
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
      departments: [],
      fairDistributions: [],
      optionSelected: '',
    }
  },
  created() { 
    //Traz os departamentos cadastrados
    axios
      .get('/api/management/department/fetch-departments/')
      .then(response => {
        //console.log(response.data.departments)
        this.departments = response.data.departments
      });

    //Traz as configurações de transferência igualitária
    axios
      .get('/api/chat/get-fair-distribution-by-channel/'+this.channel.id)
      .then(response => {
        this.fairDistributions = response.data
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
      channelLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useChannelModalEditOptionHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)


    return {
      // Add New Contact
      channelLocal,
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