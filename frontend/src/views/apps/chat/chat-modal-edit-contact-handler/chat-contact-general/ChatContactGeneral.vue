<template>
  <b-card>

    <!--
    <b-media no-body>
      <b-media-aside>
        <span
          @click="$refs.importFile.$el.click()"
          style="cursor: pointer"
        >
          <b-avatar
            :src="'../../../'+contact.con_avatar"
            :text="avatarText(contact.fullName)"
            variant="light-primary"
            rounded
            size="80px"
          />
        </span>
        <b-form-file
          ref="importFile"
          name="importFile"
          id="importFile"
          accept=".jpeg, .jpg, .png"
          :hidden="true"
          plain
          v-on:change="uploadPhotoEmit"
        />
      </b-media-aside>

      <b-media-body class="mt-75 ml-75">
        <b-badge 
          variant="primary"
          v-if="user"
          
        >
          {{contact.roles[0].rol_name}}
        </b-badge>
        <div
          style="margin-left: -20px !important" 
        >
          <b-form-rating
            v-model="contact.rating"
            id="rating-lg-no-border"
            no-border
            variant="warning"
            size="lg"
            inline
            :readonly="true"
            v-b-tooltip.hover.v-secondary
            :title="contact.rating"
          />
        </div>
      </b-media-body>
    </b-media>
    -->

    <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
    <validation-observer
      #default="{ handleSubmit }"
      ref="refFormObserver"
    >
      <!-- form -->
      <b-form 
        class="mt-2"
        enctype="multipart/form-data"
        @submit.prevent="handleSubmit(onSubmit)"
      >
        <b-row>
          <b-col sm="12">
            <b-form-checkbox
            v-model="legalPersonChecked"
            name="check-button"
            class="custom-control-dark mb-1"
            switch
            inline
            value="1"
            unchecked-value="0"
          >
            {{ $t('chat.chatContactGeneral.legalPerson') }}?
          </b-form-checkbox>

          </b-col>
        </b-row>
        <b-row>
          <b-col sm="7">
            <!-- Name -->
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.name')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.name')+'*'"
                label-for="account-name"
              >
                <b-form-input
                  id="contact-name"
                  v-model="contactLocal.con_name"
                  :state="getValidationState(validationContext)"
                  trim
                  :placeholder="$t('user.userAccountSettingGeneral.namePlaceholder')"
                  type="text"
                  :maxlength="40"
                  autocomplete="off"
                />
                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col md="5">
            <span
              v-if="legalPersonChecked == true || legalPersonChecked == '1' || legalPersonChecked == 1"
            >
              <!-- Cardholder CNPJ -->
              <validation-provider
                #default="validationContext"
                :name="$t('card.cardModalAddCardHandler.cnpj')"
                rules="min:18"
              >
                <b-form-group
                  :label="$t('card.cardModalAddCardHandler.cnpj')"
                  label-for="card-cvv"
                >
                  <b-form-input
                    id="card-cnpj"
                    v-model="contactLocal.con_cnpj"
                    :state="getValidationState(validationContext)"
                    trim
                    type="text"
                    v-mask="'##.###.###/####-##'"
                    :maxlength="18"
                    autocomplete="off"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </span>
            <span
              v-else
            >
              <!-- Cardholder Name -->
              <validation-provider
                #default="validationContext"
                :name="$t('card.cardModalAddCardHandler.cpf')"
                rules="min:14"
              >
                <b-form-group
                  :label="$t('card.cardModalAddCardHandler.cpf')"
                  label-for="card-cvv"
                >
                  <b-form-input
                    id="card-cpf"
                    v-model="contactLocal.con_cpf"
                    :state="getValidationState(validationContext)"
                    trim
                    type="text"
                    v-mask="'###.###.###-##'"
                    :maxlength="14"
                    autocomplete="off"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </span>
          </b-col>
        </b-row>
        <b-row>
          <b-col sm="6">

            <!-- Email -->
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.email')"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.email')"
                label-for="task-title"
              >
                <b-form-input
                  id="contact-email"
                  v-model="contactLocal.con_email"
                  :state="getValidationState(validationContext)"
                  trim
                  :placeholder="$t('user.userAccountSettingGeneral.emailPlaceholder')"
                  type="text"
                  :maxlength="70"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <b-col sm="6">
            <validation-provider
                #default="{ errors }"
                :name="$t('user.userAccountSettingGeneral.gender')"
                rules="required"
              >
                <b-form-group
                  :label="$t('user.userAccountSettingGeneral.gender')+'*'"
                  label-for="vue-select"
                  :state="errors.length > 0 ? false:null"
                >
                  <v-select
                    id="vue-select"
                    v-model="contactLocal.gender"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="genders"
                    :getOptionLabel="genders => genders.gen_description"
                    transition=""
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
          </b-col>
           <b-col sm="6">
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.birthday')"
              rules="min:10"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.birthday')"
                label-for="contact-birthday"
              >
                <b-form-input
                  id="contact-birthday"
                  v-model="contactLocal.con_birthday"
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

          <!--/ alert -->

          <b-col cols="12">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mt-2 mr-1"
              type="submit"
            >
              {{ $t('user.userAccountSettingGeneral.update') }}
            </b-button>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>
  </b-card>
