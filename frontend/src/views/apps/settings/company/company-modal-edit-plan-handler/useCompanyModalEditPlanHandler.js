import { ref, computed, watch } from '@vue/composition-api'

export default function useChannelModalEditParameterHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const planLocal = ref(JSON.parse(JSON.stringify(props.plan.value)))
  
  const resetTransferLocal = () => {
    planLocal.value = JSON.parse(JSON.stringify(props.plan.value))
  }
  

  const onSubmit = () => {
    const planData = JSON.parse(JSON.stringify(planLocal))
    //Manda os dados para serem processados
    emit('update-plan', planData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-edit-plan')
  }

  return {
    planLocal,

    resetTransferLocal,

    onSubmit,
  }
}
