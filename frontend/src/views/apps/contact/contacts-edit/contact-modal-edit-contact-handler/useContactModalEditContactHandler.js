import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const contactLocal = ref(JSON.parse(JSON.stringify(props.contact.value)))
  
  const resetTransferLocal = () => {
    contactLocal.value = JSON.parse(JSON.stringify(props.contact.value))
  }
  watch(props.contact, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const contactData = JSON.parse(JSON.stringify(contactLocal))
    //Manda os dados para serem processados
    if(props.contact.value.id) {
      emit('update-contact', contactData.value)
    }
    else {
      emit('add-contact', contactData.value)
    }
    
    //Fecha o modal
    emit('hide-modal', 'modal-edit-contact')
    emit('hide-modal', 'modal-add-contact')
  }

  return {
    contactLocal,

    resetTransferLocal,

    onSubmit,
  }
}
