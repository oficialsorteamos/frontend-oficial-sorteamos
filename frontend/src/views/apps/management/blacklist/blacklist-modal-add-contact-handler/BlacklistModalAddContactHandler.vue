<template>
  <div>
      <!-- select 2 demo -->
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="onSubmit"
      >
        <!-- Guests -->
        <b-form-group
          :label="$t('blacklist.blacklistModalAddContactHandler.contacts')"
          label-for="add-guests"
        >
          <v-select
            v-model="blacklistLocal.contact"
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            multiple
            :close-on-select="false"
            :options="contactsSearch"
            label="name"
            input-id="add-guests"
            @search="fuseSearch"
            :filterable="false"
          >

            <template #option="{ con_avatar, con_name }">
              <b-avatar
                size="sm"
                :src="'../../'+con_avatar"
              />
              <span class="ml-50 align-middle"> {{ con_name }}</span>
            </template>

            <template #selected-option="{ con_avatar, con_name }">
              <b-avatar
                size="sm"
                class="border border-white"
                :src="'../../'+con_avatar"
              />
              <span class="ml-50 align-middle"> {{ con_name }}</span>
            </template>
          </v-select>
        </b-form-group>
        <!-- Form Actions -->
        <div class="d-flex mt-2 modal-footer">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-2"
            type="submit"
          >
            {{ $t('blacklist.blacklistModalAddContactHandler.add') }}
          </b-button>
        </div>
      </b-form>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BAvatar,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useModalBlacklistHandler from './useModalBlacklistHandler'
import formValidation from '@core/comp-functions/forms/form-validation'
import useContactsList from './useContactsList'

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    BAvatar,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    contactBlacklist: {
      type: Object,
      required: true,
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
      blacklistLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useModalBlacklistHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearActionData)

    const {
      fetchContacts,
      searchQuery,
      contactsSearch,
    } = useContactsList()

    fetchContacts()

    const fuseSearch = (search, loading) => {
      console.log(search)
      searchQuery.value = search

      return null
    }


    return {
      // Add New Event
      blacklistLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,

      fetchContacts,
      searchQuery,
      contactsSearch,
      fuseSearch,
    }
  },
}
</script>