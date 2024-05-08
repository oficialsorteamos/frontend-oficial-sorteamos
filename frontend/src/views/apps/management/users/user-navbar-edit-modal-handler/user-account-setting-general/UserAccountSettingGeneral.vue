<template>
  <b-card>

    <!-- media -->
    <b-media no-body>
      <b-media-aside>
        <span
          @click="$refs.importFile.$el.click()"
          style="cursor: pointer"
        >
          <b-avatar
            :src="user.avatar? baseUrlStorage+user.avatar : null"
            :text="avatarText(user.fullName)"
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
        <!--/ avatar -->
      </b-media-aside>

      <b-media-body class="mt-75 ml-75">
        <b-badge 
          variant="primary"
          v-if="user"
          
        >
          {{user.roles[0].rol_name}}
        </b-badge>
        <div
          style="margin-left: -20px !important" 
        >
          <b-form-rating
            v-model="user.rating"
            id="rating-lg-no-border"
            no-border
            variant="warning"
            size="lg"
            inline
            :readonly="true"
            v-b-tooltip.hover.v-secondary
            :title="user.rating"
          />
        </div>
        <!--/ reset -->
      </b-media-body>
    </b-media>
    <!--/ media -->

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
          <b-col sm="6">
            <!-- Name -->
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.name')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.name')"
                label-for="account-name"
              >
                <b-form-input
                  id="contact-name"
                  v-model="userLocal.name"
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
          <b-col sm="6">

            <!-- Email -->
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.email')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.email')+'*'"
                label-for="task-title"
              >
                <b-form-input
                  id="contact-email"
                  v-model="userLocal.email"
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
                    v-model="userLocal.gender"
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
             <!--
             <validation-provider
                #default="validationContext"
                :name="$t('user.userAccountSettingGeneral.birthday')"
                rules="required|min:3"
              >
                <b-form-group
                  :label="$t('user.userAccountSettingGeneral.birthday')+'*'"
                  label-for="vue-select"
                >
                  <b-form-datepicker
                    v-model="userLocal.birthday"
                    id="contact-birthday"
                    :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
                    locale="pt-br"
                    :state="getValidationState(validationContext)"
                  />
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            -->
            <validation-provider
              #default="validationContext"
              :name="$t('user.userAccountSettingGeneral.birthday')"
              rules="required|min:10"
            >
              <b-form-group
                :label="$t('user.userAccountSettingGeneral.birthday')"
                label-for="contact-birthday"
              >
                <b-form-input
                  id="contact-birthday"
                  v-model="userLocal.birthday"
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
           <!-- Phone number -->
            <b-col sm="7">
              <input
                type="hidden"
                id="phoneNumber"
                v-bind:value="userLocal.phoneNumber = phoneNumber"
              />
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userAccountSettingGeneral.phoneNumber')"
                rules="required|min:12"
              >
                <b-form-group
                  label-for="user-phone"
                  :label="$t('user.userAccountSettingGeneral.phoneNumber')+'*'"
                >
                  <!-- Phone Number -->
                  <VuePhoneNumberInput  
                    v-model="user.phoneFormatted"
                    :required="true"
                    class="mb-1"
                    @update="setPhoneNumber"
                  />
                  <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                    {{ errors[0] }}
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
  BLink, BImg, BBadge, BFormRating, BFormInvalidFeedback, BFormDatepicker, VBTooltip, BAvatar,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { ref, toRefs } from '@vue/composition-api'
import axios from '@axios'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import useUserNavbarEditModalHandler from './useUserAccountSettingGeneral'
import vSelect from 'vue-select'
import VuePhoneNumberInput from 'vue-phone-number-input'
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

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    //Phone Input
    VuePhoneNumberInput,

    //Máscara
    VueMaskDirective,
  },
  directives: {
    Ripple,
    'b-tooltip': VBTooltip,
  },
  props: {
    generalData: {
      type: Object,
      default: () => {},
    },
    clearUserData: {
      type: Function,
      required: true,
    },
    user: {
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
    
    //Traz a URL pública do Storage
    axios
      .get('/api/chat/get-base-url-storage/')
      .then(response => {
        //console.log(response.data)
        this.baseUrlStorage = response.data.baseUrlStorage
      })
  },
  data() {
    return {
      optionsLocal: JSON.parse(JSON.stringify(this.generalData)),
      profileFile: null,
      genders: [],
      phoneNumber: null,
      baseUrlStorage: '',
    }
  },
  methods: {
    resetForm() {
      this.optionsLocal = JSON.parse(JSON.stringify(this.generalData))
    },
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
    },
  },
  setup(props, { emit }) {
    const refInputEl = ref(null)
    const previewEl = ref(null)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, previewEl)

    const {
      userLocal,
      resetUserLocal,
      // UI
      onSubmit,
    } = useUserNavbarEditModalHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetUserLocal, props.clearContactData)

    //Cria um atributo na prop com o número do telefone sem o DDI
    const numberWithoutDdi = () => {
      props.user.phoneFormatted = props.user.phone.slice(2)
    }
    numberWithoutDdi()

    const uploadPhotoEmit = photoData => {
      emit('upload-photo', photoData)
    }

    return {
      refInputEl,
      previewEl,
      inputImageRenderer,

      userLocal,
      resetUserLocal,
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

import 'vue-phone-number-input/dist/vue-phone-number-input.css';
</script>
