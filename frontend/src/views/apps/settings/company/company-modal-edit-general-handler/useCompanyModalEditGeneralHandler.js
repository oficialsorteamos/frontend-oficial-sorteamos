import { ref, computed, watch } from '@vue/composition-api'

export default function useChannelModalEditParameterHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const parametersGeneralLocal = ref(JSON.parse(JSON.stringify(props.parametersGeneral.value)))
  
  const resetTransferLocal = () => {
    parametersGeneralLocal.value = JSON.parse(JSON.stringify(props.parametersGeneral.value))
  }
  

  const onSubmit = () => {
    const parametersGeneralData = JSON.parse(JSON.stringify(parametersGeneralLocal))
    //Manda os dados para serem processados
    emit('update-general', parametersGeneralData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-edit-general')
  }

  return {
    parametersGeneralLocal,

    resetTransferLocal,

    onSubmit,
  }
}
