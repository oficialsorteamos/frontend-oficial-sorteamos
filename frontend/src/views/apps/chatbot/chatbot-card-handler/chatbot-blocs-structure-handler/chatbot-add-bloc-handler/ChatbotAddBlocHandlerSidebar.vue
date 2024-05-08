<template>
  <div>
    <b-sidebar
      id="sidebar-task-handler"
      width="55%"
      :visible="isTaskHandlerSidebarActive"
      bg-variant="white"
      shadow
      backdrop
      no-header
      right
      @change="(val) => $emit('update:is-task-handler-sidebar-active', val)"
      @hidden="clearForm"
    >
      <template #default="{ hide }">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
          <h5
            class="mb-0"
          >
            {{ $t('chatbot.chatbotAddBlocHandlerSidebar.addBloc') }}
          </h5>
          <div>
            <feather-icon
              v-show="blocLocal.id"
              icon="TrashIcon"
              class="cursor-pointer"
              @click="$emit('remove-bloc'); hide();"
            />
            <!--
            <feather-icon
              class="ml-1 cursor-pointer"
              icon="StarIcon"
              size="16"
              :class="{ 'text-warning': blocLocal.isImportant }"
              @click="blocLocal.isImportant = !blocLocal.isImportant"
            />
            -->
            <feather-icon
              class="ml-1 cursor-pointer"
              icon="XIcon"
              size="16"
              @click="hide"
            />
          </div>
        </div>

        <!-- Body -->
        <validation-observer
          #default="{ handleSubmit }"
          ref="refFormObserver"
        >

          <!-- Form -->
          <b-form
            class="p-2"
            @submit.prevent="handleSubmit(onSubmit)"
            @reset.prevent="resetForm"
          >
            <input
              type="hidden"
              id="blocId"
              v-bind:value="blocLocal.chatbotId = chatbot.id"
            /> 
            <div
              class="border p-1"
            >
            <!-- First Bloc -->
            <b-form-group
              v-if="showFirstBlocCheckbox == true"
            >
              <b-form-group
                label="Bloc Types"
                label-for="task-title"
              >
                <div
                  class="border pt-1 px-1"
                >   
                  <b-form-checkbox
                    v-model="blocLocal.typeBlocId"
                    name="check-button"
                    switch
                    inline
                    :value="showFirstBlocCheckbox == 1 ? 2 : null"
                    :disabled="blocLocal.firstBloc == 2 ? true : false"
                  >
                    {{ $t('chatbot.chatbotAddBlocHandlerSidebar.initialBloc') }}
                  </b-form-checkbox>
                  <b-form-checkbox
                    v-model="blocLocal.typeBlocId"
                    name="check-button"
                    class="mb-1 custom-control-danger"
                    switch
                    inline
                    :value="showFirstBlocCheckbox == 1 ? 3 : null"
                    :disabled="blocLocal.finalBloc == 2 ? true : false"
                  >
                    {{ $t('chatbot.chatbotAddBlocHandlerSidebar.finalBloc') }}
                  </b-form-checkbox>
                  <b-form-checkbox
                    v-model="blocLocal.typeBlocId"
                    name="check-button"
                    class="custom-control-warning"
                    switch
                    inline
                    :value="showFirstBlocCheckbox == 1 ? 4 : null"
                    :disabled="blocLocal.evaluationBloc == 2 ? true : false"
                  >
                    {{ $t('chatbot.chatbotAddBlocHandlerSidebar.evaluation') }}
                  </b-form-checkbox>
                </div>
              </b-form-group>
            </b-form-group>
            <b-form-checkbox
              v-model="blocLocal.cha_send_option_error_message"
              name="check-button"
              class="mb-1 custom-control-dark"
              switch
              inline
              value="1"
              unchecked-value="0"
            >
              {{ $t('chatbot.chatbotAddBlocHandlerSidebar.sendOptionErrorMessage') }}
            </b-form-checkbox>
              <b-row>
                <b-col
                  md="10"
                  class="mb-1"
                >
                  <!-- Title -->
                  <validation-provider
                    #default="validationContext"
                    name="Title"
                    rules="required"
                  >
                    <b-form-group
                      :label="$t('chatbot.chatbotAddBlocHandlerSidebar.title')"
                      label-for="task-title"
                    >
                      <b-form-input
                        id="task-title"
                        v-model="blocLocal.title"
                        autofocus
                        :state="getValidationState(validationContext)"
                        trim
                        :placeholder="$t('chatbot.chatbotAddBlocHandlerSidebar.blocTitle')"
                        @keyup="quillEditorValidator"
                        :maxlength="50"
                        autocomplete="off"
                      />

                      <b-form-invalid-feedback>
                        {{ validationContext.errors[0] }}
                      </b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>
              </b-row>
               
              <!--Se for um canal NÃO oficial -->
              <span
                v-if="chatbot.cha_only_official_channel == 0"
              >
                <!-- Adição de mensagem rápida -->
                <div
                  class="w-100 border-primary rounded mb-2 p-1"
                >
                  <chatbot-list-quick-message-handler
                    :quick-message-id="bloc.quick_message? bloc.quick_message.id : null"
                    :chatbot="chatbot"
                    :base-url-storage="baseUrlStorage"
                    @set-quick-message-selected="setQuickMessageSelected"
                    v-if="bloc.quick_message || blocs.length >= 0"
                  /> 
                </div>
              </span>
              <!-- Se o tipo de bloco for um modelo -->
              <span
                v-else
              >
                
                  <!-- Adição de template -->
                  <div
                    class="w-100 border-primary rounded mb-2 p-1"
                  >
                    <span
                      v-if="chatbot.channels && chatbot.channels.length > 0"
                    >
                      <chatbot-list-template-handler
                        :template-chosen="bloc"
                        :chatbot="chatbot"
                        :base-url-storage="baseUrlStorage"
                        @set-template-selected="setTemplateSelected"
                        @set-quick-message-selected="setQuickMessageSelected"
                        v-if="chatbot.channels.length > 0"
                      />
                    </span>
                    <span
                      v-else
                    >
                      <p class="text-center font-weight-bold">{{ $t('chatbot.chatbotAddBlocHandlerSidebar.titleChooseChannel') }}</p>
                    </span>
                  </div>  
              </span>
            </div>


            <!-- Form Actions -->
            <div class="d-flex mt-2">
              <b-button
                v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                variant="primary"
                class="mr-2"
                type="submit"
                :disabled="disableButton"
              >
                {{ blocLocal.id ? $t('chatbot.chatbotAddBlocHandlerSidebar.update') : $t('chatbot.chatbotAddBlocHandlerSidebar.add') }}
              </b-button>
              <b-button
                v-ripple.400="'rgba(186, 191, 199, 0.15)'"
                type="reset"
                variant="outline-secondary"
              >
                {{ $t('chatbot.chatbotAddBlocHandlerSidebar.reset') }}
              </b-button>
            </div>
          </b-form>
        </validation-observer>
      </template>
    </b-sidebar>
  </div>