</template>

<script>
import {
  BFormFile, BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BAlert, BCard, BCardText, BMedia, BMediaAside, BMediaBody, 
  BLink, BImg, BBadge, BFormRating, BFormInvalidFeedback, BFormDatepicker, VBTooltip, BAvatar, BFormCheckbox,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { ref, toRefs } from '@vue/composition-api'
import axios from '@axios'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import useChatContactGeneral from './useChatContactGeneral'
import vSelect from 'vue-select'
import { avatarText } from '@core/utils/filter'
import { VueMaskDirective } from 'v-mask'
import Vue from 'vue'
Vue.directive('mask', VueMaskDirective)

export default {
  components: {
    BButton,
    BForm,
    BImg,
    BFormFile,
    BFormGroup,
    BFormInput,
    BRow,
    BCol,
    BAlert,
    BCard,
    BCardText,
    BMedia,
    BMediaAside,
    BMediaBody,
    BLink,
    BBadge,
    BFormRating,
    BFormInvalidFeedback,
    vSelect,
    BFormDatepicker,
    BAvatar,
    BFormCheckbox,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    //Máscara
    VueMaskDirective,
  },
  directives: {
    Ripple,
    'b-tooltip': VBTooltip,
  },
  props: {
    clearUserData: {
      type: Function,
      required: true,
    },
    contact: {
      type: Object,
      required: true,
    },
  },
  created() { 
    //Traz os gêneros cadastrados
    axios
      .get('/api/system/gender/fetch-genders/')
      .then(response => {
        //console.log(response.data.departments)
        this.genders = response.data
      });

    this.legalPersonFunction()
  },
  data() {
    return {
      genders: [],
      legalPersonChecked: '0',
    }
  },
  methods: {
    //Checa se o contato é pessoa física um jurídica
    legalPersonFunction() {
      if(this.contact.con_cnpj) {
        this.contactLocal.legalPersonChecked = '1'
        this.legalPersonChecked = '1'
      }else {
        this.contactLocal.legalPersonChecked = '0'
        this.legalPersonChecked = '0'
      }
    },
  },
  watch: {
    //Caso haja alguma alteração
    legalPersonChecked() {
      this.contactLocal.legalPersonChecked = this.legalPersonChecked
    }
  },
  setup(props, { emit }) {
    const refInputEl = ref(null)
    const previewEl = ref(null)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, previewEl)

    const {
      contactLocal,
      resetContactLocal,
      // UI
      onSubmit,
    } = useChatContactGeneral(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetContactLocal, props.clearContactData)

    //Cria um atributo na prop com o número do telefone sem o DDI
    /*const numberWithoutDdi = () => {
      props.contact.phoneFormatted = props.contact.phone.slice(2)
    }
    numberWithoutDdi()*/

    const uploadPhotoEmit = photoData => {
      emit('upload-photo', photoData)
    }

    return {
      refInputEl,
      previewEl,
      inputImageRenderer,

      contactLocal,
      resetContactLocal,
      // UI
      onSubmit,

      refFormObserver,
      getValidationState,
      clearForm,
      uploadPhotoEmit,
      avatarText,
    }
  },
}

</script>
