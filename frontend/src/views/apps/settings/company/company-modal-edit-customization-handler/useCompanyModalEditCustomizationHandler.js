import { ref, computed, watch } from '@vue/composition-api'

export default function useChannelModalEditParameterHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const whiteLabelLocal = ref(JSON.parse(JSON.stringify(props.whiteLabel.value)))
  
  const resetTransferLocal = () => {
    whiteLabelLocal.value = JSON.parse(JSON.stringify(props.whiteLabel.value))
  }
  

  const onSubmit = () => {
    const whiteLabelData = JSON.parse(JSON.stringify(whiteLabelLocal))
    //Manda os dados para serem processados
    emit('update-customization', whiteLabelData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-edit-customization')
  }

  return {
    whiteLabelLocal,

    resetTransferLocal,

    onSubmit,
  }
}
