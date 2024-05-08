import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const transferLocal = ref(JSON.parse(JSON.stringify(props.transfer.value)))
  
  const resetTransferLocal = () => {
    transferLocal.value = JSON.parse(JSON.stringify(props.transfer.value))
  }
  watch(props.transfer, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const transferData = JSON.parse(JSON.stringify(transferLocal))
    //Manda os dados para serem processados
    emit('add-transfer', transferData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-transfer')
    emit('hide-modal', 'modal-transfer-'+transferData.value.chatId)
  }

  return {
    transferLocal,

    resetTransferLocal,

    onSubmit,
  }
}
