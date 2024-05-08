<template>
  <p class="clearfix mb-0 text-center">
    <span class="mt-25">
      COPYRIGHT  © {{ new Date().getFullYear() }}
      <!--
      <b-link
        class="ml-25"
        href="http://www.devsky.com.br"
        target="_blank"
      >Devsky</b-link> -->
      <span class="d-none d-sm-inline-block">, Todos os direitos reservados</span>
    </span>

    <!--
    <span class="float-md-right d-none d-md-block">Hand-crafted &amp; Made with
      <feather-icon
        icon="HeartIcon"
        size="21"
        class="text-danger stroke-current"
      />
    </span>
    -->
  </p>
</template>

<script>
import { BLink } from 'bootstrap-vue'
import {toRefs } from '@vue/composition-api'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import useChannel from '../../../views/apps/management/channels/useChannel'

export default {
  components: {
    BLink,

    ToastificationContent,
  },

  setup(props, { emit }) {
    //Toast Notification
    const toast = useToast()
    
    const {

      updateStatusChannel,

    } = useChannel(toRefs(props), emit)
    
    //Pega os dados do usuário no localStorage
    const userdata = JSON.parse(localStorage.getItem('userData'))
    Echo.private('user.'+userdata.id)
    .listen('.StatusConnection', (statusConnectionData) => {
      if(statusConnectionData.status == 'inChat') {
        toast({
          component: ToastificationContent,
          props: {
            title: 'Canal conectado',
            icon: 'CheckIcon',
            text: 'Canal Conectado com Sucesso!',
            variant: 'success',
          },
        })
      } 
      else if(statusConnectionData.status == 'desconnectedMobile') {
        console.log('dados de desconexão')
        console.log(statusConnectionData)
        //updateStatusChannel(null, statusConnectionData.session,'I')
        toast({
          component: ToastificationContent,
          props: {
            title: 'Canal desconectado',
            icon: 'AlertTriangleIcon',
            text: 'Canal foi desconectado!',
            variant: 'danger',
          },
        })
      }
      else if(statusConnectionData.status == 'Disconnected') {
        //updateStatusChannel(null, statusConnectionData.session,'I')

        toast({
          component: ToastificationContent,
          props: {
            title: 'Canal desconectado',
            icon: 'AlertTriangleIcon',
            text: 'O canal utilizado para envio da mensagem encontra-se desconectado no momento!',
            variant: 'danger',
          },
        })
      }
    })
    //Alertas enviados pelos gestores
    .listen('.SendAlertNotification', (alertData) => {
      toast({
        component: ToastificationContent,
        props: {
          title: alertData.userName+':',
          icon: 'MessageSquareIcon',
          text: alertData.message,
          variant: 'primary',
        },
      },
      {
        timeout: false,
      })
    })
    .listen('.StatusMessage', (statusData) => {
      if(statusData.error == true) {
        toast({
          component: ToastificationContent,
          props: {
            title: 'Erro ao Enviar a Mensagem',
            icon: 'AlertTriangleIcon',
            text: statusData.statusMessage,
            variant: 'danger',
          },
        },
        {
          timeout: 8000,
        })
      }
    })
  }
}
</script>
