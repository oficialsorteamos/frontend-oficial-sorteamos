import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const feeLocal = ref(JSON.parse(JSON.stringify(props.fee.value)))
  
  const resetTransferLocal = () => {
    feeLocal.value = JSON.parse(JSON.stringify(props.fee.value))
  }
  watch(props.fee, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const feeData = JSON.parse(JSON.stringify(feeLocal))
    //Manda os dados para serem processados
    emit('update-fee', feeData.value)

    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-fee-'+props.fee.value.id)
  }

  return {
    feeLocal,

    resetTransferLocal,

    onSubmit,
  }
}
