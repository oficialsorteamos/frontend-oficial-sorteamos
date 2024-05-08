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
            id="blocId"
            v-bind:value="actionLocal.mainBlocId = bloc.id"
          />

          <!--Actions -->
          <b-form-group
            :label="$t('chatbot.chatbotModalActionHandler.action')"
            label-for="vue-select"
          >
            <v-select
              id="vue-select"
              v-model="actionSelected"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="typeActions"
              :selectable="(option) => !option.typ_description.includes('Chamada Sequencial - Só é possível escolher essa opção')"
              :getOptionLabel="typeActions => typeActions.typ_description"
              transition=""
            >
              <template #search="{attributes, events}">
                <input
                  class="vs__search"
                  :required="!actionLocal.action"
                  v-bind="attributes"
                  v-on="events"
                />
              </template>
            </v-select>
          </b-form-group>
          <!-- Caso a ação selecionada seja uma Transferência -->
          <b-form-group
            :label="$t('chatbot.chatbotModalActionHandler.department')"
            label-for="vue-select"
            v-show="actionLocal.action.id == 1"
          >
            <v-select
              id="vue-select"
              v-model="actionLocal.department"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="departments"
              :getOptionLabel="departments => departments.dep_name"
              transition=""
            >
              <!-- Só é obrigatório caso o tipo de ação seja uma transferência  -->
              <template #search="{attributes, events}">
                <input
                  class="vs__search"
                  :required="actionLocal.action.id == 1 && !actionLocal.department"
                  v-bind="attributes"
                  v-on="events"
                />
              </template>
            </v-select>
          </b-form-group>

          <!-- Caso o tipo de ação escolhida seja TRANSFERÊNCIA IGUALITÁRIA -->
          <b-form-group
            :label="$t('chatbot.chatbotModalActionHandler.settingsFairTransfer')"
            label-for="vue-select"
            v-show="actionLocal.action.id == 7"
          >
            <v-select
              id="vue-select"
              v-model="actionLocal.fair_distribution"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="fairDistributions"
              :getOptionLabel="fairDistributions => fairDistributions.fai_name"
              transition=""
            >
              <!-- Só é obrigatório caso o tipo de ação seja uma transferência  -->
              <template #search="{attributes, events}">
                <input
                  class="vs__search"
                  :required="actionLocal.action.id == 7 && !actionLocal.fair_distribution"
                  v-bind="attributes"
                  v-on="events"
                />
              </template>
            </v-select>
          </b-form-group>
          
          <!-- Blocs call -->
          <b-form-group
            :label="$t('chatbot.chatbotModalActionHandler.bloc')"
            label-for="vue-select"
            v-show="actionLocal.action.id == 2 || actionLocal.action.id == 6"
          >
            <v-select
              id="vue-select"
              v-model="actionLocal.bloc"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="blocs"
              :getOptionLabel="blocs => blocs.cha_title"
              transition=""
            >
              <!-- Só é obrigatório caso o tipo de ação seja uma chamada de bloco ou chamada sequencial  -->
              <template #search="{attributes, events}">
                <input
                  class="vs__search"
                  :required="(actionLocal.action.id == 2 || actionLocal.action.id == 6) && !actionLocal.bloc"
                  v-bind="attributes"
                  v-on="events"
                />
              </template>
            </v-select>
          </b-form-group>

          <!--Actions -->
          <b-form-group
            :label="$t('chatbot.chatbotModalActionHandler.action')"
            label-for="vue-select"
            v-if="actionLocal.action && actionLocal.action.id == 4"
          >
            <v-select
              id="vue-select"
              v-model="typeFinishingSelected"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="typeFinishing"
              :selectable="(option) => !option.description.includes('Sem Chave - Só é possível escolher essa opção caso o bloco não')"
              :getOptionLabel="typeFinishing => typeFinishing.description"
              transition=""
            >
              <template #search="{attributes, events}">
                <input
                  class="vs__search"
                  :required="!actionLocal.action"
                  v-bind="attributes"
                  v-on="events"
                />
              </template>
            </v-select>
          </b-form-group>

          <!-- Se for chamada de bloco -->
          <span
            v-if="actionLocal.action && actionLocal.action.id == 2"
          >
            <!-- Free Key -->
            <b-form-checkbox
              v-model="actionLocal.blo_free_key"
              name="check-button"
              class="mb-1"
              switch
              inline
              value="1"
              unchecked-value="0"
            >
              {{ $t('chatbot.chatbotModalActionHandler.freeKey') }} {{ actionLocal.blo_free_key }}
              
            </b-form-checkbox>
          </span>
          <!-- Key -->
          <validation-provider
            #default="validationContext"
            name="key"
            :rules="actionLocal.blo_free_key == 1? '' : 'required'"
            v-if="actionLocal.action && (actionLocal.action.id != 6 && actionLocal.action.id != 4) || (actionLocal.action.id == 4 && typeFinishingSelected && typeFinishingSelected.id != 2)"
          >
            <b-form-group
              :label="$t('chatbot.chatbotModalActionHandler.key')"
              label-for="key"
            >
              <b-form-input
                id="key"
                :placeholder="$t('chatbot.chatbotModalActionHandler.key')"
                v-model="actionLocal.key"
                :disabled="actionLocal.blo_free_key == 1? true : false"
                :state="getValidationState(validationContext)"
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
              {{ actionLocal.id ? $t('chatbot.chatbotModalActionHandler.update') : $t('chatbot.chatbotModalActionHandler.create') }}
            </b-button>
            <!--
            <b-button
              v-ripple.400="'rgba(186, 191, 199, 0.15)'"
              type="reset"
              variant="outline-secondary"
            >
              Reset
            </b-button>
            -->
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormCheckbox,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required } from '@validations'
import useChatbotModalActionHandler from './useChatbotModalActionHandler'
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
    BFormCheckbox,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    action: {
      type: Object,
      required: true,
    },
    bloc: {
      type: Object,
      required: true,
    },
    chatbot: {
      type: Object,
      required: true,
    },
    clearActionData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      actionSelected: '',
      typeFinishingSelected: null,
      typeActions: [],
      departments: [],
      fairDistributions: [],
      blocs: [],
      typeFinishing: [{id: 1, description: 'Finalização Mediante Chave - Atendimento é finalizado após o contato digitar um valor específico'}, 
                      {id: 2, description: 'Finalização Sem Chave - Atendimento é finalizado imediatamente o após envio do bloco atual'} ],
    }
  },
  watch: {
    //Caso haja alguma alteração
    actionSelected(newAction) {
      this.actionLocal.action = newAction
      //Caso o tipo de ação seja uma transferência
      if(newAction.id == 1) {
        this.actionLocal.bloc = [];
      } //Caso o tipo de ação seja uma chamada de bloco
      else if(newAction.id == 2) {
        this.actionLocal.department = [];
      }
      else {
        this.actionLocal.department = [];
        this.actionLocal.bloc = [];
      }

      //Se a ação selecionada é de finalização de atendimento
      if(newAction.id == 4) {
        //Se já houver uma ação cadastrada para o bloco
        if(this.bloc.actions.length > 0 && this.bloc.actions[0].id != 4 && this.bloc.actions[0].key != null) {
          this.typeFinishing.pop()
          this.typeFinishing.push({id: 2, description: 'Sem Chave - Só é possível escolher essa opção caso o bloco não contenha ações cadastradas'})
        }
      }
    }
  },
  mounted() {
    //Preenche automaticamente com o tipo ação caso a ação esteja sendo atualizada
    this.actionSelected = this.action.action
    //Se houver alguma ação cadastrada
    if(this.bloc.actions.length > 0) {
      var action = this.bloc.actions.find(c => c.action_id === 4)
      console.log('action')
      console.log(action)
      if(action) {
        //Se for uma tipo de ação de finalização SEM CHAVE
        if(action.key == null) {
          this.typeFinishingSelected = {id: 2, description: 'Finalização Sem Chave - Atendimento é finalizado imediatamente o após envio do bloco atual'}
        } //Se for COM CHAVE
        else if(action.key != null && action.key != '') {
          this.typeFinishingSelected = {id: 1, description: 'Finalização Mediante Chave - Atendimento é finalizado após o contato digitar um valor específico'}
        }
      }
    }
  },
  created() { 
    //Traz os tipos de actions  
    axios
      .get('/api/chatbot/fetch-type-actions/')
      .then(response => {
        //Se houver alguma ação cadastrada para o bloco
        if(this.bloc.actions.length > 0) {
          //Desabilita a opção de CHAMADA SEQUENCIAL
          //response.data.typeActions.pop()
          //response.data.typeActions.push({id: 6, typ_description: 'Chamada Sequencial - Só é possível escolher essa opção caso o bloco não contenha ações cadastradas'})
          response.data.typeActions = []
          response.data.typeActions.push({id: 1, typ_description: 'Transferência'})
          response.data.typeActions.push({id: 2, typ_description: 'Chamada de Bloco'})
          response.data.typeActions.push({id: 4, typ_description: 'Finalização de Autoatendimento'})
          //response.data.typeActions.push({id: 5, typ_description: 'Comunicação Ativa'})
          response.data.typeActions.push({id: 6, typ_description: 'Chamada Sequencial - Só é possível escolher essa opção caso o bloco não contenha ações cadastradas'})
          response.data.typeActions.push({id: 7, typ_description: 'Transferência Igualitária'})
        }
        
      // JSON responses are automatically parsed.
        this.typeActions = response.data.typeActions
      });
    //Traz os departamentos cadastrados
    axios
      .get('/api/management/department/fetch-departments/')
      .then(response => {
        //console.log(response.data.departments)
        this.departments = response.data.departments
      });
    //Traz os Blocos cadastrados em um chatbot
    axios
      .get('/api/chatbot/fetch-destination-blocs/'+this.chatbot.id)
      .then(response => {
        //console.log(response.data.blocs)
        this.blocs = response.data.blocs
      })
    //Traz as configurações de transferência igualitária
    axios
      .get('/api/chat/get-fair-distributions/')
      .then(response => {
        //console.log(response.data.blocs)
        this.fairDistributions = response.data
      })
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
      actionLocal,
      resetActionLocal,
      // UI
      onSubmit,
    } = useChatbotModalActionHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetActionLocal, props.clearActionData)


    return {
      // Add New Event
      actionLocal,
      resetActionLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}
</script>