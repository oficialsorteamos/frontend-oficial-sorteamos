<template>
  <div>
    <b-card
      class="text-center"
    >
      <span
        v-if="!qrcode"
      >
        <b-spinner 
          :label="$t('channel.channelModalConnectChannelHandler.loading')" 
        />
        {{ $t('channel.channelModalConnectChannelHandler.generatingQrCode') }}
      </span>
      <img 
        v-bind:src="'data:image/jpeg;base64,'+qrcode"
        v-if="qrcode"
      />
    </b-card>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BCard, BSpinner,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { toRefs, ref } from '@vue/composition-api'
import formValidation from '@core/comp-functions/forms/form-validation'

export default {
  components: {
    BButton,
    BModal,
    BCard,
    BSpinner,

  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    qrcode: {
      type: String,
      required: false,
    },
    clearContactData: {
      type: Function,
      required: false,
    },
  },
  data() {
    return {
    
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



    return {

    }
  },
}

</script>