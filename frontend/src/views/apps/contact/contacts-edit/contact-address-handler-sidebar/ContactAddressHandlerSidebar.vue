<template>
  <div>
    <b-sidebar
      id="sidebar-task-handler"
      sidebar-class="sidebar-lg"
      :visible="isTaskHandlerSidebarActive"
      bg-variant="white"
      shadow
      backdrop
      no-header
      right
      @change="(val) => $emit('update:is-task-handler-sidebar-active', val)"
      @hidden="clearForm"
      :width="'600px'"
    >
      <template #default="{ hide }">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
          <h5
            class="mb-0"
          >
            {{ $t('contacts.contactAddressHandlerSidebar.addAddress') }}
          </h5>
          <div>
            <feather-icon
              v-show="addressLocal.id"
              icon="TrashIcon"
              class="cursor-pointer"
              @click="$emit('remove-task'); hide();"
            />
            <!--
            <feather-icon
              class="ml-1 cursor-pointer"
              icon="StarIcon"
              size="16"
              :class="{ 'text-warning': taskLocal.isImportant }"
              @click="taskLocal.isImportant = !taskLocal.isImportant"
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
              id="chatId"
              v-bind:value="addressLocal.userId = userId"
            />
            <!-- Seta o tipo de usuário como Contato -->
            <input
              type="hidden"
              id="typeUserId"
              v-bind:value="addressLocal.typeUserId = 2"
            />
            <b-row>
              <!-- CEP -->
              <b-col
                cols="12"
                xl="4"
                lg="2"
                md="5"
              >
                <validation-provider
                  #default="validationContext"
                  :name="$t('contacts.contactAddressHandlerSidebar.CEP')"
                  rules="required|min:9"
                >
                  <b-form-group
                    :label="$t('contacts.contactAddressHandlerSidebar.CEP')"
                    label-for="task-title"
                  >
                    <b-form-input
                      id="address-cep"
                      v-model="addressLocal.cep"
                      autofocus
                      :state="getValidationState(validationContext)"
                      trim
                      placeholder=""
                      v-mask="'#####-####'"
                      :maxlength="9"
                      @keyup="getAddressContact(addressLocal.cep)"
                      autocomplete="off"
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>
              <b-col
                cols="12"
                xl="4"
                lg="2"
                md="5"
              >
                <div
                  class="mt-2"
                >
                  <b-spinner 
                    label="Loading..." 
                    v-show="loading"
                  />
                </div>
              </b-col>
            </b-row>
            <b-row>
              <!-- Street -->
              <b-col
                cols="12"
                xl="10"
                lg="12"
                md="12"
              >
              
                <validation-provider
                  #default="validationContext"
                  :name="$t('contacts.contactAddressHandlerSidebar.street')"
                  rules="required"
                >
                  <b-form-group
                    :label="$t('contacts.contactAddressHandlerSidebar.street')"
                    label-for="address-street"
                  >
                    <b-form-input
                      id="address-street"
                      v-model="addressLocal.street"
                      :state="getValidationState(validationContext)"
                      trim
                      :placeholder="$t('contacts.contactAddressHandlerSidebar.streetPlaceholder')"
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>

              <!-- Number -->
              <b-col
                cols="12"
                xl="2"
                lg="12"
                md="12"
              >
                <validation-provider
                  #default="validationContext"
                  :name="$t('contacts.contactAddressHandlerSidebar.number')"
                >
                  <b-form-group
                    :label="$t('contacts.contactAddressHandlerSidebar.number')"
                    label-for="address-number"
                  >
                    <b-form-input
                      id="address-number"
                      v-model="addressLocal.number"
                      :state="getValidationState(validationContext)"
                      trim
                      placeholder=""
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>
            </b-row>
            <b-row>
              <!-- Address Complement -->
              <b-col
                cols="12"
                xl="12"
                lg="12"
                md="12"
              >
                <validation-provider
                  #default="validationContext"
                  :name="$t('contacts.contactAddressHandlerSidebar.addressComplement')"
                  rules=""
                >
                  <b-form-group
                    :label="$t('contacts.contactAddressHandlerSidebar.addressComplement')"
                    label-for="address-complement"
                  >
                    <b-form-input
                      id="address-complement"
                      v-model="addressLocal.address_complement"
                      :state="getValidationState(validationContext)"
                      trim
                      :placeholder="$t('contacts.contactAddressHandlerSidebar.addressComplementPlaceholder')"
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col> 
            </b-row>
            <b-row>
              <!-- District -->
              <b-col
                cols="12"
                xl="6"
                lg="12"
                md="12"
              >
                <validation-provider
                  #default="validationContext"
                  :name="$t('contacts.contactAddressHandlerSidebar.addressDistrict')"
                  rules="required"
                >
                  <b-form-group
                    :label="$t('contacts.contactAddressHandlerSidebar.addressDistrict')"
                    label-for="address-district"
                  >
                    <b-form-input
                      id="address-district"
                      v-model="addressLocal.district"
                      :state="getValidationState(validationContext)"
                      trim
                      placeholder=""
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>

              <!-- City -->
              <b-col
                cols="12"
                xl="6"
                lg="12"
                md="12"
              >
                <validation-provider
                  #default="validationContext"
                  :name="$t('contacts.contactAddressHandlerSidebar.addressCity')"
                  rules="required"
                >
                  <b-form-group
                    :label="$t('contacts.contactAddressHandlerSidebar.addressCity')"
                    label-for="address-city"
                  >
                    <b-form-input
                      id="address-city"
                      v-model="addressLocal.city"
                      :state="getValidationState(validationContext)"
                      trim
                      placeholder=""
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col> 
            </b-row>
            <b-row>
              <!-- State -->
              <b-col
                cols="12"
                xl="6"
                lg="12"
                md="12"
              >
                <b-form-group
                  :label="$t('contacts.contactAddressHandlerSidebar.state')"
                  label-for="vue-select"
                >
                  <v-select
                    id="vue-select"
                    v-model="addressLocal.state"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="states"
                    :getOptionLabel="states => states.sta_uf"
                  >
                    <template #search="{attributes, events}">
                      <input
                        class="vs__search"
                        :required="!addressLocal.state"
                        v-bind="attributes"
                        v-on="events"
                      />
                    </template>
                  </v-select>
                </b-form-group>
              </b-col>
              <!-- Country -->
              <b-col
                cols="12"
                xl="6"
                lg="12"
                md="12"
              >
                <b-form-group
                  :label="$t('contacts.contactAddressHandlerSidebar.country')"
                  label-for="vue-select"
                >
                  <v-select
                    id="vue-select"
                    v-model="addressLocal.country"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="countries"
                    :getOptionLabel="countries => countries.cou_name"
                  >
                    <template #search="{attributes, events}">
                      <input
                        class="vs__search"
                        :required="!addressLocal.country"
                        v-bind="attributes"
                        v-on="events"
                      />
                    </template>
                  </v-select>
                </b-form-group>
              </b-col> 
            </b-row>

            <!-- Form Actions -->
            <div class="d-flex mt-2">
              <b-button
                v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                variant="primary"
                class="mr-2"
                type="submit"
              >
                {{ addressLocal.id ? $t('contacts.contactAddressHandlerSidebar.update') : $t('contacts.contactAddressHandlerSidebar.add') }}
              </b-button>
              <b-button
                v-ripple.400="'rgba(186, 191, 199, 0.15)'"
                type="reset"
                variant="outline-secondary"
              >
                {{ $t('contacts.contactAddressHandlerSidebar.reset') }}
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
  BSidebar, BForm, BFormGroup, BFormInput, BAvatar, BButton, BFormInvalidFeedback, BFormCheckbox, BRow, BCol, BSpinner,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import store from '@/store'
