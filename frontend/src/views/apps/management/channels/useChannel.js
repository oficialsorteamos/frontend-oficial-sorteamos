import { ref, computed, watch, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import channelStoreModule from './channelStoreModule'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Swal from 'sweetalert2'
import Vue from 'vue'

export default function useChannel(props, emit) {
  
  const toast = useToast()

  const channels = ref(null)
  const servicesData = ref([])
  //Mostra ou não o botão exibir mais atendimentos
  const hiddenButtonService = ref(false)

  const totalQuotaOfficialChannel = ref(0)
  const totalQuotaUnofficialChannel = ref(0)

  const totalActiveOfficialChannel = ref(0)
  const totalActiveUnofficialChannel = ref(0)

  const officialChannelParameter = ref({})
  const unofficialChannelParameter = ref({})

  const CHANNEL_APP_STORE_MODULE_NAME = 'app-channel'

  // Register module
  if (!store.hasModule(CHANNEL_APP_STORE_MODULE_NAME)) store.registerModule(CHANNEL_APP_STORE_MODULE_NAME, channelStoreModule)

  // UnRegister on leave
  onUnmounted(() => {
    if (store.hasModule(CHANNEL_APP_STORE_MODULE_NAME)) store.unregisterModule(CHANNEL_APP_STORE_MODULE_NAME)
  })

  //Traz os dados do contato
  const fetchChannels = () => {
    store.dispatch('app-channel/fetchChannels')
      .then(response => { 
        channels.value = response.data.channels
        //Cota do canal oficial
        totalQuotaOfficialChannel.value = response.data.totalCurrentOfficialChannelQuota
        //Cota do canal não oficial
        totalQuotaUnofficialChannel.value = response.data.totalCurrentUnofficialChannelQuota

        //Quantidade de canais oficiais ativos 
        totalActiveOfficialChannel.value = response.data.totalOfficialChannelsActives
        //Quantidade de canais não oficiais ativos
        totalActiveUnofficialChannel.value = response.data.totalUnOfficialChannelsActives

        //Quantidade de canais oficiais ativos 
        officialChannelParameter.value = response.data.officialChannelParameter
        //Quantidade de canais não oficiais ativos
        unofficialChannelParameter.value = response.data.unofficialChannelParameter
      })
      .catch(error => {
        
      })
  }

  //Traz os atendimentos associados a um chat de um contato de pouco em pouco, de acordo com o clique do usuário
  const fetchServices = offset => {
    store.dispatch('app-contact/fetchServicesContact', { id: router.currentRoute.params.id, offset: offset } )
      .then(response => {
        //Se existem atendimentos para ser exibidos
        if(response.data.length > 0) {
          if(offset == 0) {
          servicesData.value = response.data
          }
          else {
            //Insere cada novo atendimento carregado no array de serviços
            response.data.map(function(service, key) {
              servicesData.value.push(service)
            });
            console.log(response.data)
          }
        }
        else {
          //Esconde o botão que carrega mais atendimentos
          hiddenButtonService.value = true
        }
      })
  }

  //Adiciona um canal
  const addChannel = channelData => {
    Vue.prototype.$isLoading(true)
    store.dispatch('app-channel/addChannel', { channelData: channelData })
    .then(() => {  
      fetchChannels()
      toast({
        component: ToastificationContent,
        props: {
          title: 'Canal adicionado com sucesso!',
          icon: 'CheckIcon',
          variant: 'success',
        },
      })
    })
    .finally(() => {
      //Esconde a loading screen
      Vue.prototype.$isLoading(false) 
    })
  }

  const updateStatusChannel = (channelId, sessionName, statusId) => {
    store
      .dispatch('app-channel/updateStatusChannel', {
        channelId: channelId,
        sessionName: sessionName,
        statusId: statusId,
      })
      .then(response => {
        //Atualiza os canais na tela
          fetchChannels()
      })
      .catch(() => {
        toast({
          component: ToastificationContent,
          props: {
            title: 'Error fetching contacts list',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })
      })
  }

  //Inicia uma sessão gerando o qrCode
  const startSession = channelData => {
    console.log('chamou start session')
    //Pega os dados do usuário no localStorage
    var userdata = JSON.parse(localStorage.getItem('userData'))
    store.dispatch('app-channel/startSession', { 
        channelData: channelData, 
        userData: userdata
      })
      .then(response  => {  
        console.log('dados com qrcode')
        console.log(response.data)

      })
  }

  const closeSession = channelData => {
    console.log('chamou close session')
    store.dispatch('app-channel/closeSession', { channelData: channelData })
      .then(response  => {  

      })
  }


  return {
    fetchChannels,
    fetchServices,
    addChannel,
    updateStatusChannel,
    startSession,
    closeSession,

    channels,
    servicesData,
    hiddenButtonService,
    totalQuotaOfficialChannel,
    totalQuotaUnofficialChannel,
    totalActiveOfficialChannel,
    totalActiveUnofficialChannel,
    officialChannelParameter,
    unofficialChannelParameter,
    
  }
}
