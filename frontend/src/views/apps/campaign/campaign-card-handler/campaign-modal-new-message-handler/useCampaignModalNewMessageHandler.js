import { ref, computed, watch } from '@vue/composition-api'

export default function useCampaignModalNewMessageHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const newMessageLocal = ref(JSON.parse(JSON.stringify(props.newMessage.value)))
  
  
  const resetTransferLocal = () => {
    newMessageLocal.value = JSON.parse(JSON.stringify(props.newMessage.value))
  }
  watch(props.newMessage, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const messageData = JSON.parse(JSON.stringify(newMessageLocal))
    if (props.newMessage.value.id) emit('update-message', messageData.value)
    else emit('add-message', messageData.value)
    
    //Fecha o modal
    emit('hide-modal', 'modal-new-message-'+messageData.value.campaignId)
  }

  return {
    newMessageLocal,

    onSubmit,
  }
}
