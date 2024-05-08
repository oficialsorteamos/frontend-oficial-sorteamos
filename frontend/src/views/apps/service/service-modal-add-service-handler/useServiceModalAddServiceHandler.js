import { ref, watch } from '@vue/composition-api'

export default function useServiceModalAddServiceHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const serviceLocal = ref(JSON.parse(JSON.stringify(props.service.value)))
  
  const resetTransferLocal = () => {
    serviceLocal.value = JSON.parse(JSON.stringify(props.service.value))
  }

  watch(props.service, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const serviceData = JSON.parse(JSON.stringify(serviceLocal))

    //Envia os dados para serem processados
    emit('add-service', serviceData.value)
    
    //Fecha o modal
    emit('hide-modal', 'modal-add-service')
  }

  
  return {
    serviceLocal,
    resetTransferLocal,

    onSubmit
  }
}
