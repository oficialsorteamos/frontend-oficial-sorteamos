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
          id="campaignId"
          v-bind:value="newMessageLocal.campaignId = campaignId"
        />
        <b-row class="mb-2" 
        >
          <b-col
            md="8"
            class="mb-1"
          >
            <!-- Content -->
            <b-form-group
              :label="$t('campaign.campaignModalNewMessageHandler.content')"
              label-for="new-quick-message-content"
            >
              <quill-editor
                id="quil-content"
                v-model="newMessageLocal.content"
                :options="editorOption"
                class="border-bottom-0"
                ref="myEditor"
                style="min-height:200px;"
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
                  <p class="mb-2">
                    <b-badge
                      variant="primary"
                      v-clipboard:copy="'%cpf%'"
                      v-clipboard:success="onCopy"
                      v-clipboard:error="onError"
                      class="badge-glow pointer-cursor"
                    >
                      %cpf%
                    </b-badge>
                    CPF do contato
                  </p>
                  <p class="mb-2">
                    <b-badge
                      variant="primary"
                      v-clipboard:copy="'%cnpj%'"
                      v-clipboard:success="onCopy"
                      v-clipboard:error="onError"
                      class="badge-glow pointer-cursor"
                    >
                      %cnpj%
                    </b-badge>
                    CNPJ do contato
                  </p>
                  <p class="mb-2">
                    <b-badge
                      variant="success"
                      v-clipboard:copy="'%operador%'"
                      v-clipboard:success="onCopy"
                      v-clipboard:error="onError"
                      class="badge-glow pointer-cursor"
                    >
                      %operador%
                    </b-badge>
                    Nome do usuário que está logado sistema
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
                  <p>
                    <b-badge
                      variant="dark"
                      v-clipboard:copy="'%dados_adicionais%'"
                      v-clipboard:success="onCopy"
                      v-clipboard:error="onError"
                      class="badge-glow pointer-cursor"
                    >
                      %dados_adicionais%
                    </b-badge>
                    Dados que foram inseridos na planilha de importação
                  </p>
              </b-list-group>
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
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BTable, BFormCheckbox, BCard, BRow, BCol, 
  BAvatar, BBadge, BFormInvalidFeedback, BListGroup, BListGroupItem,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useCampaignModalNewMessageHandler from './useCampaignModalNewMessageHandler'
import { TwemojiPicker } from '@kevinfaguiar/vue-twemoji-picker'
import EmojiAllData from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-all-groups.json'
import EmojiDataAnimalsNature from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-animals-nature.json'
import EmojiDataFoodDrink from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-food-drink.json'
import EmojiGroups from '@kevinfaguiar/vue-twemoji-picker/emoji-data/emoji-groups.json'
import formValidation from '@core/comp-functions/forms/form-validation'
import { quillEditor } from 'vue-quill-editor'
import flatPickr from 'vue-flatpickr-component'
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
    BFormCheckbox,
    BCard,
    BRow,
    BCol,
    BBadge,
    BAvatar,
    BFormInvalidFeedback,
    BListGroup,
    BListGroupItem,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,
    
    //Editor
    quillEditor,
    flatPickr,

    //Emojis
    'twemoji-picker': TwemojiPicker,
    EmojiGroups,
    EmojiDataFoodDrink,
    EmojiDataAnimalsNature,

    //Toast Notification
    ToastificationContent,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    newMessage: {
      type: Object,
      required: true,
    },
    clearNewMessageData: {
      type: Function,
      required: true,
    },
    campaignId: {
      type: Number,
      required: true,
    },
  },
  computed: {
    emojiDataAll() {
      return EmojiAllData;
    },
    emojiGroups() {
      return EmojiGroups;
    }
  },
  data() {
    return {
      selectMode: 'single',
      selected: [],
      items: [],
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
          title: 'Copied Tag!',
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
    }
  },
  created() { 
    //Traz as mensagens rápidas cadastradas
    axios
      .get('/api/chat/quick-message/')
      .then(response => {
        console.log(response.data)
        this.items = response.data.quickMessages
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
      newMessageLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useCampaignModalNewMessageHandler(toRefs(props), emit)

    
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


    return {
      // Add New Event
      newMessageLocal,
      resetTransferLocal,
      onSubmit,

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

.pointer-cursor {
  cursor: pointer;
}
</style>
<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
@import '@core/scss/vue/libs/quill.scss';
</style>