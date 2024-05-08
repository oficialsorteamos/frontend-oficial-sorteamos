import { ref, computed, watch } from '@vue/composition-api'

export default function useCampaignModalEditCampaignHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const chatbotLocal = ref(JSON.parse(JSON.stringify(props.chatbot.value)))
  
  const resetTransferLocal = () => {
    chatbotLocal.value = JSON.parse(JSON.stringify(props.chatbot.value))
  }
  watch(props.chatbot, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const chatbotData = JSON.parse(JSON.stringify(chatbotLocal))
    //Manda os dados para serem processados
    if (props.chatbot.value.id) emit('update-chatbot', chatbotData.value)
    else emit('add-chatbot', chatbotData.value)

    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-chatbot-'+props.chatbot.value.id)
    emit('hide-modal', 'modal-add-chatbot')
  }

  return {
    chatbotLocal,

    resetTransferLocal,

    onSubmit,
  }
}
