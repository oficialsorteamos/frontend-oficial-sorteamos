import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const messageLocal = ref(JSON.parse(JSON.stringify(props.company.value)))

  /*
  const resetTransferLocal = () => {
    quickMessageLocal.value = JSON.parse(JSON.stringify(props.transfer.value))
  }
  watch(props.transfer, () => {
    resetTransferLocal()
  })*/

  const onSubmit = () => {
    const messageData = JSON.parse(JSON.stringify(messageLocal))
    //Manda os dados para serem processados
    //emit('send-quick-message', messageData.value)
    
    //Fecha o modal
    //emit('hide-modal', 'modal-quick-message')
  }

  return {
    messageLocal,

    onSubmit,
  }
}
