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
          id="dialerId"
          v-bind:value="settingsLocal.dialerId = dialer.id"
        />
        <input
          type="hidden"
          id="dialerId"
          v-bind:value="settingsLocal.fowardingSettingId = fowardingSetting.id"
        />        
        <b-row>
          <b-col sm="6">
            <b-form-group
              :label="$t('dialer.dialerModalSettingFowardingHandler.channels')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="settingsLocal.channel"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="channels"
                :selectable="(option) => !option.inUse"
                :getOptionLabel="channels => channels.cha_name"
                transition=""
                @input="chosenChannel"
              >
                <template #option="{ cha_name, infoMessage }">
                  <b>{{ cha_name }}</b>
                  <br />
                  <cite>{{ infoMessage }}</cite>
                </template>
              </v-select>
            </b-form-group>
          </b-col>
        </b-row>
        <!-- Se um canal foi selecionado -->
        <span
          v-if="settingsLocal.channel"
        >
          <b-row>
            <!-- new password -->
            <b-col md="6">
              <validation-provider
                #default="{ errors }"
                :name="$t('dialer.dialerModalSettingFowardingHandler.departments')"
                rules="required"
              >
                <b-form-group
                  :label="$t('dialer.dialerModalSettingFowardingHandler.departments')"
                  label-for="vue-select"
                  :state="errors.length > 0 ? false:null"
                >
                  <v-select
                    id="vue-select"
                    v-model="settingsLocal.department"
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
            </b-col>
            <b-col sm="6">
              <b-form-group
                :label="$t('dialer.dialerModalSettingFowardingHandler.chatbots')"
                label-for="vue-select"
              >
                <v-select
                  id="vue-select"
                  v-model="settingsLocal.chatbot"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="chatbotsChannel"
                  :getOptionLabel="chatbotsChannel => chatbotsChannel.cha_name"
                  transition=""
                >
                  <template #option="{cha_name, cha_description }">
                    <b>{{ cha_name }}</b>
                    <br />
                    <cite>{{ cha_description }}</cite>
                  </template>
                </v-select>
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col sm="6">
              <b-form-group
                :label="$t('dialer.dialerModalSettingFowardingHandler.fairDistribution')"
                label-for="vue-select"
              >
                <v-select
                  id="vue-select"
                  v-model="settingsLocal.fair_distribution"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="fairDistributions"
                  :getOptionLabel="fairDistributions => fairDistributions.fai_name"
                  transition=""
                >
                  <template #option="{fai_name, fai_description }">
                    <b>{{ fai_name }}</b>
                    <br />
                    <cite>{{ fai_description }}</cite>
                  </template>
                </v-select>
              </b-form-group>
            </b-col>
          </b-row>
          <b-row class="mb-1">
            <!-- new password -->
            <b-col md="6">
              <b-form-checkbox
                v-model="settingsLocal.send_message"
                name="check-button"
                switch
                inline
                :value="1"
                :unchecked-value="0"
              >
                {{ $t('dialer.dialerModalSettingFowardingHandler.sendAutomaticMessage') }}
              </b-form-checkbox>
            </b-col>
          </b-row>
          <span
            v-if="settingsLocal.send_message == 1"
          >
            <!-- Se for selecionado um canal NÃO OFICIAL -->
            <span
              v-if="settingsLocal.channel.cha_api_official == 0"
            >
              <b-row 
                class="mb-2" 
              >
                <b-col
                  md="8"
                  class="mb-1"
                >
                  <!-- Content -->
                  <b-form-group
                    :label="$t('dialer.dialerModalSettingFowardingHandler.message')"
                    label-for="new-quick-message-content"
                  >
                    <quill-editor
                      id="quil-content"
                      v-model="settingsLocal.dia_message"
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
                          Nome do operador
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
                    </b-list-group>
                </b-col>  
              </b-row>
            </span>
            <!-- Se for um canal OFICIAL -->
            <span
              v-else
            >
              <dialer-modal-list-message-handler
                :template="fowardingSetting.template != 0? [fowardingSetting.template] : fowardingSetting.template"
                :base-url-storage="baseUrlStorage"
                @add-dialer-template="addDialerTemplate"
                @remove-dialer-template="removeDialerTemplate"
                @hide-modal="hideModal"
                @open-modal="openModal"
              />
              
            </span>
          </span>
        </span>
        
        <!-- Form Actions -->
        <div class="d-flex mt-2 modal-footer">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-2"
            type="submit"
            :disabled="!settingsLocal.channel || !settingsLocal.department || ( settingsLocal.send_message == 1 &&( (settingsLocal.channel.cha_api_official == 0 && (settingsLocal.dia_message && settingsLocal.dia_message.length <= 2)) || 
            (settingsLocal.channel.cha_api_official == 1 && fowardingSetting.template.length == 0)) )"
          >
            <feather-icon
              icon="SaveIcon"
              class="mr-50"
            />
            <span class="align-middle">{{ $t('dialer.dialerModalSettingFowardingHandler.save') }}</span>
          </b-button>
        </div>
      </b-form>
    </validation-observer>  
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BTable, BFormCheckbox, BCard, BRow, BCol, 
  BAvatar, BBadge, BFormInvalidFeedback, BListGroup, BListGroupItem, BFormFile,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useDialerModalSettingFowardingHandler from './useDialerModalSettingFowardingHandler'
