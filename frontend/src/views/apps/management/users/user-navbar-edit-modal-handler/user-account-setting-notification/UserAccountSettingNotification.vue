<template>
  <b-card>
    <b-form
      enctype="multipart/form-data"
      @submit.prevent="onSubmit"
    >
      <b-row>
        <h6 class="section-label mx-1 mb-2">
          {{ $t('user.userAccountSettingNotification.chat') }}
        </h6>
        <b-col
          cols="12"
          class="mb-2"
        >
          <b-form-checkbox
            id="audio-notification-chat"
            name="check-button"
            switch
            inline
            v-model="userLocal.audio_notification_chat"
            :value="userLocal.audio_notification_chat == 1 || userLocal.audio_notification_chat == true  ? 1 : true"
          >
            <span>{{ $t('user.userAccountSettingNotification.audioNotifyMessage') }}</span>
          </b-form-checkbox>
        </b-col>

        <!-- buttons -->
        <b-col cols="12">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-1 mt-1"
            type="submit"
          >
            {{ $t('user.userAccountSettingNotification.update') }}
          </b-button>
        </b-col>
        <!--/ buttons -->
      </b-row>
    </b-form>
  </b-card>
</template>

<script>
import {
  BButton, BRow, BCol, BCard, BFormCheckbox, BForm,
} from 'bootstrap-vue'
import { toRefs } from '@vue/composition-api'
import Ripple from 'vue-ripple-directive'
import useUserAccountSettingNotification from './useUserAccountSettingNotification'

export default {
  components: {
    BButton,
    BRow,
    BCol,
    BCard,
    BFormCheckbox,
    BForm,
  },
  directives: {
    Ripple,
  },
  props: {
    clearUserData: {
      type: Function,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  setup(props, {emit}) {
    const {
      userLocal,
      resetUserLocal,
      // UI
      onSubmit,
    } = useUserAccountSettingNotification(toRefs(props), emit)
    return {
      userLocal,
      resetUserLocal,
      // UI
      onSubmit,
    }
  }
}
</script>
