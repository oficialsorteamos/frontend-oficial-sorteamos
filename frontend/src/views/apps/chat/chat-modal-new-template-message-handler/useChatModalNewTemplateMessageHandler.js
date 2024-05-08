import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const templateMessageLocal = ref(JSON.parse(JSON.stringify(props.templateMessage.value)))
  
  
  const resetTransferLocal = () => {
    templateMessageLocal.value = JSON.parse(JSON.stringify(props.templateMessage.value))
  }
  watch(props.templateMessage, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const templateMessageData = JSON.parse(JSON.stringify(templateMessageLocal))
    emit('add-template-message', templateMessageData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-new-template-message')
  }

  return {
    templateMessageLocal,

    onSubmit,
  }
}
