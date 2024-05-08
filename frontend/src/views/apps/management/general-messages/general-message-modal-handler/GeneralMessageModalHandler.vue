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
          <!-- Status -->
          <b-form-checkbox
            v-model="generalMessageLocal.gen_status"
            name="check-button"
            class="mb-1"
            switch
            inline
            value="A"
            unchecked-value="I"
            v-if="generalMessage.id"
          >
            {{ $t('department.departmentModalHandler.status') }}  
          </b-form-checkbox> 
          
          <div
            class="w-100 border-primary rounded mb-2 p-1"
          >
            <b-row class="mb-2" 
            >
              <b-col
                md="8"
                class="mb-1"
              >
                <!-- Content -->
                <b-form-group
                  :label="$t('chat.chatModalNewQuickMessageHandler.content')"
                  label-for="new-quick-message-content"
                >
                  <quill-editor
                    id="quil-content"
                    v-model="generalMessageLocal.gen_content"
                    :options="editorOption"
                    class="border-bottom-0"
                    ref="myEditor"
                  />
                  <div
                    id="quill-toolbar"
                    class="d-flex justify-content-end border-top-0"
                  >
                    <twemoji-picker
                      :emojiData="emojiDataAll"
                      :emojiGroups="emojiGroups"
                      :skinsSelection="false"
                      :searchEmojisFeat="true"
                      :recentEmojisFeat="true"
                      :randomEmojiArray="['😀']"
                      searchEmojiPlaceholder="Search here."
                      searchEmojiNotFound="Emojis not found."
                      isLoadingLabel="Loading..."
                      @emojiUnicodeAdded="emojiSelected"
                      twemojiPath="https://cdnjs.cloudflare.com/ajax/libs/twemoji/14.0.2/"
                    >
                    </twemoji-picker>
                    <!-- Add a bold button -->
                    <button class="ql-bold" />
                    <!--
                    <button class="ql-italic" />
                    <button class="ql-underline" />
                    <button class="ql-align" />
                    <button class="ql-link" />
                    -->
                  </div>
                </b-form-group>
              </b-col>
              <b-col
                  md="4"
                  class="mb-1"
                >
                <!-- Tags -->
                <div class="justify-content-between">
                  <h6 class="section-label mb-1">
                    Tags
                  </h6>
                </div>
                <b-list-group class="list-group-labels">
                      <p class="mb-2">
                        <b-badge
                          variant="primary"
                          v-clipboard:copy="'%nome%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %nome%
                        </b-badge>
                        Nome do contato
                      </p>
                      <p class="mb-2"
                        v-if="generalMessage.type_general_message_id != 1"
                      >
                        <b-badge
                          variant="success"
                          v-clipboard:copy="'%operador%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %operador%
                        </b-badge>
                        Nome do operador
                      </p>
                      <p class="mb-2"
                        v-if="generalMessage.type_general_message_id == 1 || generalMessage.type_general_message_id == 2"
                      >
                        <b-badge
                          variant="dark"
                          v-clipboard:copy="'%departamento%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %departamento%
                        </b-badge>
                        Nome do departamento
                      </p>
                      <p class="mb-2">
                        <b-badge
                          variant="danger"
                          v-clipboard:copy="'%protocolo%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %protocolo%
                        </b-badge>
                        Nº do protocolo
                      </p>
                      <p>
                        <b-badge
                          variant="warning"
                          v-clipboard:copy="'%saudacao%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %saudacao%
                        </b-badge>
                        Saudação de acordo com o horário do dia
                      </p>
                      <p class="mb-2"
                        v-if="generalMessage.type_general_message_id == 2"
                      >
                        <b-badge
                          variant="secondary"
                          v-clipboard:copy="'%link_usuario%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %link_usuario%
                        </b-badge>
                        Link do Usuário
                      </p>
                  </b-list-group>
              </b-col>  
            </b-row>
          </div>

          <!-- Form Actions -->
          <div class="d-flex mt-2 modal-footer">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              {{ generalMessageLocal.id ? $t('department.departmentModalHandler.update') : $t('department.departmentModalHandler.add') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormDatepicker, BFormCheckbox,
  BRow, BCol, BBadge, BListGroup,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useGeneralMessageModalHandler from './useGeneralMessageModalHandler'
import { required } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { TwemojiPicker } from '@kevinfaguiar/vue-twemoji-picker'
import EmojiAllData from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-all-groups.json'
import EmojiDataAnimalsNature from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-animals-nature.json'
import EmojiDataFoodDrink from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-food-drink.json'
import EmojiGroups from '@kevinfaguiar/vue-twemoji-picker/emoji-data/emoji-groups.json'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { quillEditor } from 'vue-quill-editor'
import VueClipboard from 'vue-clipboard2'

// Faz com que seja possível copiar dentro de modals
VueClipboard.config.autoSetContainer = true 

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
    BBadge,
    BListGroup,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    quillEditor,
    ToastificationContent,

    //Emojis
    'twemoji-picker': TwemojiPicker,
    EmojiGroups,
    EmojiDataFoodDrink,
    EmojiDataAnimalsNature,

  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  computed: {
    emojiDataAll() {
      return EmojiAllData;
    },
    emojiGroups() {
      return EmojiGroups;
    }
  },
  props: {
    generalMessage: {
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
      totalCharactersBody: 0,
    }
  },
  methods: {
    //Insere emojis
    emojiSelected: function(emoji) {
      const range = this.$refs.myEditor.quill.getSelection()
      this.$refs.myEditor.quill.insertText(range.index, emoji)
    },
    onCopy: function (e) {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Tag Copiada!',
          icon: 'CheckIcon',
          variant: 'success',
        },
      })
    },
    onError: function (e) {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Failed to Copy!',
          icon: 'AlertTriangleIcon',
          variant: 'success',
        },
      })
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
      generalMessageLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useGeneralMessageModalHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)

    const editorOption = {
      modules: {
        toolbar: '#quill-toolbar',
      },
      placeholder: 'Write your content',
    }

    return {
      generalMessageLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
      editorOption,
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

#quil-content ::v-deep {
  > .ql-container {
    border-bottom: 0;
  }

  + #quill-toolbar {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-left-radius: $border-radius;
    border-bottom-right-radius: $border-radius;
  }
}

.pointer-cursor {
  cursor: pointer;
}
</style>
<style lang="scss">
#btn-emoji-default {
  height: auto !important;
  width: 25px !important;
  margin: 0 !important;
}
#btn-emoji-default > div > img.emoji {
  width: 17px !important;
  height: 17px !important;
}
.input-group-text {
  white-space: normal !important;
}
.fab-main {
  padding: 22px !important;
}
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
@import '@core/scss/vue/libs/quill.scss';
</style>