import DialerModalListMessageHandler from './dialer-modal-list-message-handler/DialerModalListMessageHandler.vue'
import { TwemojiPicker } from '@kevinfaguiar/vue-twemoji-picker'
import EmojiAllData from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-all-groups.json'
import EmojiDataAnimalsNature from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-animals-nature.json'
import EmojiDataFoodDrink from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-food-drink.json'
import EmojiGroups from '@kevinfaguiar/vue-twemoji-picker/emoji-data/emoji-groups.json'
import formValidation from '@core/comp-functions/forms/form-validation'
import { quillEditor } from 'vue-quill-editor'
import flatPickr from 'vue-flatpickr-component'
import VuePhoneNumberInput from 'vue-phone-number-input'
import { heightTransition } from '@core/mixins/ui/transition'
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
    BFormFile,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,
    
    //Editor
    quillEditor,
    flatPickr,

    VuePhoneNumberInput,

    //Emojis
    'twemoji-picker': TwemojiPicker,
    EmojiGroups,
    EmojiDataFoodDrink,
    EmojiDataAnimalsNature,

    DialerModalListMessageHandler,

    //Toast Notification
    ToastificationContent,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  mixins: [heightTransition],
  props: {
    dialer: {
      type: Object,
      required: true,
    },
    fowardingSetting: {
      type: Object,
      required: true,
    },
    clearChatbotData: {
      type: Function,
      required: true,
    },
    baseUrlStorage: {
      type: String,
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
      optionSelected: '',
      departments: [],
      chatbotsChannel: [],
      channels: [],
      fairDistributions: [],
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
    },
    chosenChannel($channel) {
      this.optionSelected = $channel
      this.settingsLocal.dia_message = ''
      this.settingsLocal.department = ''
      this.settingsLocal.chatbot = ''
      this.fowardingSetting.template = []
    },
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  created() { 
    axios
      .get('/api/integration/dialer/fetch-channels-fowarding/'+this.dialer.id)
      .then(response => {
        //console.log(response.data)
        this.channels = response.data.channels
      });

    axios
      .get('/api/management/department/fetch-departments')
      .then(response => {
        //console.log(response.data)
        this.departments = response.data.departments
      });

    this.optionSelected = this.fowardingSetting.channel
  },
  watch: {
    optionSelected(optionSelected) {
      //Caso exista algum departamento selecionado
      if(optionSelected) {
        //Traz os chatbots que possuem um determinado canal associado para serem inseridos em uma campanha
        axios
          //.get('/api/system/user/get-user/')
          .get(`/api/campaign/fetch-chatbots-campaign/${optionSelected.id}`)
          .then(response => {
            console.log(response.data)
            this.chatbotsChannel = response.data.chatbotsCampaign
          })

        axios
        .get('/api/chat/get-fair-distribution-by-channel/'+optionSelected.id)
        .then(response => {
          console.log('response.data')
          console.log(response.data)
          
          this.fairDistributions = response.data
        });
      }
    },
  },
  setup(props,{ emit }) {
  
    const {
      settingsLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useDialerModalSettingFowardingHandler(toRefs(props), emit)

    
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
      placeholder: 'Escreva a mensagem aqui',
    }

    //Pega o template selecionado
    const addDialerTemplate = (templateData) => {
      props.fowardingSetting.template = templateData[0]
      console.log('templateData')
      console.log(templateData)
      settingsLocal.value.template = templateData[0]
    }

    //Remove um template selecionado
    const removeDialerTemplate = (templateId) => {
      props.fowardingSetting.template = []
      settingsLocal.value.template = []
    }

    return {
      // Add New Event
      settingsLocal,
      resetTransferLocal,
      onSubmit,
      addDialerTemplate,
      removeDialerTemplate,

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

</style>