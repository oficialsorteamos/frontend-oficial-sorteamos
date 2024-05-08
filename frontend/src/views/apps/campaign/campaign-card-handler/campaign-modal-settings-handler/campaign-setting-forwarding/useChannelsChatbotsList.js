import { ref, watch, computed, } from '@vue/composition-api'
import store from '@/store'
import Vue from 'vue'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useUsersList() {

  // Use toast
  const toast = useToast()

  const refUserListTable = ref(null)
  const channelsChatbotsData = ref([])

  const servicesTimelineData = ref([])
  //Mostra ou não o botão exibir mais atendimentos
  const hiddenButtonServiceTimeline = ref(false)

  // Table Handlers
  const tableColumns = [
    { key: 'channel', label: 'Canal', sortable: false, thClass: 'text-center', tdClass: 'text-center', tbClass: 'selectColumn' },
    { key: 'chatbot', label: 'Chatbot', sortable: false },
    { key: 'actions', label:'Ações', thClass: 'text-center', tdClass: 'text-center' },
  ]
  const perPage = ref(12)
  const totalUsers = ref(0)
  const currentPage = ref(1)
  const perPageOptions = [12, 24, 48, 96]
  const searchQuery = ref('')
  const sortBy = ref('id')
  const isSortDirDesc = ref(true)
  const campaignId = ref(null)

  //Exibe as informações de paginação
  const dataMeta = computed(() => {
    const localItemsCount = refUserListTable.value ? refUserListTable.value.localItems.length : 0
    return {
      from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
      to: perPage.value * (currentPage.value - 1) + localItemsCount,
      of: totalUsers.value,
    }
  })

  const refetchData = () => {
    refUserListTable.value.refresh()
  }

  watch([currentPage, perPage,], () => {
    refetchData()
  })

  const fetchChannelsChatbotsCampaign = (campaignIdData) => {
    if(!campaignId.value) {
      campaignId.value = campaignIdData
    }
    store
      .dispatch('app-campaign/fetchChannelsChatbotsCampaign', {
        campaignId: campaignId.value,
        perPage: perPage.value,
        page: currentPage.value,
      })
      .then(response => {
        console.log('response.data.channelsChatbots')
        console.log(response.data)
        channelsChatbotsData.value = response.data.channelsChatbots
        //totalUsers.value = total
      })
      .catch((e) => {
        //console.log(e)
        /*toast({
          component: ToastificationContent,
          props: {
            title: 'Error fetching contacts list',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })*/
      })
  }

  const addChannelChatbotCampaign = (channel, chatbot) => {
    store
      .dispatch('app-campaign/addChannelChatbotCampaign', {
        campaignId: campaignId.value,
        channel: channel,
        chatbot: chatbot,
      })
      .then(response => {
        fetchChannelsChatbotsCampaign(campaignId.value)
        toast({
          component: ToastificationContent,
          props: {
            title: 'Chatbot associado ao canal com sucesso!',
            icon: 'CheckIcon',
            variant: 'success',
          },
        })
      })
      .catch((e) => {
        //console.log(e)
        /*toast({
          component: ToastificationContent,
          props: {
            title: 'Error fetching contacts list',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })*/
      })
  }
  

  return {
    fetchChannelsChatbotsCampaign,
    addChannelChatbotCampaign,
    tableColumns,
    perPage,
    currentPage,
    totalUsers,
    dataMeta,
    perPageOptions,
    searchQuery,
    sortBy,
    isSortDirDesc,
    refUserListTable,
    channelsChatbotsData,
    
    refetchData,
  }
}