import axios from '@axios'
import flatPickr from 'vue-flatpickr-component'
import Ripple from 'vue-ripple-directive'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import { avatarText } from '@core/utils/filter'
import formValidation from '@core/comp-functions/forms/form-validation'
import { toRefs, onUnmounted, ref } from '@vue/composition-api'
import useAddressHandler from './useAddressHandler'
import { VueMaskDirective } from 'v-mask'
import userStoreModule from '../../contactStoreModule'
import Vue from 'vue'
Vue.directive('mask', VueMaskDirective)
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

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
    BSpinner,

    // 3rd party packages
    vSelect,
    flatPickr,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    //Máscara
    VueMaskDirective,
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
    address: {
      type: Object,
      required: true,
    },
    userId: {
      type: Number,
      required: true,
    },
    clearAddressData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      required,
      email,
      url,
      states: [],
      countries: [],
    }
  },
  created() { 
    //Traz os Estados
    axios
      .get('/api/system/state/fetch-states/')
      .then(response => {
        //console.log(response.data.departments)
        this.states = response.data
      });
    
    //Traz os países
    axios
      .get('/api/system/country/fetch-countries/')
      .then(response => {
        //console.log(response.data.departments)
        this.countries = response.data
      });
  },
  setup(props, { emit }) {

    const USER_APP_STORE_MODULE_NAME = 'app-contact'

    // Register module
    if (!store.hasModule(USER_APP_STORE_MODULE_NAME)) store.registerModule(USER_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_APP_STORE_MODULE_NAME)) store.unregisterModule(USER_APP_STORE_MODULE_NAME)
    })

    const toast = useToast()

    const {
      addressLocal,
      resetAddressLocal,

      // UI
      assigneeOptions,
      onSubmit,
      tagOptions,
      resolveAvatarVariant,
    } = useAddressHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetAddressLocal, props.clearAddressData)

    const loading = ref(false)

    //Busca o endereço com base no CEP digitado
    const getAddressContact = cep => {
      //Se foi digitado todos os caracteres do CEP
      if(cep.length == 9) {
        //Mostra o spinner
        loading.value = true

        store.dispatch('app-contact/getAddressContact', { cep: cep })
        .then(response => {
          //Se não houve erro ao buscar o endereço
          if(!response.data.error) { 
            addressLocal.value.street = response.data.address.logradouro 
            addressLocal.value.district = response.data.address.bairro 
            //addressLocal.value.address_complement = response.data.complemento 
            addressLocal.value.city = response.data.address.localidade 
            addressLocal.value.state = response.data.state 
            addressLocal.value.country = response.data.country
          }
          else {
            addressLocal.value.street = null 
            addressLocal.value.district = null 
            //addressLocal.value.address_complement = response.data.complemento 
            addressLocal.value.city = null 
            addressLocal.value.state = null 
            addressLocal.value.country = null
            toast({
              component: ToastificationContent,
              props: {
                title: response.data.error,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            })
          }
           
          //console.log(response.data)

          //Esconde o spinner
          loading.value = false
        })
        .catch(error => {
        })
      }
    }

    return {
      // Add New
      addressLocal,
      onSubmit,
      assigneeOptions,
      tagOptions,

      // Form Validation
      resetForm,
      clearForm,
      refFormObserver,
      getValidationState,

      // UI
      resolveAvatarVariant,

      // Filter/Formatter
      avatarText,

      //Busca o endereço
      getAddressContact,

      //Spinner
      loading,
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
