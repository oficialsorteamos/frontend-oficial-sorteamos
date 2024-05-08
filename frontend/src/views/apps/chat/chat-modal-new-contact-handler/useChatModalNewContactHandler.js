import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const contactLocal = ref(JSON.parse(JSON.stringify(props.newContact.value)))
  
  const resetTransferLocal = () => {
    transferLocal.value = JSON.parse(JSON.stringify(props.newContact.value))
  }
  watch(props.newContact, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const contactData = JSON.parse(JSON.stringify(contactLocal))
    //Manda os dados para serem processados
    emit('add-contact', contactData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-new-contact')
  }

  return {
    contactLocal,

    resetTransferLocal,

    onSubmit,
  }
}