</template>

<script>
import {
  BSidebar, BForm, BFormGroup, BFormInput, BAvatar, BButton, BFormInvalidFeedback, BFormCheckbox, BRow, BCol,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import Ripple from 'vue-ripple-directive'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import { avatarText } from '@core/utils/filter'
import formValidation from '@core/comp-functions/forms/form-validation'
import { toRefs } from '@vue/composition-api'
import { quillEditor } from 'vue-quill-editor'
import useBlocHandler from './useBlocHandler'
import ChatbotListTemplateHandler from './ChatbotListTemplateHandler.vue'
import ChatbotListQuickMessageHandler from './ChatbotListQuickMessageHandler.vue'

export default {
  components: {
    // BSV
    BButton,
    BSidebar,
    BForm,
    BFormGroup,
    BFormInput,
    BAvatar,
    BFormInvalidFeedback,
    BFormCheckbox,
    BRow,
    BCol,

    // 3rd party packages
    vSelect,
    flatPickr,
    quillEditor,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    ChatbotListTemplateHandler,
    ChatbotListQuickMessageHandler,

  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isTaskHandlerSidebarActive',
    event: 'update:is-task-handler-sidebar-active',
  },
  props: {
    isTaskHandlerSidebarActive: {
      type: Boolean,
      required: true,
    },
    chatbot: {
      type: Object,
      required: true,
    },
    blocs: {
      type: Array,
      required: false,
    },
    bloc: {
      type: Object,
      required: true,
    },
    clearBlocData: {
      type: Function,
      required: true,
    },
    showFirstBlocCheckbox: {
      type: Boolean,
      default: false,
      required: true,
    },
    baseUrlStorage: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      required,
      email,
      url,
      disableButton: true,
      typeBloc:[ 
        {
          id: 1,
          name: 'Texto Simples'
        },
        {
          id: 2,
          name: 'Modelo'
        },
      ]
    }
  },
  methods: {
    //Função que só habilita o botão para inserir ou atualizar bloco caso os campos estejam validados
    quillEditorValidator: function() {
      //const textLength = this.$refs.myEditor.quill.getLength()
      //Se for uma mensagem de texto simples
      this.validateSendButton()
    },
    //Limpa o texto digitado ou o template escolhido
    clearTextData (typeBloc) {
      if(this.blocLocal.typeMessageSelected == null) {
        this.blocLocal.template = []
        //Seta o texto do quill editor como vazio
        this.$refs.myEditor.quill.setText('')
      }//Se o tipo de bloco for um template
      else if(this.blocLocal.typeMessageSelected.id == 2) {
        //Seta o texto do quill editor como vazio
        this.$refs.myEditor.quill.setText('')
      } //Se o tipo de bloco for de texto simples
      else if(this.blocLocal.typeMessageSelected == 1) {
        //Limpa a variável template
        this.blocLocal.template = []
      }
    },
    validateSendButton() {
      //console.log('this.blocLocal')
      //console.log(this.blocLocal)
      //Se o usuário inseriu o título do bloco, preencheu a mensagem simples ou escolheu o template (onde o template tem status de aprovado ou sinalizado)
      if(this.blocLocal.title.length > 0 && ((this.blocLocal.quickMessage && this.blocLocal.quickMessage.length) || (this.blocLocal.template.length && (this.blocLocal.template[0].status_id == 2 || this.blocLocal.template[0].status_id == 5)))) {
        //Habilita o botão
        this.disableButton = false
      }
      else {
        //Desabilita o botão
        this.disableButton = true
      }
      
    },
    //Seta o template escolhido pelo usuário
    setTemplateSelected (template) {
      this.blocLocal.template = template

      this.validateSendButton()
    },
    //Seta o template escolhido pelo usuário
    setQuickMessageSelected (quickMessage) {
      this.blocLocal.quickMessage = quickMessage

      this.validateSendButton()
    },
  },
  created() {
    //this.disableButton = false
  },
  setup(props, { emit }) {
    const {
      blocLocal,
      resetTaskLocal,

      // UI
      assigneeOptions,
      onSubmit,
      tagOptions,
      resolveAvatarVariant,
    } = useBlocHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTaskLocal, props.clearBlocData)

    const editorOption = {
      modules: {
        toolbar: '#quill-toolbar',
      },
      //placeholder: 'Write Your Content',
    }

    const addBloc = () => {
      
    }

    return {
      // Add New
      blocLocal,
      onSubmit,
      assigneeOptions,
      tagOptions,

      // Form Validation
      resetForm,
      clearForm,
      refFormObserver,
      getValidationState,

      // UI
      editorOption,
      resolveAvatarVariant,

      // Filter/Formatter
      avatarText,
      addBloc,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
@import '@core/scss/vue/libs/quill.scss';
</style>

<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';

.assignee-selector {
  ::v-deep .vs__dropdown-toggle {
  padding-left: 0;
  }
}

#quil-content ::v-deep {
  > .ql-container {
    border-bottom: 0;
    min-height: inherit;
  }

  + #quill-toolbar {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-left-radius: $border-radius;
    border-bottom-right-radius: $border-radius;
  }
}
</style>
<style lang="scss">
#btn-emoji-default {
  height: auto !important;
  width: 25px !important;
  margin: 0 !important;
}
#btn-emoji-default > div > img.emoji {
  width: 15px !important;
  height: 15px !important;
}
</style>
