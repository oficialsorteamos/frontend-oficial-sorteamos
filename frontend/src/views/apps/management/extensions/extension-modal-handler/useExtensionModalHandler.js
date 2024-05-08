import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const extensionLocal = ref(JSON.parse(JSON.stringify(props.extension.value)))
  
  const resetTransferLocal = () => {
    extensionLocal.value = JSON.parse(JSON.stringify(props.extension.value))
  }
  watch(props.extension, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const extensionData = JSON.parse(JSON.stringify(extensionLocal))
    //Manda os dados para serem processados
    if (props.extension.value.id) emit('update-extension', extensionData.value)
    else emit('add-extension', extensionData.value)

    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-extension-'+props.extension.value.id)
    emit('hide-modal', 'modal-add-extension')
  }

  return {
    extensionLocal,

    resetTransferLocal,

    onSubmit,
  }
}
