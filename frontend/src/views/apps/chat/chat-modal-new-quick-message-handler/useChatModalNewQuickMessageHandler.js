import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const newQuickMessageLocal = ref(JSON.parse(JSON.stringify(props.newQuickMessage.value)))
  
  
  const resetTransferLocal = () => {
    newQuickMessageLocal.value = JSON.parse(JSON.stringify(props.newQuickMessage.value))
  }
  watch(props.newQuickMessage, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const quickMessageData = JSON.parse(JSON.stringify(newQuickMessageLocal))
    if (props.newQuickMessage.value.id) emit('update-quick-message', quickMessageData.value)
    else emit('add-quick-message', quickMessageData.value)
    
    //Fecha o modal
    emit('hide-modal', 'modal-new-quick-message')
  }

  return {
    newQuickMessageLocal,

    onSubmit,
  }
}
