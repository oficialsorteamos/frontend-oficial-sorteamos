import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const generalMessageLocal = ref(JSON.parse(JSON.stringify(props.generalMessage.value)))
  
  const resetTransferLocal = () => {
    generalMessageLocal.value = JSON.parse(JSON.stringify(props.generalMessage.value))
  }
  watch(props.generalMessage, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const generalMessageData = JSON.parse(JSON.stringify(generalMessageLocal))
    //Manda os dados para serem processados
    emit('update-general-message', generalMessageData.value)
    
    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-general-message-'+props.generalMessage.value.id)
  }

  return {
    generalMessageLocal,

    resetTransferLocal,

    onSubmit,
  }
}
