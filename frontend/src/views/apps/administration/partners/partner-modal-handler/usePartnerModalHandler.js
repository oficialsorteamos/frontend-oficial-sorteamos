import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const partnerLocal = ref(JSON.parse(JSON.stringify(props.partner.value)))

  partnerLocal.value.par_partnership_started = new Date(partnerLocal.value.par_partnership_started).toLocaleDateString("pt-BR")
  
  const resetTransferLocal = () => {
    partnerLocal.value = JSON.parse(JSON.stringify(props.partner.value))
  }
  watch(props.partner, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const partnerData = JSON.parse(JSON.stringify(partnerLocal))

    if (props.partner.value.id) emit('update-partner', partnerData.value)
    else emit('add-partner', partnerData.value)

    //Fecha o modal DE adição de usuário
    emit('hide-modal', 'modal-add-partner')
    emit('hide-modal', 'modal-edit-partner-'+props.partner.value.id)
  }

  return {
    partnerLocal,

    resetTransferLocal,

    onSubmit,
  }
}
