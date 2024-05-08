<template>
  <div>
    <validation-observer
      #default="{ handleSubmit }"
      ref="refFormObserver"
    >
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="handleSubmit(onSubmit)"
      >
        <b-row>
          <!-- Companies -->
          <b-col md="12">
            <validation-provider
              #default="{ errors }"
              :name="$t('administrationNotification.notificationModalHandler.companies')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationNotification.notificationModalHandler.companies')+'*'"
                label-for="vue-select"
                :state="errors.length > 0 ? false:null"
              >
                <v-select
                  id="vue-select"
                  v-model="notificationLocal.companies"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  multiple
                  :options="companies"
                  :getOptionLabel="companies => companies.com_name"
                  transition=""
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row>
          <!-- Type Users -->
          <b-col md="6">
            <validation-provider
              #default="{ errors }"
              :name="$t('administrationNotification.notificationModalHandler.typeUsersReceiveMessage')"
              rules="required"
            >
              <b-form-group
                :label="$t('administrationNotification.notificationModalHandler.typeUsersReceiveMessage')+'*'"
                label-for="vue-select"
                :state="errors.length > 0 ? false:null"
              >
                <v-select
                  id="vue-select"
                  v-model="notificationLocal.typeUsers"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  multiple
                  :options="typeUsers"
                  :getOptionLabel="typeUsers => typeUsers.rol_name"
                  transition=""
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row>
          <!-- Name -->
          <b-col md="12">
            <validation-provider
              #default="validationContext"
              :name="$t('administrationNotification.notificationModalHandler.title')"
              rules="required|min:3"
            >
              <b-form-group
                label-for="user-name"
                :label="$t('administrationNotification.notificationModalHandler.title')+'*'"
              >
                <b-form-input
                  v-model="notificationLocal.not_title"
                  id="user-name"
                  :state="getValidationState(validationContext)"
                  trim
                  type="text"
                  autocomplete="off"
                />
                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row>
          <b-col
            class="mb-1"
          >
            <!-- Content -->
            <b-form-group
              :label="$t('administrationNotification.notificationModalHandler.message')"
              label-for="new-quick-message-content"
            >
              <quill-editor
                id="quil-content"
                v-model="notificationLocal.not_message"
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
        </b-row>
        <!-- Form Actions -->
        <div class="d-flex mt-2 modal-footer">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-2"
            type="submit"
          >
            {{ $t('administrationNotification.notificationModalHandler.send') }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import {
  BRow, BCol, BFormGroup, BFormInput, BFormInvalidFeedback, BFormDatepicker, BInputGroupAppend, BInputGroup,
  BFormCheckbox, BForm, BButton,
} from 'bootstrap-vue'
import axios from '@axios'
import vSelect from 'vue-select'
import { toRefs, ref } from '@vue/composition-api'
import Ripple from 'vue-ripple-directive'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import formValidation from '@core/comp-functions/forms/form-validation'
import useNotificationModalHandler from './useNotificationModalHandler'
import { TwemojiPicker } from '@kevinfaguiar/vue-twemoji-picker'
import EmojiAllData from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-all-groups.json'
import EmojiDataAnimalsNature from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-animals-nature.json'
import EmojiDataFoodDrink from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-food-drink.json'
import EmojiGroups from '@kevinfaguiar/vue-twemoji-picker/emoji-data/emoji-groups.json'
import { quillEditor } from 'vue-quill-editor'
import { required } from '@validations'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Vue from 'vue'



export default {
  components: {
    BRow,
    BCol,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    BForm,
    BButton,
    BFormDatepicker,
    vSelect,
    BFormCheckbox,
    // eslint-disable-next-line vue/no-unused-components
    ToastificationContent,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
    BInputGroupAppend,
    BInputGroup,

    //Editor
    quillEditor,

    //Emojis
    'twemoji-picker': TwemojiPicker,
    EmojiGroups,
    EmojiDataFoodDrink,
    EmojiDataAnimalsNature,
  },
  directives: {
    Ripple,
  },
  props: {
    notification: {
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
      companies: [],
      typeUsers: [],
    }
  },
  created() { 
    //Traz as empresas cadastradas
    axios
      .get('/api/administration/company/get-companies-by-status/1')
      .then(response => {
        this.companies = response.data.companies
        this.companies.unshift({id: 0, com_name: 'Todas as Empresas'})
      })
    
    //Traz os usuários de sistema
    axios
      .get('/api/management/user/get-system-users')
      .then(response => {
        this.typeUsers = response.data.systemUsers
      })

  },
  methods: {
    //Insere emojis
    emojiSelected: function(emoji) {
      const range = this.$refs.myEditor.quill.getSelection()
      this.$refs.myEditor.quill.insertText(range.index, emoji)
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
      notificationLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useNotificationModalHandler(toRefs(props), emit)

    
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
      placeholder: 'Escreva aqui',
    }    

    return {
      notificationLocal,
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
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/quill.scss';

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
</style>