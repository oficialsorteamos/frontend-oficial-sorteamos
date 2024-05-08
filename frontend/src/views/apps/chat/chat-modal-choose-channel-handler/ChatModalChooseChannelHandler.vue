<template>
  <div>
      <!-- select 2 demo -->
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="onSubmit"
      >
        <input
          type="hidden"
          id="chatId"
          v-bind:value="channelLocal.chatId = chat.id"
        />
        <!-- Tag -->
        <b-form-group
          :label="$t('chat.chatModalChooseChannelHandler.channels')"
          label-for="vue-select"
        >
          <v-select
            id="vue-select"
            v-model="channelLocal.channel"
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :options="channels"
            :getOptionLabel="channels => channels.cha_name"
            transition=""
          >
            <template #search="{attributes, events}">
              <input
                class="vs__search"
                :required="!channelLocal.channel"
                v-bind="attributes"
                v-on="events"
              />
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
            {{ $t('chat.chatModalChooseChannelHandler.select') }}
          </b-button>
        </div>
      </b-form>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useChatModalChooseChannelHandler from './useChatModalChooseChannelHandler'
import formValidation from '@core/comp-functions/forms/form-validation'

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    chat: {
      type: Object,
      required: true,
    },
  },
  data() {
    return { 
      channels: [],
    }
  },
  created() { 
    //Traz as tags cadastradas para contatos
    axios
      .get('/api/management/channel/fetch-channels-by-status/A')
      .then(response => {
        console.log(response.data)
        this.channels = response.data.channels
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
      channelLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useChatModalChooseChannelHandler(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearActionData)


    return {
      // Add New Event
      channelLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}
</script>