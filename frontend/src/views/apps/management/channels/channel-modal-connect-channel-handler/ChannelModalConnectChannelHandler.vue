<template>
  <div>
    <b-card
      class="text-center"
    >
      <span
        v-if="!qrCode"
      >
        <b-spinner 
          :label="$t('channel.channelModalConnectChannelHandler.loading')" 
        />
        {{ $t('channel.channelModalConnectChannelHandler.generatingQrCode') }}
      </span>
      <img 
        v-bind:src="qrCode"
        v-if="qrCode"
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
import useChannelModalConnectChannelHandler from './useChannelModalConnectChannelHandler'
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
    channel: {
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
      phoneNumber: '',
    }
  },
  methods: {
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
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
    const qrCode = ref('')
    //Pega os dados do usuário no localStorage
    const userdata = JSON.parse(localStorage.getItem('userData'))
    //Escuta as mensagens enviadas pelos contatos
      Echo.private('user.'+userdata.id)
      .listen('.SendQrCode', (qrCodeData) => {
        qrCode.value = qrCodeData.qrCode 
        console.log(qrCodeData)
      })
      //Escuta o status da conexão com o whatsapp
      .listen('.StatusConnection', (statusConnectionData) => {
        if(statusConnectionData.status == 'inChat') {
          //Fecha o modal
          emit('hide-modal', 'modal-connect-channel-'+props.channel.id)
          //Atualiza o status do canal para ENABLE
          emit('update-status-channel', props.channel.id, null, 'A') 
        }
      })

    const {
      channelLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useChannelModalConnectChannelHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)


    return {
      // Add New Contact
      channelLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
      qrCode,
    }
  },
}

</script>