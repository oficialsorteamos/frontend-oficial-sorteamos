import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const companyLocal = ref(JSON.parse(JSON.stringify(props.company.value)))
  
  const resetTransferLocal = () => {
    companyLocal.value = JSON.parse(JSON.stringify(props.company.value))
  }
  watch(props.company, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const companyData = JSON.parse(JSON.stringify(companyLocal))
    if (props.company.value.id) emit('update-company', companyData.value)
    else emit('add-company', companyData.value)

    //Fecha o modal DE adição de usuário
    emit('hide-modal', 'modal-add-company')
    emit('hide-modal', 'modal-edit-company-'+props.company.value.id)
  }

  return {
    companyLocal,

    resetTransferLocal,

    onSubmit,
  }
}
