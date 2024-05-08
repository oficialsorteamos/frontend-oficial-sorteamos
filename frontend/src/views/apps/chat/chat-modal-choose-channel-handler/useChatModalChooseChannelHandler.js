import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const channelLocal = ref(JSON.parse(JSON.stringify(props.chat.value)))
  
  const resetTransferLocal = () => {
    channelLocal.value = JSON.parse(JSON.stringify(props.chat.value))
  }
  watch(props.chat, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const channelData = JSON.parse(JSON.stringify(channelLocal))
    //Manda os dados para serem processados
    emit('start-service', { chatId: channelData.value.chatId, channel: channelData.value.channel} )
    //Fecha o modal
    emit('hide-modal', 'modal-choose-channel')
  }

  return {
    channelLocal,

    resetTransferLocal,

    onSubmit,
  }
}
