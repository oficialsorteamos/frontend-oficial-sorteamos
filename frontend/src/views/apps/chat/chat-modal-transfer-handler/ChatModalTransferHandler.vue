<template>
  <div>
      <!-- select 2 demo -->
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="onSubmit"
      >
        <input
          type="hidden"
          id="chatId"
          v-bind:value="transferLocal.chatId = chatId"
        />
        <input
          type="hidden"
          id="chatId"
          v-bind:value="transferLocal.serviceId = serviceId"
        />
        <!-- Situação do atendimento -->
        <input
          type="hidden"
          id="situationService"
          v-bind:value="transferLocal.situationService = situationService"
        />
        <!-- Departamento para onde o atendimento será transferido -->
        <b-form-group
          :label="$t('chat.chatModalTransferHandler.department')"
          label-for="vue-select"
        >
          <v-select
            id="vue-select"
            v-model="optionSelected"
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :options="departments"
            :getOptionLabel="departments => departments.dep_name"
            transition=""
          >
            <template #search="{attributes, events}">
              <input
                class="vs__search"
                :required="!optionSelected"
                v-bind="attributes"
                v-on="events"
              />
            </template>
          </v-select>
        </b-form-group>
        <b-form-group
          :label="$t('chat.chatModalTransferHandler.user')"
          label-for="vue-select"
          v-show="optionSelected"
        >
          <v-select
            id="vue-select"
            v-model="transferLocal.user"
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :options="users"
            :getOptionLabel="users => users.name"
            transition=""
          />
        </b-form-group>
        <!-- Form Actions -->
        <div class="d-flex mt-2 modal-footer">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-2"
            type="submit"
          >
            {{ $t('chat.chatModalTransferHandler.transfer') }}
          </b-button>
        </div>
      </b-form>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useChatModalTransferHandler from './useChatModalTransferHandler'
import formValidation from '@core/comp-functions/forms/form-validation'

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
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
    transfer: {
      type: Object,
      required: true,
    },
    chatId: {
      type: Number,
      required: true,
    },
    serviceId: {
      type: Number,
      required: false,
    },
    //Situação do atendimento (autoatendimento, pendente ou ativo)
    situationService: {
      type: Number,
      required: false,
    },
    clearTransferData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      optionSelected: '',
      departments: [],
      users: [],
      blocs: [],
      /*transferLocal: {
        department: null,
        user: null,
        chatId: null,
      }*/
    }
  },
  watch: {
    optionSelected(optionSelected) {
      this.transferLocal.department = optionSelected
      //Caso exista algum departamento selecionado
      if(optionSelected) {
        //Traz os usuários que pertencem ao departamento departamento escolhido
        axios
          //.get('/api/system/user/get-user/')
          .get(`/api/management/user/get-user-department-transfer/${optionSelected.id}`)
          .then(response => {
            console.log(response.data)
            this.users = response.data.users
          })
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
      transferLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useChatModalTransferHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearActionData)


    return {
      // Add New Event
      transferLocal,
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