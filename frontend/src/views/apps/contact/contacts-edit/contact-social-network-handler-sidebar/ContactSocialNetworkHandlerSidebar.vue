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
      :width="'400px'"
    >
      <template #default="{ hide }">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
          <h5
            class="mb-0"
          >
            {{ $t('contacts.contactSocialNetworkHandlerSidebar.addSocialNetwork') }}
          </h5>
          <div>
            <feather-icon
              v-show="socialLocal.id"
              icon="TrashIcon"
              class="cursor-pointer"
              @click="$emit('remove-social-network'); hide();"
            />
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
              v-bind:value="socialLocal.userId = userId"
            />
            <b-row>
              <!-- Type Social Networks -->
              <b-col
                cols="12"
                xl="8"
                lg="12"
                md="12"
              >
                <b-form-group
                  :label="$t('contacts.contactSocialNetworkHandlerSidebar.socialNetwork')"
                  label-for="vue-select"
                >
                  <v-select
                    id="social-type"
                    v-model="socialLocal.typeSocialNetwork"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="socialNetworks"
                    :getOptionLabel="socialNetworks => socialNetworks.typ_name"
                  >
                    <template #search="{attributes, events}">
                      <input
                        class="vs__search"
                        :required="!socialLocal.typeSocialNetwork"
                        v-bind="attributes"
                        v-on="events"
                      />
                    </template>
                  </v-select>
                </b-form-group>
              </b-col>
            </b-row>
            <b-row>
              <!-- URL -->
              <b-col
                cols="12"
                xl="12"
                lg="12"
                md="12"
              >
                <validation-provider
                  #default="validationContext"
                  :name="$t('contacts.contactSocialNetworkHandlerSidebar.url')"
                  rules="required|url"
                >
                  <b-form-group
                    :label="$t('contacts.contactSocialNetworkHandlerSidebar.url')"
                    label-for="social-url"
                  >
                    <b-form-input
                      id="social-url"
                      v-model="socialLocal.url"
                      :state="getValidationState(validationContext)"
                      trim
                      :placeholder="$t('contacts.contactSocialNetworkHandlerSidebar.urlPlaceholder')"
                      type="url"
                    />

                    <b-form-invalid-feedback>
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
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
                {{ socialLocal.id ? $t('contacts.contactSocialNetworkHandlerSidebar.update') : $t('contacts.contactSocialNetworkHandlerSidebar.add') }}
              </b-button>
              <b-button
                v-ripple.400="'rgba(186, 191, 199, 0.15)'"
                type="reset"
                variant="outline-secondary"
              >
                {{ $t('contacts.contactSocialNetworkHandlerSidebar.reset') }}
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
import useAddressHandler from './useSocialNetworkHandler'
import { VueMaskDirective } from 'v-mask'
import userStoreModule from '../../contactStoreModule'
import Vue from 'vue'
Vue.directive('mask', VueMaskDirective)

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
    social: {
      type: Object,
      required: true,
    },
    userId: {
      type: Number,
      required: true,
    },
    clearSocialNetworkData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      required,
      email,
      url,
      socialNetworks: [],
    }
  },
  created() { 
    //Traz os Estados
    axios
      .get('/api/contact/fetch-type-social-networks/')
      .then(response => {
        //console.log(response.data.departments)
        this.socialNetworks = response.data
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

    const {
      socialLocal,
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
    } = formValidation(resetAddressLocal, props.clearSocialNetworkData)

    return {
      // Add New
      socialLocal,
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
      
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';

</style>

<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';

.assignee-selector {
  ::v-deep .vs__dropdown-toggle {
  padding-left: 0;
  }
}
</style>